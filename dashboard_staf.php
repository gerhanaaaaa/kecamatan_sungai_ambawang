<?php
require_once 'config.php';
requireLogin();

if ($_SESSION['role'] === 'admin') {
    header('Location: dashboard_admin.php');
    exit;
}

$db = getDB();
$user = getCurrentUser();

// Get schedules assigned to this staff
$stmt = $db->prepare("
    SELECT s.*, u.nama as created_by_name,
           ss.konfirmasi, ss.laporan
    FROM schedules s
    JOIN schedule_staff ss ON s.id = ss.schedule_id
    LEFT JOIN users u ON s.created_by = u.id
    WHERE ss.user_id = ?
    ORDER BY s.tanggal DESC, s.waktu DESC
");
$stmt->execute([$user['id']]);
$my_schedules = $stmt->fetchAll();

// Get all schedules (for viewing all)
$stmt = $db->query("
    SELECT s.*, u.nama as created_by_name,
           COUNT(ss.id) as jumlah_staf
    FROM schedules s
    LEFT JOIN users u ON s.created_by = u.id
    LEFT JOIN schedule_staff ss ON s.id = ss.schedule_id
    GROUP BY s.id
    ORDER BY s.tanggal DESC, s.waktu DESC
");
$all_schedules = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Staf - Sistem Jadwal Turun Lapangan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h1>Sistem Jadwal Turun Lapangan</h1>
            <div class="nav-user">
                <span>Selamat datang, <?php echo htmlspecialchars($user['nama']); ?></span>
                <a href="logout.php" class="btn btn-sm btn-secondary">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <?php if (isset($_GET['konfirmasi'])): ?>
            <div class="alert alert-success">Konfirmasi berhasil disimpan!</div>
        <?php endif; ?>
        
        <div class="page-header">
            <h2>Dashboard Staf</h2>
        </div>
        
        <div class="tabs">
            <button class="tab-btn active" onclick="showTab('my-schedules')">Jadwal Saya</button>
            <button class="tab-btn" onclick="showTab('all-schedules')">Semua Jadwal</button>
            <button class="tab-btn" onclick="showTab('finished-schedules')">Jadwal Selesai</button>
        </div>
        
        <!-- My Schedules Tab -->
        <div id="my-schedules" class="tab-content active">
            <div class="card">
                <h3>Jadwal Turun Lapangan Saya</h3>
                
                <?php if (empty($my_schedules)): ?>
                    <p class="text-center">Anda belum memiliki jadwal turun lapangan.</p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Waktu</th>
                                <th>Lokasi</th>
                                <th>Kegiatan</th>
                                <th>Status</th>
                                <th>Konfirmasi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($my_schedules as $schedule): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($schedule['tanggal'])); ?></td>
                                    <td><?php echo date('H:i', strtotime($schedule['waktu'])); ?></td>
                                    <td><?php echo htmlspecialchars($schedule['lokasi']); ?></td>
                                    <td><?php echo htmlspecialchars($schedule['kegiatan']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $schedule['status'] === 'selesai' ? 'badge-success' : 'badge-warning'; ?>">
                                            <?php echo ucfirst($schedule['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge 
                                            <?php 
                                            if ($schedule['konfirmasi'] === 'hadir') echo 'badge-success';
                                            elseif ($schedule['konfirmasi'] === 'tidak_hadir') echo 'badge-danger';
                                            else echo 'badge-warning';
                                            ?>">
                                            <?php 
                                            if ($schedule['konfirmasi'] === 'hadir') echo 'Hadir';
                                            elseif ($schedule['konfirmasi'] === 'tidak_hadir') echo 'Tidak Hadir';
                                            else echo 'Pending';
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="detail_jadwal.php?id=<?php echo $schedule['id']; ?>" 
                                           class="btn btn-sm btn-info">Detail</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- All Schedules Tab -->
        <div id="all-schedules" class="tab-content">
            <div class="card">
                <h3>Semua Jadwal Turun Lapangan</h3>
                
                <?php if (empty($all_schedules)): ?>
                    <p class="text-center">Belum ada jadwal.</p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Waktu</th>
                                <th>Lokasi</th>
                                <th>Kegiatan</th>
                                <th>Jumlah Staf</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_schedules as $schedule): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($schedule['tanggal'])); ?></td>
                                    <td><?php echo date('H:i', strtotime($schedule['waktu'])); ?></td>
                                    <td><?php echo htmlspecialchars($schedule['lokasi']); ?></td>
                                    <td><?php echo htmlspecialchars($schedule['kegiatan']); ?></td>
                                    <td class="text-center"><?php echo $schedule['jumlah_staf']; ?></td>
                                    <td>
                                        <span class="badge <?php echo $schedule['status'] === 'selesai' ? 'badge-success' : 'badge-warning'; ?>">
                                            <?php echo ucfirst($schedule['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="detail_jadwal.php?id=<?php echo $schedule['id']; ?>" 
                                           class="btn btn-sm btn-info">Detail</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Finished Schedules Tab -->
        <div id="finished-schedules" class="tab-content">
            <div class="card">
                <h3>Jadwal Selesai</h3>
                <?php
                // Filter jadwal selesai milik staf login
                $finished_schedules = array_filter($my_schedules, function($s) {
                    return isset($s['status']) && $s['status'] === 'selesai';
                });
                ?>
                <?php if (empty($finished_schedules)): ?>
                    <p class="text-center">Belum ada jadwal selesai.</p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Waktu</th>
                                <th>Lokasi</th>
                                <th>Kegiatan</th>
                                <th>Status</th>
                                <th>Konfirmasi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($finished_schedules as $schedule): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($schedule['tanggal'])); ?></td>
                                    <td><?php echo date('H:i', strtotime($schedule['waktu'])); ?></td>
                                    <td><?php echo htmlspecialchars($schedule['lokasi']); ?></td>
                                    <td><?php echo htmlspecialchars($schedule['kegiatan']); ?></td>
                                    <td><span class="badge badge-success">Selesai</span></td>
                                    <td>
                                        <span class="badge 
                                            <?php 
                                            if ($schedule['konfirmasi'] === 'hadir') echo 'badge-success';
                                            elseif ($schedule['konfirmasi'] === 'tidak_hadir') echo 'badge-danger';
                                            else echo 'badge-warning';
                                            ?>">
                                            <?php 
                                            if ($schedule['konfirmasi'] === 'hadir') echo 'Hadir';
                                            elseif ($schedule['konfirmasi'] === 'tidak_hadir') echo 'Tidak Hadir';
                                            else echo 'Pending';
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="detail_jadwal.php?id=<?php echo $schedule['id']; ?>" class="btn btn-sm btn-info">Detail</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        function showTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            // Remove active class from all buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            // Show selected tab
            document.getElementById(tabName).classList.add('active');
            // Add active class to the correct button
            document.querySelectorAll('.tab-btn').forEach(btn => {
                if (btn.getAttribute('onclick').includes(tabName)) {
                    btn.classList.add('active');
                }
            });
        }
    </script>
</body>
</html>
