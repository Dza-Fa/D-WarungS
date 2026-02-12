<?php
/**
 * Halaman Penjualan Kasir
 */

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'kasir') {
    header('Location: /D-WarungS/auth/login.php');
    exit();
}

require_once '../config/db.php';

$page_title = 'Laporan Penjualan';

// Get filter dari URL
$start_date = trim($_GET['start_date'] ?? '');
$end_date = trim($_GET['end_date'] ?? '');

// Default: bulan ini
if (!$start_date) {
    $start_date = date('Y-m-01');
}
if (!$end_date) {
    $end_date = date('Y-m-t');
}

// Get penjualan berdasarkan periode
$base_query = "WHERE o.status IN ('dibayar', 'diproses', 'siap', 'selesai') AND DATE(o.waktu_pesan) BETWEEN ? AND ?";

$total_penjualan = getRow(
    "SELECT SUM(o.total_harga) as total FROM orders o $base_query",
    [$start_date, $end_date]
)['total'] ?? 0;

$total_transaksi = getRow(
    "SELECT COUNT(*) as count FROM orders o $base_query",
    [$start_date, $end_date]
)['count'];

$rata_transaksi = $total_transaksi > 0 ? $total_penjualan / $total_transaksi : 0;

// Get penjualan per warung
$warung_sales = getRows(
    "SELECT w.id, w.nama_warung, SUM(o.total_harga) as total, COUNT(DISTINCT o.id) as count
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN menu m ON oi.menu_id = m.id
    JOIN warung w ON m.warung_id = w.id
    WHERE o.status IN ('dibayar', 'diproses', 'siap', 'selesai') AND DATE(o.waktu_pesan) BETWEEN ? AND ?
    GROUP BY w.id, w.nama_warung
    ORDER BY total DESC",
    [$start_date, $end_date]
);

// Get top menu
$top_menu = getRows(
    "SELECT m.nama_menu, w.nama_warung, SUM(oi.qty) as total_qty, SUM(oi.subtotal) as total_penjualan
    FROM order_items oi
    JOIN menu m ON oi.menu_id = m.id
    JOIN warung w ON m.warung_id = w.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.status IN ('dibayar', 'diproses', 'siap', 'selesai') AND DATE(o.waktu_pesan) BETWEEN ? AND ?
    GROUP BY m.id
    ORDER BY total_qty DESC
    LIMIT 10",
    [$start_date, $end_date]
);

// Get transaksi detail
$transaksi = getRows(
    "SELECT o.id, o.total_harga, o.waktu_pesan, o.status, u.nama, 
    (SELECT GROUP_CONCAT(m.nama_menu) FROM order_items oi JOIN menu m ON oi.menu_id = m.id WHERE oi.order_id = o.id) as items
    FROM orders o
    JOIN users u ON o.pembeli_id = u.id
    WHERE o.status IN ('dibayar', 'diproses', 'siap', 'selesai') AND DATE(o.waktu_pesan) BETWEEN ? AND ?
    ORDER BY o.waktu_pesan DESC",
    [$start_date, $end_date]
);
?>
<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/sidebar.php'; ?>

<div class="page-header">
    <h1 class="page-title">📊 Laporan Penjualan</h1>
    <p class="page-subtitle">Analisis penjualan dan transaksi</p>
</div>

<!-- Filter Period -->
<div class="card" style="margin-bottom: 2rem;">
    <form method="GET" class="card-body" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
        <div class="form-group" style="margin: 0;">
            <label for="start_date">Tanggal Awal</label>
            <input type="date" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
        </div>
        <div class="form-group" style="margin: 0;">
            <label for="end_date">Tanggal Akhir</label>
            <input type="date" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
        </div>
        <button type="submit" class="btn btn-primary">
            🔍 Filter
        </button>
    </form>
</div>

