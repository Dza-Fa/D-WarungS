<?php
/**
 * Realtime Notifications API (AJAX Polling Version)
 * Lebih efisien untuk server PHP standar (XAMPP/Shared Hosting)
 * daripada SSE yang menahan proses PHP.
 */

session_start();
require_once '../config/db.php';

// Set headers JSON
header('Content-Type: application/json');
header('Cache-Control: no-cache');

// Cek authentification
if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$last_id = isset($_GET['last_id']) ? intval($_GET['last_id']) : 0;

try {
    // Ambil notifikasi BARU saja (id > last_id)
    // Join dengan orders untuk mendapatkan total_harga dan status terkini
    // Tambahkan filter is_read = 0 agar hanya mengambil notifikasi aktif/baru
    $query = "SELECT n.*, o.total_harga, o.status as order_status, o.id as real_order_id
              FROM notifications n
              LEFT JOIN orders o ON n.order_id = o.id
              WHERE n.user_id = ? AND n.id > ? AND n.is_read = 0
              ORDER BY n.created_at ASC 
              LIMIT 20";
    
    $notifications = getRows($query, [$user_id, $last_id]);
    
    // Format data untuk frontend
    $data = [];
    $max_id = $last_id;
    
    foreach ($notifications as $n) {
        $data[] = [
            'id' => $n['id'],
            'type' => $n['type'],
            'message' => $n['message'],
            'pesanan_id' => $n['order_id'],
            'total_harga' => $n['total_harga'],
            'status' => $n['order_status'] ?? null, // Status terkini dari tabel orders
            'is_read' => $n['is_read'],
            'created_at' => $n['created_at']
        ];
        $max_id = max($max_id, $n['id']);
    }
    
    echo json_encode([
        'success' => true,
        'notifications' => $data,
        'last_id' => $max_id
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
