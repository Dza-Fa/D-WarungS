<?php
/**
 * Dashboard Kasir - Validasi Pembayaran
 */

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'kasir') {
    header('Location: /D-WarungS/auth/login.php');
    exit();
}

require_once '../config/db.php';
require_once '../config/security.php';

$page_title = 'Dashboard Kasir';

$message = '';
$error = '';

// Handle mark notification as read (AJAX)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_read'])) {
    $notification_id = intval($_POST['notification_id'] ?? 0);
    if ($notification_id) {
        execute("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?", [$notification_id, $_SESSION['user_id']]);
    }
    exit;
}

// Handle AJAX Updates (Professional Way: Get Data Only)
if (isset($_GET['get_updates'])) {
    // Bersihkan buffer output untuk mencegah whitespace yang merusak JSON
    if (ob_get_length()) ob_clean();

    // 1. Get Updated Stats
    $menunggu_count = getRow("SELECT COUNT(*) as count FROM orders WHERE status = 'menunggu'")['count'] ?? 0;
    $dibayar_total = getRow("SELECT SUM(total_harga) as total FROM orders WHERE status = 'dibayar'")['total'] ?? 0;
    $semua_total = getRow("SELECT SUM(total_harga) as total FROM orders WHERE status IN ('dibayar', 'diproses', 'siap', 'menunggu')")["total"] ?? 0;
    
    // 2. Generate Table HTML
    $query = "SELECT * FROM orders WHERE status = 'menunggu' ORDER BY waktu_pesan ASC";
    $pesanan = getRows($query);
    
    ob_start();
    foreach ($pesanan as $p) {
            $pembeli = getRow("SELECT nama FROM users WHERE id = ?", [$p['pembeli_id']]);
            $pembeli_nama = $pembeli ? esc($pembeli['nama']) : 'Unknown';
            $items_count = getRow("SELECT COUNT(*) as count FROM order_items WHERE order_id = ?", [$p['id']])['count'] ?? 0;
            
            echo '<tr>';
            echo '<td><strong>#' . $p['id'] . '</strong><br><small style="color: #999;">' . $items_count . ' item</small></td>';
            echo '<td>' . $pembeli_nama . '</td>';
            echo '<td>' . formatDateTime($p['waktu_pesan']) . '</td>';
            echo '<td style="text-align: right; font-weight: 600; font-size: 1.1rem;">' . formatCurrency($p['total_harga']) . '</td>';
            echo '<td style="text-align: center;">
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="csrf_token" value="' . generate_csrf_token() . '">
                        <input type="hidden" name="order_id" value="' . $p['id'] . '">
                        <button type="submit" name="validasi" class="btn btn-success btn-sm" onclick="return confirm(\'Validasi pembayaran pesanan ini?\')">
                            ✓ Validasi
                        </button>
                    </form>
                  </td>';
            echo '</tr>';
    }
    $html = ob_get_clean();
    
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, no-store, must-revalidate'); // Prevent caching
    echo json_encode([
        'stats' => [
            'menunggu' => $menunggu_count,
            'dibayar' => formatCurrency($dibayar_total),
            'total' => formatCurrency($semua_total)
        ],
        'html' => $html,
        'isEmpty' => empty($pesanan)
    ]);
    exit;
}

// Handle validasi pembayaran
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['validasi'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Token keamanan tidak valid.';
    } else {
    $order_id = intval($_POST['order_id'] ?? 0);
    
    // Get pembeli_id sebelum update
    $order_info = getRow("SELECT pembeli_id FROM orders WHERE id = ?", [$order_id]);
    
    // Update order status ke 'dibayar'
    $query = "UPDATE orders SET status = 'dibayar' WHERE id = ? AND status = 'menunggu'";
    if (executeUpdate($query, [$order_id])) {
        // Kirim notifikasi ke pembeli
        if ($order_info) {
            $msg = "Pembayaran pesanan #$order_id telah divalidasi/dibayar";
            execute("INSERT INTO notifications (user_id, order_id, type, message, role, is_read) VALUES (?, ?, 'order_update', ?, 'pembeli', 0)", 
                [$order_info['pembeli_id'], $order_id, $msg]);
        }
        
        // Kirim notifikasi ke penjual terkait agar daftar pesanan mereka terupdate otomatis
        $sellers = getRows("
            SELECT DISTINCT w.pemilik_id 
            FROM order_items oi
            JOIN menu m ON oi.menu_id = m.id
            JOIN warung w ON m.warung_id = w.id
            WHERE oi.order_id = ?
        ", [$order_id]);

        foreach ($sellers as $seller) {
            $msg_seller = "Pesanan #$order_id telah dibayar. Silakan proses.";
            execute("INSERT INTO notifications (user_id, order_id, type, message, role, is_read) VALUES (?, ?, 'order_update', ?, 'pedagang', 0)", 
                [$seller['pemilik_id'], $order_id, $msg_seller]);
        }
        
        $message = 'Pembayaran berhasil divalidasi!';
    } else {
        $error = 'Gagal memvalidasi pembayaran!';
    }
    }
}

