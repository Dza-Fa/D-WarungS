<?php
/**
 * Halaman Pesanan Selesai (Riwayat)
 */

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pembeli') {
    header('Location: /D-WarungS/auth/login.php');
    exit();
}

require_once '../config/db.php';

$page_title = 'Pesanan Selesai';

// Get filter parameter
$filter = isset($_GET['filter']) ? trim($_GET['filter']) : 'all';

// Base query untuk pesanan yang sudah dikonfirmasi ATAU dibatalkan
$where = "pembeli_id = ? AND (is_confirmed = 1 OR status = 'batal')";
$params = [$_SESSION['user_id']];

// Apply filter jika ada
if ($filter === 'dibayar') {
    $where .= " AND status = ?";
    $params[] = 'dibayar';
} elseif ($filter === 'diproses') {
    $where .= " AND status = ?";
    $params[] = 'diproses';
} elseif ($filter === 'siap') {
    $where .= " AND status = ?";
    $params[] = 'siap';
} elseif ($filter === 'batal') {
    $where .= " AND status = ?";
    $params[] = 'batal';
}

// Get pesanan selesai dengan order items count
$query = "
    SELECT o.*, COUNT(oi.id) as item_count
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE $where
    GROUP BY o.id
    ORDER BY o.waktu_pesan DESC
";
$pesanan = getRows($query, $params);

// Get statistics (pesanan selesai + dibatalkan)
$stats = [];
$stats['total_pesanan'] = getRow("SELECT COUNT(*) as count FROM orders WHERE pembeli_id = ? AND (is_confirmed = 1 OR status = 'batal')", [$_SESSION['user_id']])['count'] ?? 0;
$stats['total_pengeluaran'] = getRow("SELECT SUM(total_harga) as total FROM orders WHERE pembeli_id = ? AND (is_confirmed = 1 OR status = 'batal')", [$_SESSION['user_id']])['total'] ?? 0;
$stats['dibayar'] = getRow("SELECT COUNT(*) as count FROM orders WHERE pembeli_id = ? AND (is_confirmed = 1 OR status = 'batal') AND status = ?", [$_SESSION['user_id'], 'dibayar'])['count'] ?? 0;
$stats['diproses'] = getRow("SELECT COUNT(*) as count FROM orders WHERE pembeli_id = ? AND (is_confirmed = 1 OR status = 'batal') AND status = ?", [$_SESSION['user_id'], 'diproses'])['count'] ?? 0;
$stats['siap'] = getRow("SELECT COUNT(*) as count FROM orders WHERE pembeli_id = ? AND (is_confirmed = 1 OR status = 'batal') AND status = ?", [$_SESSION['user_id'], 'siap'])['count'] ?? 0;
$stats['batal'] = getRow("SELECT COUNT(*) as count FROM orders WHERE pembeli_id = ? AND status = ?", [$_SESSION['user_id'], 'batal'])['count'] ?? 0;
?>
<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/sidebar.php'; ?>

<div class="page-header">
    <a href="pesanan.php" style="color: #667eea; text-decoration: none; margin-bottom: 1rem; display: inline-block;">
        ← Kembali ke Pesanan Aktif
    </a>
    <h1 class="page-title">✓ Riwayat Pesanan</h1>
    <p class="page-subtitle">Pesanan yang sudah dikonfirmasi diterima dan selesai</p>
</div>

<!-- Statistics Section -->
<div class="grid grid-4" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;">
        <div style="text-align: center; padding: 1.5rem;">
            <div style="font-size: 2.5rem; font-weight: 700; margin-bottom: 0.5rem;">
                <?php echo $stats['total_pesanan']; ?>
            </div>
            <div style="font-size: 0.9rem; opacity: 0.9;">Total Pesanan Selesai</div>
        </div>
    </div>
    
    <div class="card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; border: none;">
        <div style="text-align: center; padding: 1.5rem;">
            <div style="font-size: 1.8rem; font-weight: 700; margin-bottom: 0.5rem; white-space: nowrap;">
                <?php echo formatCurrency($stats['total_pengeluaran']); ?>
            </div>
            <div style="font-size: 0.9rem; opacity: 0.9;">Total Pengeluaran</div>
        </div>
    </div>
    
    <div class="card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; border: none;">
        <div style="text-align: center; padding: 1.5rem;">
            <div style="font-size: 2.5rem; font-weight: 700; margin-bottom: 0.5rem;">
                <?php echo $stats['dibayar'] + $stats['diproses'] + $stats['siap']; ?>
            </div>
            <div style="font-size: 0.9rem; opacity: 0.9;">Pesanan Selesai</div>
        </div>
    </div>
    
    <div class="card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; border: none;">
        <div style="text-align: center; padding: 1.5rem;">
            <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;">
                <?php echo $stats['batal']; ?>
            </div>
            <div style="font-size: 0.9rem; opacity: 0.9;">Pesanan Dibatalkan</div>
        </div>
    </div>
