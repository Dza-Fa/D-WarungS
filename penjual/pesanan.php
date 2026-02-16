<?php
/**
 * Pesanan Masuk untuk Penjual
 */

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pedagang') {
    header('Location: /D-WarungS/auth/login.php');
    exit();
}

require_once '../config/db.php';
require_once '../config/queries.php';
require_once '../config/security.php';

$page_title = 'Pesanan Masuk';

// Get warung milik penjual
$warung = getRow("SELECT * FROM warung WHERE pemilik_id = ?", [$_SESSION['user_id']]);

if (!$warung) {
    header('Location: dashboard.php');
    exit();
}

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
    $stats = [
        'dibayar' => getRow("SELECT COUNT(DISTINCT o.id) as count FROM orders o JOIN order_items oi ON o.id = oi.order_id JOIN menu m ON oi.menu_id = m.id WHERE m.warung_id = ? AND o.status = 'dibayar' AND o.is_confirmed = 0", [$warung['id']])['count'] ?? 0,
        'diproses' => getRow("SELECT COUNT(DISTINCT o.id) as count FROM orders o JOIN order_items oi ON o.id = oi.order_id JOIN menu m ON oi.menu_id = m.id WHERE m.warung_id = ? AND o.status = 'diproses' AND o.is_confirmed = 0", [$warung['id']])['count'] ?? 0,
        'siap' => getRow("SELECT COUNT(DISTINCT o.id) as count FROM orders o JOIN order_items oi ON o.id = oi.order_id JOIN menu m ON oi.menu_id = m.id WHERE m.warung_id = ? AND o.status = 'siap' AND o.is_confirmed = 0", [$warung['id']])['count'] ?? 0,
    ];

    // 2. Get Orders
    $pesanan = getWarungOrders($warung['id'], null, 0);
    $pesanan = array_filter($pesanan, function($p) {
        return $p['status'] !== 'batal';
    });

    // 3. Generate HTML
    ob_start();
    foreach ($pesanan as $p) {
        $pembeli = getRow("SELECT nama FROM users WHERE id = ?", [$p['pembeli_id']]);
        $pembeli_nama = $pembeli ? esc($pembeli['nama']) : 'Unknown';
        $items = getRows("SELECT m.nama_menu, oi.qty FROM order_items oi JOIN menu m ON oi.menu_id = m.id WHERE oi.order_id = ?", [$p['id']]);
        
        echo '<tr>';
        echo '<td><strong>#' . $p['id'] . '</strong></td>';
        echo '<td>' . $pembeli_nama . '</td>';
        echo '<td>';
        foreach ($items as $item) {
            echo '<small>' . esc($item['nama_menu']) . ' (' . $item['qty'] . ')</small><br>';
        }
        echo '</td>';
        echo '<td><strong>' . formatCurrency($p['total_harga']) . '</strong></td>';
        echo '<td><span class="status status-' . $p['status'] . '">' . ucfirst(str_replace('_', ' ', $p['status'])) . '</span></td>';
        echo '<td>' . formatDateTime($p['waktu_pesan']) . '</td>';
        echo '<td style="text-align: center;">
                <form method="POST" style="display: flex; gap: 0.5rem; align-items: center; justify-content: center; flex-wrap: wrap;">
                    <input type="hidden" name="csrf_token" value="' . generate_csrf_token() . '">
                    <input type="hidden" name="order_id" value="' . $p['id'] . '">
                    <select name="status" style="padding: 0.5rem 0.75rem; border: 1px solid #ddd; border-radius: 5px; font-size: 0.85rem; background: white;">
                        <option value="dibayar" ' . ($p['status'] == 'dibayar' ? 'selected' : '') . '>Dibayar</option>
                        <option value="diproses" ' . ($p['status'] == 'diproses' ? 'selected' : '') . '>Diproses</option>
                        <option value="siap" ' . ($p['status'] == 'siap' ? 'selected' : '') . '>Siap</option>
                    </select>
                    <button type="submit" name="update_status" class="btn btn-success btn-sm" style="white-space: nowrap; padding: 0.5rem 1rem;">
                        ✓ Update
                    </button>
                </form>
            </td>';
        echo '</tr>';
    }
    $html = ob_get_clean();
    
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, no-store, must-revalidate'); // Prevent caching
    echo json_encode([
        'stats' => $stats,
        'html' => $html,
        'isEmpty' => empty($pesanan)
    ]);
    exit;
}

