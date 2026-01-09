<?php
require_once 'config.php';
requireAdmin();

$db = getDB();
$error = '';

// Get schedule ID
$schedule_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get schedule data
$stmt = $db->prepare("SELECT * FROM schedules WHERE id = ?");
$stmt->execute([$schedule_id]);
$schedule = $stmt->fetch();

if (!$schedule) {
    header('Location: dashboard_admin.php');
    exit;
}

// Get current staff assignments
$stmt = $db->prepare("SELECT user_id FROM schedule_staff WHERE schedule_id = ?");
$stmt->execute([$schedule_id]);
$current_staff = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $tanggal = $_POST['tanggal'] ?? '';
    $waktu = $_POST['waktu'] ?? '';
    $lokasi = $_POST['lokasi'] ?? '';
    $kegiatan = $_POST['kegiatan'] ?? '';
    $staff_ids = $_POST['staff_ids'] ?? [];
    $file_pdf = $schedule['file_pdf']; // Keep existing file by default
    $upload_dir = __DIR__ . '/uploads/';
    
    // Handle PDF upload
    if (isset($_FILES['file_pdf']) && $_FILES['file_pdf']['error'] === UPLOAD_ERR_OK) {
        // Create uploads directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_name = $_FILES['file_pdf']['name'];
        $file_tmp = $_FILES['file_pdf']['tmp_name'];
        $file_size = $_FILES['file_pdf']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Validate file type
        $allowed_extensions = ['pdf'];
        if (!in_array($file_ext, $allowed_extensions)) {
            $error = "Hanya file PDF yang diizinkan!";
        } elseif ($file_size > 5242880) { // 5MB max
            $error = "Ukuran file maksimal 5MB!";
        } else {
            // Delete old file if exists
            if ($file_pdf && file_exists($upload_dir . $file_pdf)) {
                unlink($upload_dir . $file_pdf);
            }
            
            // Generate unique filename
            $new_filename = uniqid('jadwal_', true) . '_' . time() . '.pdf';
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($file_tmp, $upload_path)) {
                $file_pdf = $new_filename;
            } else {
                $error = "Gagal mengunggah file!";
            }
        }
    } elseif (isset($_FILES['file_pdf']) && $_FILES['file_pdf']['error'] !== UPLOAD_ERR_NO_FILE) {
        $error = "Error saat mengunggah file: " . $_FILES['file_pdf']['error'];
    }
    
    // Handle file deletion if checkbox is checked
    if (isset($_POST['hapus_file_pdf']) && $_POST['hapus_file_pdf'] === '1') {
        if ($file_pdf && file_exists($upload_dir . $file_pdf)) {
            unlink($upload_dir . $file_pdf);
        }
        $file_pdf = null;
    }
    
    if (empty($tanggal) || empty($waktu) || empty($lokasi) || empty($kegiatan)) {
        $error = "Semua field harus diisi!";
    }
    
    if (empty($error)) {
        try {
            $db->beginTransaction();
            
            // Update schedule
            $stmt = $db->prepare("
                UPDATE schedules 
                SET tanggal = ?, waktu = ?, lokasi = ?, kegiatan = ?, file_pdf = ?
                WHERE id = ?
            ");
            $stmt->execute([$tanggal, $waktu, $lokasi, $kegiatan, $file_pdf, $schedule_id]);
            
            // Delete old staff assignments
            $stmt = $db->prepare("DELETE FROM schedule_staff WHERE schedule_id = ?");
            $stmt->execute([$schedule_id]);
            
            // Insert new staff assignments
            if (!empty($staff_ids) && is_array($staff_ids)) {
                $stmt = $db->prepare("INSERT INTO schedule_staff (schedule_id, user_id) VALUES (?, ?)");
                foreach ($staff_ids as $staff_id) {
                    $stmt->execute([$schedule_id, (int)$staff_id]);
                }
            }
            
            $db->commit();
            header('Location: dashboard_admin.php?success=updated');
            exit;
        } catch (Exception $e) {
            $db->rollBack();
            // Delete uploaded file if database update fails
            if (isset($new_filename) && file_exists($upload_dir . $new_filename)) {
                unlink($upload_dir . $new_filename);
            }
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Get all staff users
$stmt = $db->query("SELECT id, nama, nip, golongan FROM users WHERE role = 'staf' ORDER BY nama");
$staff_list = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Jadwal - Sistem Jadwal Turun Lapangan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h1>Sistem Jadwal Turun Lapangan</h1>
            <div class="nav-user">
                <a href="dashboard_admin.php" class="btn btn-sm btn-secondary">← Kembali</a>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <div class="page-header">
            <h2>Edit Jadwal</h2>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="card">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="tanggal">Tanggal *</label>
                    <input type="date" id="tanggal" name="tanggal" 
                           value="<?php echo $schedule['tanggal']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="waktu">Waktu *</label>
                    <input type="time" id="waktu" name="waktu" 
                           value="<?php echo date('H:i', strtotime($schedule['waktu'])); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="lokasi">Lokasi *</label>
                    <input type="text" id="lokasi" name="lokasi" 
                           value="<?php echo htmlspecialchars($schedule['lokasi']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="kegiatan">Kegiatan *</label>
                    <textarea id="kegiatan" name="kegiatan" rows="4" required><?php echo htmlspecialchars($schedule['kegiatan']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="file_pdf">File PDF (Opsional)</label>
                    <?php if (!empty($schedule['file_pdf'])): ?>
                        <div style="margin-bottom: 10px; padding: 10px; background: #f5f5f5; border-radius: 4px;">
                            <strong>File saat ini:</strong> 
                            <a href="download_pdf.php?id=<?php echo $schedule_id; ?>" target="_blank" 
                               style="color: #007bff; text-decoration: none;">
                                <?php echo htmlspecialchars($schedule['file_pdf']); ?>
                            </a>
                            <label style="display: block; margin-top: 8px;">
                                <input type="checkbox" name="hapus_file_pdf" value="1">
                                Hapus file PDF saat ini
                            </label>
                        </div>
                    <?php endif; ?>
                    <input type="file" id="file_pdf" name="file_pdf" accept=".pdf">
                    <small style="color: #666; display: block; margin-top: 5px;">
                        Format: PDF | Maksimal: 5MB
                        <?php if (!empty($schedule['file_pdf'])): ?>
                            | Upload file baru untuk mengganti file yang ada
                        <?php endif; ?>
                    </small>
                </div>
                
                <div class="form-group">
                    <label>Staf yang Ikut</label>
                    <?php if (empty($staff_list)): ?>
                        <p class="text-muted">Belum ada staf terdaftar.</p>
                    <?php else: ?>
                        <div class="checkbox-group">
                            <?php foreach ($staff_list as $staff): ?>
                                <label class="checkbox-label" style="display: block; margin-bottom: 10px; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                    <input type="checkbox" name="staff_ids[]" value="<?php echo $staff['id']; ?>"
                                           <?php echo in_array($staff['id'], $current_staff) ? 'checked' : ''; ?>
                                           style="margin-right: 10px;">
                                    <div style="display: inline-block; vertical-align: top;">
                                        <div><strong><?php echo htmlspecialchars($staff['nama']); ?></strong></div>
                                        <div style="font-size: 0.9em; color: #666; margin-top: 4px;">
                                            NIP: <?php echo htmlspecialchars($staff['nip'] ?? '-'); ?> | 
                                            Golongan: <?php echo htmlspecialchars($staff['golongan'] ?? '-'); ?>
                                        </div>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="update" class="btn btn-primary">Update Jadwal</button>
                    <a href="dashboard_admin.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