</div>

<!-- Filter Buttons -->
<div style="margin-bottom: 1.5rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
    <a href="pesanan_selesai.php" class="btn <?php echo $filter === 'all' ? 'btn-primary' : 'btn-outline-secondary'; ?> btn-sm">
        📊 Semua (<?php echo $stats['total_pesanan']; ?>)
    </a>
    <a href="pesanan_selesai.php?filter=dibayar" class="btn <?php echo $filter === 'dibayar' ? 'btn-primary' : 'btn-outline-secondary'; ?> btn-sm">
        💳 Dibayar (<?php echo $stats['dibayar']; ?>)
    </a>
    <a href="pesanan_selesai.php?filter=diproses" class="btn <?php echo $filter === 'diproses' ? 'btn-primary' : 'btn-outline-secondary'; ?> btn-sm">
        🔄 Diproses (<?php echo $stats['diproses']; ?>)
    </a>
    <a href="pesanan_selesai.php?filter=siap" class="btn <?php echo $filter === 'siap' ? 'btn-primary' : 'btn-outline-secondary'; ?> btn-sm">
        ✓ Siap (<?php echo $stats['siap']; ?>)
    </a>
    <a href="pesanan_selesai.php?filter=batal" class="btn <?php echo $filter === 'batal' ? 'btn-primary' : 'btn-outline-secondary'; ?> btn-sm">
        ✗ Dibatalkan (<?php echo $stats['batal']; ?>)
    </a>
</div>

<!-- Pesanan List -->
<div class="card">
    <div class="card-header">
        <h3>Daftar Pesanan Selesai & Dibatalkan</h3>
    </div>
    
    <?php if (empty($pesanan)): ?>
        <div class="card-body">
            <div class="empty-state" style="padding: 2rem;">
                <div class="empty-state-icon">✓</div>
                <div class="empty-state-text">Tidak ada pesanan selesai atau dibatalkan</div>
                <div class="empty-state-subtext" style="font-size: 0.9rem; color: #999;">
                    Pesanan yang Anda konfirmasi diterima atau dibatalkan akan muncul di sini
                </div>
                <div class="empty-state-action">
                    <a href="pesanan.php" class="btn btn-primary">
                        📦 Lihat Pesanan
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="card-body" style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>No. Pesanan</th>
                        <th>Waktu</th>
                        <th style="text-align: center;">Items</th>
                        <th>Status</th>
                        <th style="text-align: right;">Total</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pesanan as $p): ?>
                        <tr>
                            <td><strong>#<?php echo $p['id']; ?></strong></td>
                            <td><small><?php echo formatDateTime($p['waktu_pesan']); ?></small></td>
                            <td style="text-align: center;">
                                <span style="background: #f0f0f0; padding: 0.3rem 0.6rem; border-radius: 20px; font-size: 0.85rem;">
                                    <?php echo $p['item_count']; ?> item
                                </span>
                            </td>
                            <td>
                                <span class="status status-<?php echo $p['status']; ?>">
                                    <?php echo ucfirst($p['status']); ?>
                                </span>
                            </td>
                            <td style="text-align: right; font-weight: 600;">
                                <?php echo formatCurrency($p['total_harga']); ?>
                            </td>
                            <td style="text-align: center;">
                                <a href="pesanan.php?pesanan_id=<?php echo $p['id']; ?>" class="btn btn-info btn-sm">
                                    👁️ Detail
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="card-footer" style="text-align: right;">
            <strong>Total Pesanan:</strong> <?php echo count($pesanan); ?> | 
            <strong>Total Harga:</strong> 
            <span style="color: #667eea; font-weight: 600;">
                <?php echo formatCurrency(array_sum(array_column($pesanan, 'total_harga'))); ?>
            </span>
        </div>
    <?php endif; ?>
</div>

<script src="/D-WarungS/assets/js/realtime-notifications.js?v=<?php echo time(); ?>"></script>
<script>
    // Inisialisasi realtime notifications untuk riwayat pesanan
    document.addEventListener('DOMContentLoaded', function() {
        const realtime = new RealtimeNotifications({
            endpoint: '/D-WarungS/api/realtime-notifications.php',
            reconnectInterval: 3000,
            onNotification: function(notif) {
                console.log('📩 Notifikasi realtime:', notif);
                
                // Refresh halaman untuk update terbaru
                if (notif.pesanan_id) {
                    console.log('Update terbaru untuk pesanan #' + notif.pesanan_id);
                    location.reload();
                }
            }
        });
        
        realtime.connect();
        
        window.addEventListener('beforeunload', function() {
            realtime.disconnect();
        });
    });
</script>

</main>
<?php require_once '../includes/footer.php'; ?>