// Handle update status pesanan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Token keamanan tidak valid.';
    } else {
    $order_id = intval($_POST['order_id'] ?? 0);
    $status = trim($_POST['status'] ?? '');
    
    // Validate status
    if (!in_array($status, ['dibayar', 'diproses', 'siap'])) {
        $error = 'Status tidak valid!';
    } else {
        // Verify order belongs to this warung
        $order_check = getRow(
            "SELECT o.id FROM orders o
            JOIN order_items oi ON o.id = oi.order_id
            JOIN menu m ON oi.menu_id = m.id
            WHERE o.id = ? AND m.warung_id = ?",
            [$order_id, $warung['id']]
        );
        
        if (!$order_check) {
            $error = 'Pesanan tidak valid!';
        } else {
            // Get pembeli_id untuk notifikasi
            $order_info = getRow("SELECT pembeli_id FROM orders WHERE id = ?", [$order_id]);
            
            // Update order status dengan prepared statement
            $query = "UPDATE orders SET status = ? WHERE id = ?";
            if (execute($query, [$status, $order_id])) {
                // Kirim notifikasi ke pembeli
                if ($order_info) {
                    $msg = "Pesanan #$order_id statusnya berubah menjadi: " . ucfirst($status);
                    execute("INSERT INTO notifications (user_id, order_id, type, message, role, is_read) VALUES (?, ?, 'order_update', ?, 'pembeli', 0)", 
                        [$order_info['pembeli_id'], $order_id, $msg]);
                }
                $message = 'Status pesanan berhasil diperbarui!';
            } else {
                $error = 'Gagal memperbarui status!';
            }
        }
    }
}
}

// Get pesanan untuk warung ini dengan ORDER BY untuk prioritas
// Hanya tampilkan pesanan yang BELUM dikonfirmasi pembeli (is_confirmed = 0)
// Parameter ke-3 (0) menandakan is_confirmed = 0
$pesanan = getWarungOrders($warung['id'], null, 0);
$pesanan = array_filter($pesanan, function($p) {
    return $p['status'] !== 'batal';
});
?>
<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/sidebar.php'; ?>

<div class="page-header">
    <h1 class="page-title">📦 Pesanan Masuk</h1>
    <p class="page-subtitle">Kelola pesanan yang belum dikonfirmasi pembeli</p>
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

<!-- Status Stats -->
<?php
$stats = [
    'dibayar' => getRow(
        "SELECT COUNT(DISTINCT o.id) as count FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN menu m ON oi.menu_id = m.id
        WHERE m.warung_id = ? AND o.status = ? AND o.is_confirmed = 0",
        [$warung['id'], 'dibayar']
    )['count'] ?? 0,
    'diproses' => getRow(
        "SELECT COUNT(DISTINCT o.id) as count FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN menu m ON oi.menu_id = m.id
        WHERE m.warung_id = ? AND o.status = ? AND o.is_confirmed = 0",
        [$warung['id'], 'diproses']
    )['count'] ?? 0,
    'siap' => getRow(
        "SELECT COUNT(DISTINCT o.id) as count FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN menu m ON oi.menu_id = m.id
        WHERE m.warung_id = ? AND o.status = ? AND o.is_confirmed = 0",
        [$warung['id'], 'siap']
    )['count'] ?? 0,
];
?>
<div class="grid grid-3" style="margin-bottom: 2rem;">
    <div class="card">
        <div style="text-align: center; padding: 1rem;">
            <div id="stat-dibayar" style="font-size: 2rem; font-weight: 700; color: #f39c12;">
                <?php echo $stats['dibayar']; ?>
            </div>
            <div style="color: #666; margin-top: 0.5rem;">Pesanan Baru</div>
        </div>
    </div>
    
    <div class="card">
        <div style="text-align: center; padding: 1rem;">
            <div id="stat-diproses" style="font-size: 2rem; font-weight: 700; color: #3498db;">
                <?php echo $stats['diproses']; ?>
            </div>
            <div style="color: #666; margin-top: 0.5rem;">Sedang Diproses</div>
        </div>
    </div>
    
    <div class="card">
        <div style="text-align: center; padding: 1rem;">
            <div id="stat-siap" style="font-size: 2rem; font-weight: 700; color: #27ae60;">
                <?php echo $stats['siap']; ?>
            </div>
            <div style="color: #666; margin-top: 0.5rem;">Siap Diambil</div>
        </div>
    </div>
