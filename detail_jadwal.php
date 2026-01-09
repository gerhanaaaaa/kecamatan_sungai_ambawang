<?php
require_once 'config.php';
requireLogin();

$user = getCurrentUser();
$db = getDB();
$schedule_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get schedule data
$stmt = $db->prepare("
    SELECT s.*, u.nama as created_by_name
    FROM schedules s
    LEFT JOIN users u ON s.created_by = u.id
    WHERE s.id = ?
");
$stmt->execute([$schedule_id]);
$schedule = $stmt->fetch();

if (!$schedule) {
    header('Location: ' . ($_SESSION['role'] === 'admin' ? 'dashboard_admin.php' : 'dashboard_staf.php'));
    exit;
}

// Get staff members assigned to this schedule
$stmt = $db->prepare("
    SELECT ss.*, u.nama, u.nip, u.golongan
    FROM schedule_staff ss
    JOIN users u ON ss.user_id = u.id
    WHERE ss.schedule_id = ?
    ORDER BY u.nama
");
$stmt->execute([$schedule_id]);
$assigned_staff = $stmt->fetchAll();

// Proses upload foto bukti kehadiran staf (setelah variabel terisi)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user['role'] === 'staf' && isset($schedule['status']) && $schedule['status'] !== 'selesai') {
    foreach ($assigned_staff as $staff) {
        if ($staff['user_id'] === $user['id']) {
            if (isset($_POST['konfirmasi_hadir'])) {
                // Validasi file upload
                if (!isset($_FILES['bukti_foto']) || $_FILES['bukti_foto']['error'] !== UPLOAD_ERR_OK) {
                    $upload_error = 'Upload foto gagal. Silakan coba lagi.';
                } else {
                    $file = $_FILES['bukti_foto'];
                    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
                    $max_size = 2 * 1024 * 1024; // 2MB
                    if (!in_array($file['type'], $allowed_types)) {
                        $upload_error = 'Tipe file tidak didukung. Hanya JPG/JPEG/PNG.';
                    } elseif ($file['size'] > $max_size) {
                        $upload_error = 'Ukuran file maksimal 2MB.';
                    } else {
                        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                        $new_name = 'bukti_' . $schedule_id . '_' . $user['id'] . '_' . time() . '.' . $ext;
                        $target = __DIR__ . '/uploads/bukti_kehadiran/' . $new_name;
                        if (move_uploaded_file($file['tmp_name'], $target)) {
                            // Update database: konfirmasi, bukti_foto, waktu_konfirmasi
                            $stmt = $db->prepare("UPDATE schedule_staff SET konfirmasi = 'hadir', bukti_foto = ?, waktu_konfirmasi = NOW() WHERE schedule_id = ? AND user_id = ?");
                            $stmt->execute([$new_name, $schedule_id, $user['id']]);
                            $upload_success = 'Konfirmasi hadir & upload foto berhasil.';
                            header('Location: detail_jadwal.php?id=' . $schedule_id . '&upload=success');
                            exit;
                        } else {
                            $upload_error = 'Gagal menyimpan file. Coba lagi.';
                        }
                    }
                }
            } elseif (isset($_POST['konfirmasi_tidak_hadir'])) {
                $stmt = $db->prepare("UPDATE schedule_staff SET konfirmasi = 'tidak_hadir', waktu_konfirmasi = NOW() WHERE schedule_id = ? AND user_id = ?");
                $stmt->execute([$schedule_id, $user['id']]);
                $upload_success = 'Konfirmasi tidak hadir berhasil.';
                header('Location: detail_jadwal.php?id=' . $schedule_id . '&upload=success');
                exit;
            }
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Jadwal - Sistem Jadwal Turun Lapangan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h1>Sistem Jadwal Turun Lapangan</h1>
            <div class="nav-user">
                <span><?php echo htmlspecialchars($user['nama']); ?></span>
                <a href="<?php echo $user['role'] === 'admin' ? 'dashboard_admin.php' : 'dashboard_staf.php'; ?>" 
                   class="btn btn-sm btn-secondary">← Kembali</a>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <div class="card">
            <div class="detail-header">
                <div>
                    <h2>Detail Jadwal</h2>
                    <span class="badge <?php echo $schedule['status'] === 'selesai' ? 'badge-success' : 'badge-warning'; ?>">
                        <?php echo ucfirst($schedule['status']); ?>
                    </span>
                </div>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="detail_jadwal.php?id=<?php echo $schedule_id; ?>&delete=1" 
                       class="btn btn-sm btn-danger"
                       onclick="return confirm('Yakin hapus jadwal ini? Data tidak bisa dikembalikan.')">
                        🗑️ Hapus Jadwal
                    </a>
                <?php endif; ?>
            </div>
            
            <div class="detail-info">
                <div class="info-row">
                    <strong>Tanggal:</strong>
                    <span><?php echo date('l, d F Y', strtotime($schedule['tanggal'])); ?></span>
                </div>
                
                <div class="info-row">
                    <strong>Waktu:</strong>
                    <span><?php echo date('H:i', strtotime($schedule['waktu'])); ?> WIB</span>
                </div>
                
                <div class="info-row">
                    <strong>Lokasi:</strong>
                    <span><?php echo htmlspecialchars($schedule['lokasi']); ?></span>
                </div>
                
                <div class="info-row">
                    <strong>Kegiatan:</strong>
                    <span><?php echo nl2br(htmlspecialchars($schedule['kegiatan'])); ?></span>
                </div>
                
                <div class="info-row">
                    <strong>Dibuat oleh:</strong>
                    <span><?php echo htmlspecialchars($schedule['created_by_name']); ?></span>
                </div>
                
                <?php if (!empty($schedule['file_pdf'])): ?>
                <div class="info-row">
                    <strong>File PDF:</strong>
                    <span>
                        <a href="download_pdf.php?id=<?php echo $schedule_id; ?>" 
                           target="_blank" 
                           class="btn btn-sm btn-primary"
                           style="display: inline-block; text-decoration: none; padding: 6px 12px; border-radius: 4px; background: #007bff; color: white;">
                            📄 Lihat/Download PDF
                        </a>
                    </span>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="detail-section">
                <h3>Staf yang Ikut (<?php echo count($assigned_staff); ?> orang)</h3>
                <?php if (empty($assigned_staff)): ?>
                    <p class="text-muted">Belum ada staf yang ditugaskan.</p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>NIP</th>
                                <th>Golongan</th>
                                <th>Konfirmasi</th>
                                <?php if ($user['role'] === 'staf' && in_array($user['id'], array_column($assigned_staff, 'user_id'))): ?>
                                    <th>Aksi</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($upload_error)) echo '<div class="alert alert-danger">' . $upload_error . '</div>';
                                if (!empty($upload_success)) echo '<div class="alert alert-success">' . $upload_success . '</div>';
                            ?>
                            <?php foreach ($assigned_staff as $staff): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($staff['nama']); ?></td>
                                    <td><?php echo htmlspecialchars($staff['nip'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($staff['golongan'] ?? '-'); ?></td>
                                    <td>
                                        <span class="badge 
                                            <?php 
                                            if ($staff['konfirmasi'] === 'hadir') echo 'badge-success';
                                            elseif ($staff['konfirmasi'] === 'tidak_hadir') echo 'badge-danger';
                                            else echo 'badge-warning';
                                            ?>">
                                            <?php 
                                            if ($staff['konfirmasi'] === 'hadir') echo 'Hadir';
                                            elseif ($staff['konfirmasi'] === 'tidak_hadir') echo 'Tidak Hadir';
                                            else echo 'Pending';
                                            ?>
                                        </span>
                                        <?php // Tampilkan link foto bukti untuk admin jika ada ?>
                                        <?php if ($staff['konfirmasi'] === 'hadir' && !empty($staff['bukti_foto']) && $user['role'] === 'admin'): ?>
                                            <br><a href="uploads/bukti_kehadiran/<?php echo htmlspecialchars($staff['bukti_foto']); ?>" target="_blank">Lihat Foto Bukti</a>
                                        <?php endif; ?>
                                    </td>
                                    <?php if ($user['role'] === 'staf' && $staff['user_id'] === $user['id'] && $schedule['status'] !== 'selesai'): ?>
                                        <td>
                                            <?php if ($staff['konfirmasi'] !== 'hadir'): ?>
                                                <form method="post" enctype="multipart/form-data" style="display:inline;">
                                                    <input type="hidden" name="konfirmasi_hadir" value="1">
                                                    <input type="file" name="bukti_foto" accept="image/jpeg,image/png" required style="margin-bottom:5px;">
                                                    <button type="submit" class="btn btn-sm btn-success">Konfirmasi Hadir + Upload Foto</button>
                                                </form>
                                                <form method="post" style="display:inline;">
                                                    <input type="hidden" name="konfirmasi_tidak_hadir" value="1">
                                                    <button type="submit" class="btn btn-sm btn-danger">Tidak Hadir</button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                                <?php if (!empty($staff['laporan'])): ?>
                                    <tr>
                                        <td colspan="<?php 
                                            $colspan = 4;
                                            if ($user['role'] === 'staf' && $staff['user_id'] === $user['id'] && $schedule['status'] !== 'selesai') {
                                                $colspan = 5;
                                            }
                                            echo $colspan;
                                        ?>" class="laporan-cell">
                                            <strong>Laporan:</strong> <?php echo nl2br(htmlspecialchars($staff['laporan'])); ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                        </table>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
