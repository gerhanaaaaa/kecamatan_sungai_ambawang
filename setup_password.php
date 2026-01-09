<?php
/**
 * Setup Password untuk Default Users
 * Jalankan file ini sekali untuk mengupdate password default
 */

require_once 'config.php';

$db = getDB();

// Update password untuk admin
$admin_password = password_hash('admin123', PASSWORD_DEFAULT);
$stmt = $db->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
$stmt->execute([$admin_password]);
echo "Password admin updated!\n";

// Update password untuk semua staf
$staf_password = password_hash('staf123', PASSWORD_DEFAULT);
$stmt = $db->prepare("UPDATE users SET password = ? WHERE role = 'staf'");
$stmt->execute([$staf_password]);
echo "Password staf updated!\n";

echo "\nSetup selesai! Password default:\n";
echo "Admin: admin / admin123\n";
echo "Staf: staf1 / staf123\n";
echo "Staf: staf2 / staf123\n";
?>
