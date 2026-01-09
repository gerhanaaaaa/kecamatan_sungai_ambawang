<?php
session_start();
require_once 'config.php';
require_once 'auth.php';

// If already logged in, redirect
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: dashboard_admin.php');
    } else {
        header('Location: dashboard_staf.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Jadwal Turun Lapangan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="login-grid">
                <div class="login-visual" aria-hidden="true">
                    <div class="visual-inner">
                        
                        <h2 class="visual-title">Sistem Jadwal</h2>
                        <p class="visual-desc">Jadwal turun lapangan kecatam sungai ambawang</p>
                    </div>
                </div>
                <div class="login-content">
            <header class="login-brand">
                <div class="brand-logo" aria-hidden="true">SJ</div>
                <div class="brand-text">
                    <h1>Sistem Jadwal</h1>
                    <p class="subtitle">Turun Lapangan Kecamatan</p>
                </div>
            </header>

            <?php if (isset($error)): ?>
                <div class="alert alert-error" role="alert">
                    <strong>Kesalahan:</strong> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="login-form" novalidate>
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-with-icon">
                        <input type="text" id="username" name="username" placeholder="Masukkan username" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-with-icon">
                        <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                    </div>
                </div>

                <div class="form-actions" style="align-items:center; justify-content:space-between; gap:12px;">
                    <label class="checkbox-label" style="margin:0; font-weight:500;">
                        <input type="checkbox" name="remember"> Ingat saya
                    </label>
                    <a href="setup_password.php" class="text-muted" style="font-size:0.95rem; text-decoration:none;">Lupa password?</a>
                </div>

                <button type="submit" name="login" class="btn btn-primary">Masuk</button>
            </form>

            <footer class="login-footer">
                <a href="index.php">Kembali ke Beranda</a>
            </footer>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