</div>

<!-- Pesanan List -->
<div class="card">
    <div class="card-header">
        <h3>Daftar Pesanan</h3>
    </div>
    
    <!-- Empty State Container -->
    <div id="orders-empty-state" class="card-body" style="display: <?php echo empty($pesanan) ? 'block' : 'none'; ?>">
            <div class="empty-state" style="padding: 2rem;">
                <div class="empty-state-icon">📦</div>
                <div class="empty-state-text">Tidak ada pesanan masuk</div>
                <div class="empty-state-subtext" style="font-size: 0.9rem; color: #999; margin-bottom: 1rem;">
                    Semua pesanan pembeli yang sudah dikonfirmasi dapat dilihat di halaman <strong>Pesanan Selesai</strong>
                </div>
                <div class="empty-state-action">
                    <a href="pesanan_selesai.php" class="btn btn-primary">
                        ✓ Lihat Pesanan Selesai
                    </a>
                </div>
            </div>
    </div>

    <!-- Table Container -->
    <div id="orders-table-container" class="card-body" style="overflow-x: auto; display: <?php echo empty($pesanan) ? 'none' : 'block'; ?>">
            <table class="table">
                <thead>
                    <tr>
                        <th>Pesanan</th>
                        <th>Pembeli</th>
                        <th>Item</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Waktu</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="orders-table-body">
                    <?php foreach ($pesanan as $p): ?>
                        <?php
                        $pembeli = getRow("SELECT nama FROM users WHERE id = ?", [$p['pembeli_id']]);
                        $pembeli_nama = $pembeli ? esc($pembeli['nama']) : 'Unknown';
                        $items = getRows("SELECT m.nama_menu, oi.qty FROM order_items oi JOIN menu m ON oi.menu_id = m.id WHERE oi.order_id = ?", [$p['id']]);
                        ?>
                        <tr>
                            <td><strong>#<?php echo $p['id']; ?></strong></td>
                            <td><?php echo $pembeli_nama; ?></td>
                            <td>
                                <?php foreach ($items as $item): ?>
                                    <small><?php echo esc($item['nama_menu']); ?> (<?php echo $item['qty']; ?>)</small><br>
                                <?php endforeach; ?>
                            </td>
                            <td><strong><?php echo formatCurrency($p['total_harga']); ?></strong></td>
                            <td>
                                <span class="status status-<?php echo $p['status']; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $p['status'])); ?>
                                </span>
                            </td>
                            <td><?php echo formatDateTime($p['waktu_pesan']); ?></td>
                            <td style="text-align: center;">
                                <form method="POST" style="display: flex; gap: 0.5rem; align-items: center; justify-content: center; flex-wrap: wrap;">
                                    <?php csrf_field(); ?>
                                    <input type="hidden" name="order_id" value="<?php echo $p['id']; ?>">
                                    <select name="status" style="padding: 0.5rem 0.75rem; border: 1px solid #ddd; border-radius: 5px; font-size: 0.85rem; background: white;">
                                        <option value="dibayar" <?php echo $p['status'] == 'dibayar' ? 'selected' : ''; ?>>Dibayar</option>
                                        <option value="diproses" <?php echo $p['status'] == 'diproses' ? 'selected' : ''; ?>>Diproses</option>
                                        <option value="siap" <?php echo $p['status'] == 'siap' ? 'selected' : ''; ?>>Siap</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn btn-success btn-sm" style="white-space: nowrap; padding: 0.5rem 1rem;">
                                        ✓ Update
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
    </div>
