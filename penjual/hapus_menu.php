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
require_once '../config/security.php';

// Hanya izinkan POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit();
}

if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    die('Akses tidak sah (Invalid CSRF Token)');
}

$menu_id = intval($_POST['id'] ?? 0);

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

// Cek apakah menu sudah pernah dipesan (ada di order_items)
$is_ordered = getRow("SELECT id FROM order_items WHERE menu_id = ? LIMIT 1", [$menu_id]);

if ($is_ordered) {
    // Soft delete (set status_aktif = 0) karena data historis pesanan harus tetap ada
    $query = "UPDATE menu SET status_aktif = 0 WHERE id = ?";
    executeUpdate($query, [$menu_id]);
} else {
    // Hard delete (hapus permanen) karena belum ada transaksi
    // Hapus file gambar jika ada
    if (!empty($menu['gambar']) && file_exists('../assets/uploads/menu/' . $menu['gambar'])) {
        unlink('../assets/uploads/menu/' . $menu['gambar']);
    }
    
    $query = "DELETE FROM menu WHERE id = ?";
    executeUpdate($query, [$menu_id]);
}

// Redirect ke dashboard
header('Location: dashboard.php?success=Menu berhasil dihapus');
exit();
?>
