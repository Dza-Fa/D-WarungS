<?php
/**
 * Pesanan Selesai untuk Penjual - Pesanan yang sudah diterima pembeli
 */

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pedagang') {
    header('Location: /D-WarungS/auth/login.php');
    exit();
}

require_once '../config/db.php';

$page_title = 'Pesanan Selesai';

// Get warung milik penjual
$warung = getRow("SELECT * FROM warung WHERE pemilik_id = ?", [$_SESSION['user_id']]);

if (!$warung) {
    header('Location: dashboard.php');
    exit();
}

// Get pesanan yang sudah dikonfirmasi (diterima pembeli)
$query = "
    SELECT DISTINCT o.* FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN menu m ON oi.menu_id = m.id
    WHERE m.warung_id = ? AND o.is_confirmed = 1
    ORDER BY o.waktu_pesan DESC
";
$pesanan = getRows($query, [$warung['id']]);

// Get status stats untuk pesanan yang sudah selesai
$stats = [
    'total' => count($pesanan),
    'dengan_rating' => getRow(
        "SELECT COUNT(DISTINCT o.id) as count FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN menu m ON oi.menu_id = m.id
        JOIN ratings r ON r.order_id = o.id
        WHERE m.warung_id = ? AND o.is_confirmed = 1",
        [$warung['id']]
    )['count'] ?? 0,
];
?>
<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/sidebar.php'; ?>

<div class="page-header">
    <h1 class="page-title">✓ Pesanan Selesai</h1>
    <p class="page-subtitle">Pesanan yang sudah diterima dan dikonfirmasi pembeli</p>
</div>

<!-- Status Stats -->
<div class="grid grid-2" style="margin-bottom: 2rem;">
    <div class="card">
        <div style="text-align: center; padding: 1rem;">
            <div style="font-size: 2rem; font-weight: 700; color: #27ae60;">
                <?php echo $stats['total']; ?>
            </div>
            <div style="color: #666; margin-top: 0.5rem;">Total Selesai</div>
        </div>
    </div>
    
    <div class="card">
        <div style="text-align: center; padding: 1rem;">
            <div style="font-size: 2rem; font-weight: 700; color: #f39c12;">
                <?php echo $stats['dengan_rating']; ?>
            </div>
            <div style="color: #666; margin-top: 0.5rem;">Dengan Rating</div>
        </div>
    </div>
</div>

<!-- Pesanan List -->
<div class="card">
    <div class="card-header">
        <h3>Daftar Pesanan Selesai</h3>
    </div>
    
    <?php if (empty($pesanan)): ?>
        <div class="card-body">
            <div class="empty-state" style="padding: 2rem;">
                <div class="empty-state-icon">✓</div>
                <div class="empty-state-text">Belum ada pesanan yang selesai</div>
                <div class="empty-state-subtext" style="font-size: 0.9rem; color: #999; margin-bottom: 1rem;">
                    Pesanan akan ditampilkan di sini setelah pembeli mengkonfirmasi penerimaan
                </div>
                <div class="empty-state-action">
                    <a href="pesanan.php" class="btn btn-primary">
                        📦 Lihat Pesanan Aktif
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="card-body" style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Pesanan</th>
                        <th>Pembeli</th>
                        <th>Item</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Waktu</th>
                        <th style="text-align: center;">Rating</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pesanan as $p): ?>
                        <?php
                        $pembeli = getRow("SELECT nama FROM users WHERE id = ?", [$p['pembeli_id']]);
                        $pembeli_nama = $pembeli ? esc($pembeli['nama']) : 'Unknown';
                        $items = getRows("SELECT m.nama_menu, oi.qty FROM order_items oi JOIN menu m ON oi.menu_id = m.id WHERE oi.order_id = ?", [$p['id']]);
                        
                        // Get average rating for this order
                        $rating = getRow("SELECT AVG(rating) as avg_rating, COUNT(*) as count FROM ratings WHERE order_id = ?", [$p['id']]);
                        $avg_rating = $rating['avg_rating'] ? round($rating['avg_rating'], 1) : 0;
                        $rating_count = $rating['count'] ?? 0;
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
                                <span class="status status-selesai">
                                    ✓ Selesai
                                </span>
                            </td>
                            <td><?php echo formatDateTime($p['waktu_pesan']); ?></td>
                            <td style="text-align: center;">
                                <?php if ($rating_count > 0): ?>
                                    <span title="<?php echo $rating_count; ?> rating">
                                        ⭐ <?php echo $avg_rating; ?>/5
                                    </span>
                                <?php else: ?>
                                    <span style="color: #999;">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

</main>
<?php require_once '../includes/footer.php'; ?>