</div>

<script src="/D-WarungS/assets/js/realtime-notifications.js?v=<?php echo time(); ?>"></script>
<script>
    // Inisialisasi realtime notifications untuk penjual
    document.addEventListener('DOMContentLoaded', function() {
        // Request browser notification permission
        RealtimeNotifications.requestNotificationPermission();
        
        // Buat instance realtime
        const realtime = new RealtimeNotifications({
            endpoint: '/D-WarungS/api/realtime-notifications.php',
            reconnectInterval: 3000,
            onNotification: function(notif) {
                console.log('📩 Pesanan Baru:', notif);
                
                // Tampilkan notifikasi visual
                if (notif.type === 'order' || notif.pesanan_id) {
                    // Tampilkan notifikasi pop-up
                    showOrderNotification(notif);
                    
                    // Update data tanpa reload halaman
                    fetch('pesanan.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'mark_read=1&notification_id=' + notif.id
                    }).then(() => {
                        // Fetch updated data (HTML & Stats)
                        // Tambahkan timestamp untuk mencegah caching browser
                        return fetch('pesanan.php?get_updates=1&t=' + new Date().getTime());
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        // Update Statistics
                        document.getElementById('stat-dibayar').innerText = data.stats.dibayar;
                        document.getElementById('stat-diproses').innerText = data.stats.diproses;
                        document.getElementById('stat-siap').innerText = data.stats.siap;
                        
                        // Update Table Content & Visibility
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
                    }).catch(error => {
                        console.error('Error updating orders:', error);
                        // Sebagai fallback jika AJAX gagal, reload halaman
                        window.location.reload(true);
                    });
                }
            }
        });
        
        realtime.connect();
        
        window.addEventListener('beforeunload', function() {
            realtime.disconnect();
        });
        
        // Fungsi untuk menampilkan notifikasi pesanan
        function showOrderNotification(notif) {
            const notification = document.createElement('div');
            
            let title = '🎉 Pesanan Baru!';
            let bgColor = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'; // Default purple
            
            if (notif.type === 'order_update') {
                title = '🔔 Update Pesanan';
                bgColor = 'linear-gradient(135deg, #3498db 0%, #2980b9 100%)'; // Blue for updates
            } else if (notif.type === 'order_cancel') {
                title = '❌ Pesanan Dibatalkan';
                bgColor = 'linear-gradient(135deg, #ff6b6b 0%, #ee5253 100%)'; // Red for cancel
            }
            
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${bgColor};
                color: white;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 8px 32px rgba(0,0,0,0.2);
                z-index: 9999;
                max-width: 400px;
                animation: slideIn 0.3s ease-out;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            `;
            
            notification.innerHTML = `
                <div style="font-weight: 600; font-size: 16px; margin-bottom: 8px;">${title}</div>
                <div style="font-size: 14px; opacity: 0.9;">${notif.message || 'Pesanan #' + notif.pesanan_id}</div>
                <div style="font-size: 13px; opacity: 0.85; margin-top: 4px;">Total: Rp ${(notif.total_harga || 0).toLocaleString('id-ID')}</div>
                <div style="font-size: 12px; opacity: 0.8; margin-top: 8px; cursor: pointer; text-decoration: underline;">Data diperbarui otomatis</div>
            `;
            
            document.body.appendChild(notification);
            
            // Auto remove setelah 5 detik
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => notification.remove(), 300);
            }, 5000);
        }
    });
</script>

<style>
    @keyframes slideIn {
        from {
            transform: translateX(400px);
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
            transform: translateX(400px);
            opacity: 0;
        }
    }
</style>

</main>
<?php require_once '../includes/footer.php'; ?>
