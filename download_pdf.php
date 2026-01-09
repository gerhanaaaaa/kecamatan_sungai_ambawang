<?php
require_once 'config.php';
requireLogin();

$db = getDB();
$schedule_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get schedule data
$stmt = $db->prepare("SELECT file_pdf FROM schedules WHERE id = ?");
$stmt->execute([$schedule_id]);
$schedule = $stmt->fetch();

if (!$schedule || empty($schedule['file_pdf'])) {
    header('HTTP/1.0 404 Not Found');
    die('File tidak ditemukan');
}

$file_path = __DIR__ . '/uploads/' . $schedule['file_pdf'];

if (!file_exists($file_path)) {
    header('HTTP/1.0 404 Not Found');
    die('File tidak ditemukan di server');
}

// Set headers for PDF download
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . basename($schedule['file_pdf']) . '"');
header('Content-Length: ' . filesize($file_path));
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

// Output the file
readfile($file_path);
exit;
?>
