<?php
require_once 'config.php';
requireAdmin();

$db = getDB();
$user = getCurrentUser();

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Cek apakah staf masih terdaftar di jadwal AKTIF (bukan yang sudah selesai)
    // Hanya tolak penghapusan jika ada jadwal dengan status != 'selesai'
    $stmt = $db->prepare("
        SELECT COUNT(*) as count 
        FROM schedule_staff ss
        INNER JOIN schedules s ON ss.schedule_id = s.id
        WHERE ss.user_id = ? AND s.status != 'selesai'
    ");
    $stmt->execute([$id]);
    $check = $stmt->fetch();
    
    if ($check['count'] > 0) {
        header('Location: kelola_staf.php?error=staf_memiliki_jadwal');
        exit;
    }
    
    $stmt = $db->prepare("DELETE FROM users WHERE id = ? AND role = 'staf'");
    $stmt->execute([$id]);
    header('Location: kelola_staf.php?success=deleted');
    exit;
}

// Get all staff users
$stmt = $db->query("SELECT id, username, nama, nip, golongan, created_at FROM users WHERE role = 'staf' ORDER BY nama");
$staff_list = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Staf - Sistem Jadwal Turun Lapangan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h1>Sistem Jadwal Turun Lapangan</h1>
            <div class="nav-user">
                <a href="dashboard_admin.php" class="btn btn-sm btn-secondary">← Kembali ke Dashboard</a>
                <a href="logout.php" class="btn btn-sm btn-secondary">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <?php 
                if ($_GET['success'] === 'deleted') echo "Staf berhasil dihapus!";
                if ($_GET['success'] === 'added') {
                    $username = isset($_GET['username']) ? htmlspecialchars($_GET['username']) : '';
                    echo "Staf berhasil ditambahkan!<br>";
                    if ($username) {
                        echo "<strong>Akun staf sudah aktif dan bisa langsung digunakan untuk login!</strong><br>";
                        echo "Username: <strong>" . $username . "</strong>";
                    }
                }
                if ($_GET['success'] === 'updated') echo "Data staf berhasil diperbarui!";
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <?php 
                if ($_GET['error'] === 'staf_memiliki_jadwal') echo "Staf tidak dapat dihapus karena masih terdaftar dalam jadwal turun lapangan!";
                ?>
            </div>
        <?php endif; ?>
        
        <div class="page-header">
            <h2>Kelola Data Staf</h2>
            <a href="tambah_staf.php" class="btn btn-primary">+ Tambah Staf Baru</a>
        </div>
        
        <div class="card">
            <h3>Daftar Staf</h3>
            
            <?php if (empty($staff_list)): ?>
                <p class="text-center">Belum ada staf terdaftar. <a href="tambah_staf.php">Tambah staf pertama</a></p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Username</th>
                            <th>NIP</th>
                            <th>Golongan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($staff_list as $index => $staff): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($staff['nama']); ?></td>
                                <td><?php echo htmlspecialchars($staff['username']); ?></td>
                                <td><?php echo htmlspecialchars($staff['nip'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($staff['golongan'] ?? '-'); ?></td>
                                <td class="action-buttons">
                                    <a href="edit_staf.php?id=<?php echo $staff['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="kelola_staf.php?delete=<?php echo $staff['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Yakin hapus staf ini?')">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
