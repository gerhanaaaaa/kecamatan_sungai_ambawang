<?php
require_once 'config.php';
requireLogin();

if ($_SESSION['role'] !== 'staf') {
    header('Location: dashboard_admin.php');
    exit;
}

$db = getDB();
$schedule_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$status = isset($_GET['status']) ? $_GET['status'] : '';

if ($status === 'hadir' || $status === 'tidak_hadir') {
    // Verify user is assigned to this schedule
    $stmt = $db->prepare("SELECT * FROM schedule_staff WHERE schedule_id = ? AND user_id = ?");
    $stmt->execute([$schedule_id, $_SESSION['user_id']]);
    
    if ($stmt->fetch()) {
        $stmt = $db->prepare("
            UPDATE schedule_staff 
            SET konfirmasi = ? 
            WHERE schedule_id = ? AND user_id = ?
        ");
        $stmt->execute([$status, $schedule_id, $_SESSION['user_id']]);
        header('Location: detail_jadwal.php?id=' . $schedule_id . '&konfirmasi=success');
    } else {
        header('Location: dashboard_staf.php');
    }
    exit;
}

header('Location: dashboard_staf.php');
exit;
?>
