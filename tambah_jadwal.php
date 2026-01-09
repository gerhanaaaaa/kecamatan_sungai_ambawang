<?php
require_once 'config.php';
requireAdmin();

$db = getDB();
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
    $tanggal = $_POST['tanggal'] ?? '';
    $waktu = $_POST['waktu'] ?? '';
    $lokasi = $_POST['lokasi'] ?? '';
    $kegiatan = $_POST['kegiatan'] ?? '';
    $staff_ids = $_POST['staff_ids'] ?? [];
    $file_pdf = null;
    
    // Handle PDF upload
    if (isset($_FILES['file_pdf']) && $_FILES['file_pdf']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/uploads/';
        
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
    
    if (empty($error) && (empty($tanggal) || empty($waktu) || empty($lokasi) || empty($kegiatan))) {
        $error = "Semua field harus diisi!";
    }
    
    if (empty($error)) {
        try {
            $db->beginTransaction();
            
            // Insert schedule
            $stmt = $db->prepare("
                INSERT INTO schedules (tanggal, waktu, lokasi, kegiatan, file_pdf, created_by) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$tanggal, $waktu, $lokasi, $kegiatan, $file_pdf, $_SESSION['user_id']]);
            $schedule_id = $db->lastInsertId();
            
            // Insert staff members
            if (!empty($staff_ids) && is_array($staff_ids)) {
                $stmt = $db->prepare("INSERT INTO schedule_staff (schedule_id, user_id) VALUES (?, ?)");
                foreach ($staff_ids as $staff_id) {
                    $stmt->execute([$schedule_id, (int)$staff_id]);
                }
            }
            
            $db->commit();
            header('Location: dashboard_admin.php?success=added');
            exit;
        } catch (Exception $e) {
            $db->rollBack();
            // Delete uploaded file if database insert fails
            if ($file_pdf && file_exists($upload_dir . $file_pdf)) {
                unlink($upload_dir . $file_pdf);
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
    <title>Tambah Jadwal - Sistem Jadwal Turun Lapangan</title>
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
            <h2>Tambah Jadwal Baru</h2>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="card">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="tanggal">Tanggal *</label>
                    <input type="date" id="tanggal" name="tanggal" 
                           value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="waktu">Waktu *</label>
                    <input type="time" id="waktu" name="waktu" required>
                </div>
                
                <div class="form-group">
                    <label for="lokasi">Lokasi *</label>
                    <input type="text" id="lokasi" name="lokasi" 
                           placeholder="Contoh: Desa ABC, Kelurahan XYZ" required>
                </div>
                
                <div class="form-group">
                    <label for="kegiatan">Kegiatan *</label>
                    <textarea id="kegiatan" name="kegiatan" rows="4" 
                              placeholder="Deskripsi kegiatan yang akan dilakukan" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="file_pdf">File PDF (Opsional)</label>
                    <input type="file" id="file_pdf" name="file_pdf" accept=".pdf">
                    <small style="color: #666; display: block; margin-top: 5px;">
                        Format: PDF | Maksimal: 5MB
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
                                    <input type="checkbox" name="staff_ids[]" value="<?php echo $staff['id']; ?>" style="margin-right: 10px;">
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
                    <button type="submit" name="tambah" class="btn btn-primary">Simpan Jadwal</button>
                    <a href="dashboard_admin.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
