<?php
session_start();
require_once 'config.php';

// If already logged in, redirect to dashboard
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
            <title>Selamat Datang - Sistem Jadwal Turun Lapangan</title>
            <link rel="stylesheet" href="style.css">
            <style>
                :root { --hero-gradient: linear-gradient(135deg,#516be2 0%,#8b5cf6 55%,#f093fb 100%); }
                html,body{height:100%;}
                body{margin:0;font-family: 'Poppins', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;}
                .landing-page{min-height:100vh;display:flex;align-items:center;justify-content:center;background:var(--hero-gradient);}
                .welcome-card{max-width:920px;width:95%;padding:56px 40px;border-radius:16px;background:linear-gradient(180deg, rgba(255,255,255,0.06), rgba(255,255,255,0.04));backdrop-filter: blur(6px);box-shadow:0 18px 50px rgba(15,23,42,0.18);text-align:center;color:white}
                .welcome-card h1{margin:0 0 14px;font-size:2rem;line-height:1.15;font-weight:700}
                .welcome-card p{margin:0 0 26px;font-size:1.05rem;opacity:0.95}
                .cta-buttons{display:flex;justify-content:center}
                .cta-buttons .btn{padding:14px 36px;font-size:1rem;border-radius:10px}
                @media (max-width:600px){.welcome-card{padding:36px 20px}.welcome-card h1{font-size:1.4rem}.welcome-card p{font-size:0.98rem}}
            </style>
        </head>
        <body>
            <main class="landing-page" role="main">
                <section class="welcome-card" aria-labelledby="welcome-title">
                    <h1 id="welcome-title">Selamat Datang di Sistem Jadwal Turun Lapangan</h1>
                    <p>Platform terpusat untuk mengelola dan memantau jadwal kegiatan lapangan secara profesional dan aman.</p>
                    <div class="cta-buttons">
                        <a href="login.php" class="btn btn-primary">Masuk ke Sistem</a>
                    </div>
                </section>
            </main>
        </body>
        </html>
                        Masuk ke Sistem
                    </a>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> Sistem Jadwal Turun Lapangan - Kecamatan</p>
            <p style="font-size: 0.9rem; opacity: 0.8; margin-top: 5px;">
                Dibangun dengan PHP Native untuk kemudahan dan keamanan
            </p>
        </div>
    </div>
</body>
</html>
