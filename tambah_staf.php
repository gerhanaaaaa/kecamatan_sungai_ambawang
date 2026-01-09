<?php
require_once 'config.php';
requireAdmin();

$db = getDB();
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $nama = trim($_POST['nama'] ?? '');
    $nip = trim($_POST['nip'] ?? '');
    $golongan = trim($_POST['golongan'] ?? '');
    
    if (empty($username) || empty($password) || empty($nama)) {
        $error = "Username, Password, dan Nama harus diisi!";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } else {
        try {
            // Check if username already exists
            $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $error = "Username sudah digunakan!";
            } else {
                // Hash password menggunakan PASSWORD_DEFAULT (bcrypt)
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Validasi hash berhasil
                if ($hashed_password === false) {
                    $error = "Error: Gagal mengenkripsi password!";
                } else {
                    // Insert new staff
                    $stmt = $db->prepare("
                        INSERT INTO users (username, password, nama, nip, golongan, role) 
                        VALUES (?, ?, ?, ?, ?, 'staf')
                    ");
                    $stmt->execute([
                        $username, 
                        $hashed_password, 
                        $nama, 
                        !empty($nip) ? $nip : null, 
                        !empty($golongan) ? $golongan : null
                    ]);
                    
                    // Verifikasi bahwa data berhasil disimpan
                    $stmt = $db->prepare("SELECT id, username FROM users WHERE username = ?");
                    $stmt->execute([$username]);
                    $new_user = $stmt->fetch();
                    
                    if ($new_user) {
                        // Redirect dengan pesan sukses
                        header('Location: kelola_staf.php?success=added&username=' . urlencode($username));
                        exit;
                    } else {
                        $error = "Error: Gagal menyimpan data staf!";
                    }
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
    <title>Tambah Staf - Sistem Jadwal Turun Lapangan</title>
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
            <h2>Tambah Staf Baru</h2>
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
                           value="<?php echo htmlspecialchars($_POST['nama'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="nip">NIP</label>
                    <input type="text" id="nip" name="nip" 
                           placeholder="Contoh: 198012012010011002" 
                           value="<?php echo htmlspecialchars($_POST['nip'] ?? ''); ?>">
                    <small class="text-muted">Nomor Induk Pegawai (opsional)</small>
                </div>
                
                <div class="form-group">
                    <label for="golongan">Golongan</label>
                    <input type="text" id="golongan" name="golongan" 
                           placeholder="Contoh: III/a, IV/b" 
                           value="<?php echo htmlspecialchars($_POST['golongan'] ?? ''); ?>">
                    <small class="text-muted">Golongan pegawai (opsional)</small>
                </div>
                
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" 
                           placeholder="Contoh: budisantoso" 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" 
                           placeholder="Minimal 6 karakter" required>
                    <small class="text-muted">Password untuk login (minimal 6 karakter)</small>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="tambah" class="btn btn-primary">Simpan Staf</button>
                    <a href="kelola_staf.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
