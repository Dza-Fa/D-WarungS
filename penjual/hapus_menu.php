<?php
/**
 * Hapus Menu
 */

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pedagang') {
    header('Location: /D-WarungS/auth/login.php');
    exit();
}

require_once '../config/db.php';

// Get menu_id dari URL
$menu_id = intval($_GET['id'] ?? 0);

if (!$menu_id) {
    header('Location: dashboard.php');
    exit();
}

// Get warung milik penjual
$warung = getRow("SELECT * FROM warung WHERE pemilik_id = ?", [$_SESSION['user_id']]);

if (!$warung) {
    header('Location: dashboard.php');
    exit();
}

// Get menu
$menu = getRow("SELECT * FROM menu WHERE id = ? AND warung_id = ?", [$menu_id, $warung['id']]);

if (!$menu) {
    header('Location: dashboard.php');
    exit();
}

// Delete menu dengan prepared statement
$query = "DELETE FROM menu WHERE id = ?";
executeUpdate($query, [$menu_id]);

// Redirect ke dashboard
header('Location: dashboard.php?success=Menu berhasil dihapus');
exit();
?>