// Get pesanan yang menunggu pembayaran
$query = "SELECT * FROM orders WHERE status = 'menunggu' ORDER BY waktu_pesan ASC";
$pesanan = getRows($query);

// Get statistics
$menunggu_count = count($pesanan);
$dibayar_total = getRow("SELECT SUM(total_harga) as total FROM orders WHERE status = 'dibayar'")['total'] ?? 0;
$semua_total = getRow("SELECT SUM(total_harga) as total FROM orders WHERE status IN ('dibayar', 'diproses', 'siap', 'menunggu')")["total"] ?? 0;
?>
<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/sidebar.php'; ?>

<div class="page-header">
    <h1 class="page-title">💰 Dashboard Kasir</h1>
    <p class="page-subtitle">Validasi pembayaran pesanan</p>
</div>

<?php if ($message): ?>
    <div class="alert alert-success">
        ✓ <?php echo esc($message); ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger">
        ✗ <?php echo esc($error); ?>
    </div>
<?php endif; ?>

<!-- Statistics -->
<div class="grid grid-3" style="margin-bottom: 2rem;">
    <div class="card">
        <div style="text-align: center; padding: 1rem;">
            <div id="stat-menunggu" style="font-size: 2rem; font-weight: 700; color: #f39c12;">
                <?php echo $menunggu_count; ?>
            </div>
            <div style="color: #666; margin-top: 0.5rem;">Pembayaran Menunggu</div>
        </div>
    </div>
    
    <div class="card">
        <div style="text-align: center; padding: 1rem;">
            <div id="stat-dibayar" style="font-size: 2rem; font-weight: 700; color: #27ae60;">
                <?php echo formatCurrency($dibayar_total); ?>
            </div>
            <div style="color: #666; margin-top: 0.5rem;">Sudah Dibayar</div>
        </div>
    </div>
    
    <div class="card">
        <div style="text-align: center; padding: 1rem;">
            <div id="stat-total" style="font-size: 2rem; font-weight: 700; color: #667eea;">
                <?php echo formatCurrency($semua_total); ?>
            </div>
            <div style="color: #666; margin-top: 0.5rem;">Total Transaksi</div>
        </div>
    </div>
</div>

<!-- Pesanan Menunggu -->
<div class="card">
    <div class="card-header">
        <h3>Pesanan Menunggu Pembayaran</h3>
    </div>
    
    <!-- Empty State -->
    <div id="orders-empty-state" class="card-body" style="display: <?php echo empty($pesanan) ? 'block' : 'none'; ?>">
            <div class="empty-state" style="padding: 2rem;">
                <div class="empty-state-icon">✅</div>
                <div class="empty-state-text">Semua pembayaran sudah divalidasi</div>
            </div>
    </div>
    
    <!-- Table Container -->
    <div id="orders-table-container" class="card-body" style="overflow-x: auto; display: <?php echo empty($pesanan) ? 'none' : 'block'; ?>;">
            <table class="table">
                <thead>
                    <tr>
                        <th>No. Pesanan</th>
                        <th>Pembeli</th>
                        <th>Waktu Pesan</th>
                        <th style="text-align: right;">Total</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="orders-table-body">
                    <?php foreach ($pesanan as $p): ?>
                        <?php
                        $pembeli = getRow("SELECT nama FROM users WHERE id = ?", [$p['pembeli_id']]);
                        $pembeli_nama = $pembeli ? esc($pembeli['nama']) : 'Unknown';
                        $items_count = getRow("SELECT COUNT(*) as count FROM order_items WHERE order_id = ?", [$p['id']])['count'] ?? 0;
                        ?>
                        <tr>
                            <td>
                                <strong>#<?php echo $p['id']; ?></strong><br>
                                <small style="color: #999;"><?php echo $items_count; ?> item</small>
                            </td>
                            <td><?php echo $pembeli_nama; ?></td>
                            <td><?php echo formatDateTime($p['waktu_pesan']); ?></td>
                            <td style="text-align: right; font-weight: 600; font-size: 1.1rem;">
                                <?php echo formatCurrency($p['total_harga']); ?>
                            </td>
                            <td style="text-align: center;">
                                <form method="POST" style="display: inline;">
                                    <?php csrf_field(); ?>
                                    <input type="hidden" name="order_id" value="<?php echo $p['id']; ?>">
                                    <button type="submit" name="validasi" class="btn btn-success btn-sm" onclick="return confirm('Validasi pembayaran pesanan ini?')">
                                        ✓ Validasi
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
</div>

