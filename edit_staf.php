<?php
require_once 'config.php';
requireAdmin();

$db = getDB();
$error = '';

// Get staff ID
$staff_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get staff data
$stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND role = 'staf'");
$stmt->execute([$staff_id]);
$staff = $stmt->fetch();

if (!$staff) {
    header('Location: kelola_staf.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $nama = trim($_POST['nama'] ?? '');
    $nip = trim($_POST['nip'] ?? '');
    $golongan = trim($_POST['golongan'] ?? '');
    
    if (empty($username) || empty($nama)) {
        $error = "Username dan Nama harus diisi!";
    } elseif (!empty($password) && strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } else {
        try {
            // Check if username already exists (exclude current user)
            $stmt = $db->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $stmt->execute([$username, $staff_id]);
            if ($stmt->fetch()) {
                $error = "Username sudah digunakan!";
            } else {
                // Update staff
                if (!empty($password)) {
                    // Update with new password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    if ($hashed_password === false) {
                        $error = "Error: Gagal mengenkripsi password!";
                    } else {
                        $stmt = $db->prepare("
                            UPDATE users 
                            SET username = ?, password = ?, nama = ?, nip = ?, golongan = ?
                            WHERE id = ? AND role = 'staf'
                        ");
                        $stmt->execute([
                            $username, 
                            $hashed_password, 
                            $nama, 
                            !empty($nip) ? $nip : null, 
                            !empty($golongan) ? $golongan : null,
                            $staff_id
                        ]);
                        
                        header('Location: kelola_staf.php?success=updated');
                        exit;
                    }
                } else {
                    // Update without changing password
                    $stmt = $db->prepare("
                        UPDATE users 
                        SET username = ?, nama = ?, nip = ?, golongan = ?
                        WHERE id = ? AND role = 'staf'
                    ");
                    $stmt->execute([
                        $username, 
                        $nama, 
                        !empty($nip) ? $nip : null, 
                        !empty($golongan) ? $golongan : null,
                        $staff_id
                    ]);
                    
                    header('Location: kelola_staf.php?success=updated');
                    exit;
                }
            }
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Staf - Sistem Jadwal Turun Lapangan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h1>Sistem Jadwal Turun Lapangan</h1>
            <div class="nav-user">
                <a href="kelola_staf.php" class="btn btn-sm btn-secondary">← Kembali</a>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <div class="page-header">
            <h2>Edit Data Staf</h2>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="card">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="nama">Nama Lengkap *</label>
                    <input type="text" id="nama" name="nama" 
                           placeholder="Contoh: Budi Santoso" 
                           value="<?php echo htmlspecialchars($staff['nama']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="nip">NIP</label>
                    <input type="text" id="nip" name="nip" 
                           placeholder="Contoh: 198012012010011002" 
                           value="<?php echo htmlspecialchars($staff['nip'] ?? ''); ?>">
                    <small class="text-muted">Nomor Induk Pegawai (opsional)</small>
                </div>
                
                <div class="form-group">
                    <label for="golongan">Golongan</label>
                    <input type="text" id="golongan" name="golongan" 
                           placeholder="Contoh: III/a, IV/b" 
                           value="<?php echo htmlspecialchars($staff['golongan'] ?? ''); ?>">
                    <small class="text-muted">Golongan pegawai (opsional)</small>
                </div>
                
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" 
                           placeholder="Contoh: budisantoso" 
                           value="<?php echo htmlspecialchars($staff['username']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password Baru</label>
                    <input type="password" id="password" name="password" 
                           placeholder="Kosongkan jika tidak ingin mengubah password">
                    <small class="text-muted">Biarkan kosong jika tidak ingin mengubah password</small>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="update" class="btn btn-primary">Update Data Staf</button>
                    <a href="kelola_staf.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
