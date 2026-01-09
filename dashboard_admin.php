<?php
require_once 'config.php';
requireAdmin();

$db = getDB();
$user = getCurrentUser();

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $db->prepare("DELETE FROM schedules WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: dashboard_admin.php?success=deleted');
    exit;
}

// Handle mark as completed
if (isset($_GET['complete'])) {
    $id = (int)$_GET['complete'];
    $stmt = $db->prepare("UPDATE schedules SET status = 'selesai' WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: dashboard_admin.php?success=completed');
    exit;
}

// Get statistics
// Total Jadwal = jadwal yang belum selesai (excludes 'selesai' status)
$stmt = $db->query("SELECT COUNT(*) as total FROM schedules WHERE status != 'selesai'");
$total_jadwal = $stmt->fetch()['total'];

// Jadwal Terdekat: tampilkan jumlah total jadwal terdekat (hari ini dan ke depan) dengan status 'terjadwal'
$stmt = $db->query("
    SELECT COUNT(*) as total FROM schedules
    WHERE LOWER(status) = 'terjadwal'
      AND DATE(tanggal) >= CURDATE()
");
$jadwal_terjadwal = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM schedules WHERE status = 'selesai'");
$jadwal_selesai = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'staf'");
$jumlah_staff = $stmt->fetch()['total'];

// Get filter from URL
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'all';

// Get all schedules with staff details
$query = "
    SELECT s.*, u.nama as created_by_name
    FROM schedules s
    LEFT JOIN users u ON s.created_by = u.id
";

if ($filter_status === 'terjadwal') {
    $query .= " WHERE s.status = 'terjadwal'";
} elseif ($filter_status === 'selesai') {
    $query .= " WHERE s.status = 'selesai'";
} else {
    // Filter 'all' = tampilkan jadwal aktif (belum selesai)
    $query .= " WHERE s.status != 'selesai'";
}

$query .= " ORDER BY s.tanggal DESC, s.waktu DESC";

$stmt = $db->query($query);
$schedules = $stmt->fetchAll();

// Get staff for each schedule
foreach ($schedules as &$schedule) {
    $stmt = $db->prepare("
        SELECT u.nama, u.nip, u.golongan
        FROM schedule_staff ss
        JOIN users u ON ss.user_id = u.id
        WHERE ss.schedule_id = ?
        ORDER BY u.nama
    ");
    $stmt->execute([$schedule['id']]);
    $schedule['staff_list'] = $stmt->fetchAll();
}
unset($schedule); // Break reference
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sistem Jadwal Turun Lapangan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h1>Sistem Jadwal Turun Lapangan</h1>
            <div class="nav-user">
                <span>Selamat datang, <?php echo htmlspecialchars($user['nama']); ?> (Admin)</span>
                <a href="logout.php" class="btn btn-sm btn-secondary">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="main-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>Menu</h3>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard_admin.php" class="sidebar-link active">📊 Dashboard</a>
                <a href="kelola_staf.php" class="sidebar-link">👥 Kelola Staf</a>
                <a href="tambah_jadwal.php" class="sidebar-link">➕ Tambah Jadwal</a>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="container">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <?php 
                if ($_GET['success'] === 'deleted') echo "Jadwal berhasil dihapus!";
                if ($_GET['success'] === 'completed') echo "Kegiatan ditandai selesai!";
                if ($_GET['success'] === 'added') echo "Jadwal berhasil ditambahkan!";
                if ($_GET['success'] === 'updated') echo "Jadwal berhasil diperbarui!";
                ?>
            </div>
        <?php endif; ?>
        
        <div class="page-header">
            <div>
                <h2>Dashboard Admin</h2>
                <p style="color: rgba(255,255,255,0.9); margin-top: 5px;">Selamat datang, <?php echo htmlspecialchars($user['nama']); ?></p>
            </div>
        </div>
        
        <!-- Statistics Summary Section -->
        <div class="content-summary">
            <div class="stats-grid">
                <a href="dashboard_admin.php?status=all" class="stat-card <?php echo $filter_status === 'all' ? 'active' : ''; ?>">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        📅
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Total Jadwal</div>
                        <div class="stat-value"><?php echo $total_jadwal; ?></div>
                    </div>
                </a>
                
                <a href="dashboard_admin.php?status=terjadwal" class="stat-card <?php echo $filter_status === 'terjadwal' ? 'active' : ''; ?>">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        ⏰
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Jadwal Terdekat</div>
                        <div class="stat-value" style="font-size: 1.3rem; font-weight: 700;">
                            <?php echo $jadwal_terjadwal; ?>
                        </div>
                    </div>
                </a>
                
                <a href="dashboard_admin.php?status=selesai" class="stat-card <?php echo $filter_status === 'selesai' ? 'active' : ''; ?>">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        ✅
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Jadwal Selesai</div>
                        <div class="stat-value"><?php echo $jadwal_selesai; ?></div>
                    </div>
                </a>
                
                <a href="kelola_staf.php" class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                        👥
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Jumlah Staff</div>
                        <div class="stat-value"><?php echo $jumlah_staff; ?></div>
                    </div>
                </a>
            </div>
        </div>
        
        <!-- Main Content Section -->
        <div class="content-main">
        
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="margin: 0;">
                    Daftar Jadwal Turun Lapangan
                    <?php if ($filter_status !== 'all'): ?>
                        <span style="font-size: 0.9rem; font-weight: normal; color: #666;">
                            - <?php echo ucfirst($filter_status); ?>
                        </span>
                    <?php endif; ?>
                </h3>
                <?php if ($filter_status !== 'all'): ?>
                    <a href="dashboard_admin.php" class="btn btn-sm btn-secondary">Tampilkan Semua</a>
                <?php endif; ?>
            </div>
            
            <?php if (empty($schedules)): ?>
                <div style="text-align: center; padding: 40px; color: #999;">
                    <div style="font-size: 3rem; margin-bottom: 10px;">📋</div>
                    <p style="font-size: 1.1rem; margin-bottom: 10px;">
                        <?php if ($filter_status === 'terjadwal'): ?>
                            Belum ada jadwal yang terjadwal.
                        <?php elseif ($filter_status === 'selesai'): ?>
                            Belum ada jadwal yang selesai.
                        <?php else: ?>
                            Belum ada jadwal.
                        <?php endif; ?>
                    </p>
                    <a href="tambah_jadwal.php" class="btn btn-primary">Tambah Jadwal Pertama</a>
                </div>
            <?php else: ?>
                <div class="schedule-list">
                    <?php foreach ($schedules as $schedule): ?>
                        <div class="schedule-card">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 20px; flex-wrap: wrap; gap: 20px;">
                                <div style="flex: 1; min-width: 300px;">
                                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 15px;">
                                        <div style="width: 4px; height: 40px; background: linear-gradient(135deg, var(--primary-color), var(--primary-light)); border-radius: 2px;"></div>
                                        <div>
                                            <h4 style="margin: 0 0 5px 0; color: var(--gray-800); font-size: 1.25rem; font-weight: 700;">
                                                <?php echo date('l, d F Y', strtotime($schedule['tanggal'])); ?>
                                            </h4>
                                            <div style="color: var(--gray-500); font-size: 0.95rem; font-weight: 500;">
                                                🕐 <?php echo date('H:i', strtotime($schedule['waktu'])); ?> WIB
                                            </div>
                                        </div>
                                    </div>
                                    <div style="display: flex; flex-direction: column; gap: 10px; padding-left: 16px;">
                                        <div style="display: flex; align-items: start; gap: 10px;">
                                            <span style="font-size: 1.2rem;">📍</span>
                                            <div>
                                                <div style="font-weight: 600; color: var(--gray-700); margin-bottom: 2px;">Lokasi</div>
                                                <div style="color: var(--gray-600);"><?php echo htmlspecialchars($schedule['lokasi']); ?></div>
                                            </div>
                                        </div>
                                        <div style="display: flex; align-items: start; gap: 10px;">
                                            <span style="font-size: 1.2rem;">📋</span>
                                            <div>
                                                <div style="font-weight: 600; color: var(--gray-700); margin-bottom: 2px;">Kegiatan</div>
                                                <div style="color: var(--gray-600); line-height: 1.6;"><?php echo htmlspecialchars($schedule['kegiatan']); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 12px;">
                                    <span class="badge <?php echo $schedule['status'] === 'selesai' ? 'badge-success' : 'badge-warning'; ?>" style="font-size: 0.875rem; padding: 8px 16px;">
                                        <?php echo ucfirst($schedule['status']); ?>
                                    </span>
                                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                        <a href="detail_jadwal.php?id=<?php echo $schedule['id']; ?>" class="btn btn-sm btn-info">Detail</a>
                                        <?php if ($schedule['status'] !== 'selesai'): ?>
                                            <a href="edit_jadwal.php?id=<?php echo $schedule['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                            <a href="dashboard_admin.php?complete=<?php echo $schedule['id']; ?>" 
                                               class="btn btn-sm btn-success" 
                                               onclick="return confirm('Tandai kegiatan sebagai selesai?')">Selesai</a>
                                            <a href="dashboard_admin.php?delete=<?php echo $schedule['id']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Yakin hapus jadwal ini?')">Hapus</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div style="border-top: 2px solid var(--gray-100); padding-top: 20px; margin-top: 20px;">
                                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                                    <span style="font-size: 1.3rem;">👥</span>
                                    <strong style="color: var(--gray-800); font-size: 1.05rem;">Staf yang Ikut (<?php echo count($schedule['staff_list']); ?> orang)</strong>
                                </div>
                                <?php if (empty($schedule['staff_list'])): ?>
                                    <div style="padding: 20px; background: var(--gray-50); border-radius: 12px; text-align: center; color: var(--gray-500);">
                                        Belum ada staf yang ditugaskan.
                                    </div>
                                <?php else: ?>
                                    <div style="overflow-x: auto;">
                                        <table class="table" style="margin: 0;">
                                            <thead>
                                                <tr>
                                                    <th style="padding: 12px; background: var(--gray-50); font-weight: 600; color: var(--gray-700);">No</th>
                                                    <th style="padding: 12px; background: var(--gray-50); font-weight: 600; color: var(--gray-700);">Nama</th>
                                                    <th style="padding: 12px; background: var(--gray-50); font-weight: 600; color: var(--gray-700);">NIP</th>
                                                    <th style="padding: 12px; background: var(--gray-50); font-weight: 600; color: var(--gray-700);">Golongan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($schedule['staff_list'] as $index => $staff): ?>
                                                    <tr>
                                                        <td style="padding: 12px; color: var(--gray-600);"><?php echo $index + 1; ?></td>
                                                        <td style="padding: 12px; font-weight: 600; color: var(--gray-800);"><?php echo htmlspecialchars($staff['nama']); ?></td>
                                                        <td style="padding: 12px; color: var(--gray-600); font-family: 'Courier New', monospace;"><?php echo htmlspecialchars($staff['nip'] ?? '-'); ?></td>
                                                        <td style="padding: 12px; color: var(--gray-600);"><?php echo htmlspecialchars($staff['golongan'] ?? '-'); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            </div>
            </div>
        </div>
        </div>
    </div>
</body>
</html>