</main>

<script src="/D-WarungS/assets/js/realtime-notifications.js?v=<?php echo time(); ?>"></script>
<script>
    // Inisialisasi realtime untuk kasir dashboard
    document.addEventListener('DOMContentLoaded', function() {
        // Request permission untuk browser notifications
        RealtimeNotifications.requestNotificationPermission();
        
        const realtime = new RealtimeNotifications({
            endpoint: '/D-WarungS/api/realtime-notifications.php',
            reconnectInterval: 2000,
            onNotification: function(notif) {
                console.log('📩 Pesanan baru masuk:', notif);
                
                // Untuk kasir, refresh halaman saat ada pesanan baru
                if (notif.type === 'order' || notif.type === 'order_cancel' || (notif.pesanan_id && notif.status === 'menunggu')) {
                    // Tampilkan notifikasi visual
                    showNewOrderNotif(notif);
                    
                    // Professional Way: Update data tanpa reload halaman
                    fetch('dashboard.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'mark_read=1&notification_id=' + notif.id
                    }).then(() => {
                        // Fetch updated data (HTML & Stats)
                        // Tambahkan timestamp untuk mencegah caching browser
                        return fetch('dashboard.php?get_updates=1&t=' + new Date().getTime());
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        // Update Statistics
                        document.getElementById('stat-menunggu').innerText = data.stats.menunggu;
                        document.getElementById('stat-dibayar').innerText = data.stats.dibayar;
                        document.getElementById('stat-total').innerText = data.stats.total;
                        
                        // Update Table Content
                        const emptyState = document.getElementById('orders-empty-state');
                        const tableContainer = document.getElementById('orders-table-container');
                        const tableBody = document.getElementById('orders-table-body');
                        
                        if (data.isEmpty) {
                            emptyState.style.display = 'block';
                            tableContainer.style.display = 'none';
                            tableBody.innerHTML = '';
                        } else {
                            emptyState.style.display = 'none';
                            tableContainer.style.display = 'block';
                            tableBody.innerHTML = data.html;
                        }
                    })
                    .catch(error => console.error('Error updating dashboard:', error));
                }
            }
        });
        
        realtime.connect();
        
        window.addEventListener('beforeunload', function() {
            realtime.disconnect();
        });
        
        function showNewOrderNotif(notif) {
            const notifEl = document.createElement('div');
            
            let title = '🎉 Pesanan Baru!';
            let bgColor = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
            
            if (notif.type === 'order_cancel') {
                title = '❌ Pesanan Dibatalkan';
                bgColor = 'linear-gradient(135deg, #ff6b6b 0%, #ee5253 100%)';
            }
            
            notifEl.style.cssText = `
                position: fixed;
                top: 20px;
                left: 20px;
                background: ${bgColor};
                color: white;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 8px 32px rgba(0,0,0,0.2);
                z-index: 9999;
                max-width: 350px;
                animation: slideIn 0.3s ease-out;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto;
            `;
            
            notifEl.innerHTML = `
                <div style="font-weight: 600; font-size: 16px; margin-bottom: 8px;">${title}</div>
                <div style="font-size: 14px; opacity: 0.9;">ID Pesanan: #${notif.pesanan_id}</div>
                <div style="font-size: 13px; opacity: 0.85; margin-top: 4px;">Total: Rp ${(notif.total_harga || 0).toLocaleString('id-ID')}</div>
                <div style="font-size: 12px; opacity: 0.8; margin-top: 8px; cursor: pointer; text-decoration: underline;">Data diperbarui otomatis</div>
            `;
            
            document.body.appendChild(notifEl);
            
            setTimeout(() => {
                notifEl.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => notifEl.remove(), 300);
            }, 6000);
        }
    });
</script>

<style>
    @keyframes slideIn {
        from {
            transform: translateX(-400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(-400px);
            opacity: 0;
        }
    }
</style>

<?php require_once '../includes/footer.php'; ?>