<!-- Summary Statistics -->
<div class="grid grid-4" style="margin-bottom: 2rem;">
    <div class="card">
        <div style="text-align: center; padding: 1rem;">
            <div style="font-size: 2rem; font-weight: 700; color: #27ae60;">
                <?php echo formatCurrency($total_penjualan); ?>
            </div>
            <div style="color: #666; margin-top: 0.5rem;">Total Penjualan</div>
        </div>
    </div>
    
    <div class="card">
        <div style="text-align: center; padding: 1rem;">
            <div style="font-size: 2rem; font-weight: 700; color: #3498db;">
                <?php echo number_format($total_transaksi, 0); ?>
            </div>
            <div style="color: #666; margin-top: 0.5rem;">Total Transaksi</div>
        </div>
    </div>
    
    <div class="card">
        <div style="text-align: center; padding: 1rem;">
            <div style="font-size: 2rem; font-weight: 700; color: #667eea;">
                <?php echo formatCurrency(round($rata_transaksi, 0)); ?>
            </div>
            <div style="color: #666; margin-top: 0.5rem;">Rata-rata Transaksi</div>
        </div>
    </div>
    
    <div class="card">
        <div style="text-align: center; padding: 1rem;">
            <div style="font-size: 1.5rem; color: #666;">
                <?php echo date('d M Y', strtotime($start_date)); ?><br>
                s/d<br>
                <?php echo date('d M Y', strtotime($end_date)); ?>
            </div>
        </div>
    </div>
</div>

<!-- Top Menu -->
<?php if (!empty($top_menu)): ?>
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header">
        <h3>Menu Terlaris</h3>
    </div>
    
    <div class="card-body" style="overflow-x: auto;">
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Menu</th>
                    <th>Warung</th>
                    <th style="text-align: center;">Terjual</th>
                    <th style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($top_menu as $menu): ?>
                    <tr>
                        <td><?php echo esc($menu['nama_menu']); ?></td>
                        <td><?php echo esc($menu['nama_warung']); ?></td>
                        <td style="text-align: center;">
                            <strong><?php echo $menu['total_qty']; ?></strong>
                        </td>
                        <td style="text-align: right;">
                            <?php echo formatCurrency($menu['total_penjualan']); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Penjualan Per Warung -->
<?php if (!empty($warung_sales)): ?>
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header">
        <h3>Penjualan Per Warung</h3>
    </div>
    
    <div class="card-body" style="overflow-x: auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>Warung</th>
                    <th style="text-align: center;">Transaksi</th>
                    <th style="text-align: right;">Total Penjualan</th>
                    <th style="text-align: right;">Persentase</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($warung_sales as $w): ?>
                    <tr>
                        <td><?php echo esc($w['nama_warung']); ?></td>
                        <td style="text-align: center;"><?php echo $w['count']; ?></td>
                        <td style="text-align: right; font-weight: 600;">
                            <?php echo formatCurrency($w['total']); ?>
                        </td>
                        <td style="text-align: right;">
                            <?php echo number_format(($w['total'] / $total_penjualan) * 100, 1); ?>%
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Detail Transaksi -->
<div class="card">
    <div class="card-header">
        <h3>Riwayat Transaksi</h3>
    </div>
    
    <?php if (empty($transaksi)): ?>
        <div class="card-body">
            <div class="empty-state" style="padding: 2rem;">
                <div class="empty-state-icon">📊</div>
                <div class="empty-state-text">Tidak ada transaksi untuk periode ini</div>
            </div>
        </div>
    <?php else: ?>
        <div class="card-body" style="overflow-x: auto;">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Pesanan</th>
                        <th>Pembeli</th>
                        <th>Waktu</th>
                        <th>Item</th>
                        <th style="text-align: right;">Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transaksi as $t): ?>
                        <tr>
                            <td><strong>#<?php echo $t['id']; ?></strong></td>
                            <td><?php echo esc($t['nama']); ?></td>
                            <td><?php echo formatDateTime($t['waktu_pesan']); ?></td>
                            <td>
                                <small style="color: #999;">
                                    <?php echo esc(substr($t['items'], 0, 30)); ?>...
                                </small>
                            </td>
                            <td style="text-align: right; font-weight: 600;">
                                <?php echo formatCurrency($t['total_harga']); ?>
                            </td>
                            <td>
                                <span class="status status-<?php echo $t['status']; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $t['status'])); ?>
                                </span>
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
