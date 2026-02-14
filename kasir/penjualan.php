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
require_once '../config/queries.php';

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
$stats = getSalesReportStats($start_date, $end_date);
$total_penjualan = $stats['total_penjualan'];
$total_transaksi = $stats['total_transaksi'];
$rata_transaksi = $stats['rata_transaksi'];

// Get penjualan per warung
$warung_sales = getSalesByWarung($start_date, $end_date);

// Get top menu
$top_menu = getTopSellingMenus($start_date, $end_date, 10);

// Get transaksi detail
// Pindahkan query ke file `queries.php` agar terpusat dan rapi
$transaksi = getTransactionsForReport($start_date, $end_date);
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

<!-- Pindahkan CSS dan JS ke file terpisah untuk kebersihan kode -->
<link rel="stylesheet" href="/D-WarungS/assets/css/kasir-notifications.css">
<script src="/D-WarungS/assets/js/realtime-notifications.js?v=<?php echo time(); ?>"></script>
<script src="/D-WarungS/assets/js/kasir-penjualan.js"></script>

</main>
<?php require_once '../includes/footer.php'; ?>
