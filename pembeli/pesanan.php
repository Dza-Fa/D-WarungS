<?php
/**
 * Halaman Pesanan Pembeli
 */

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pembeli') {
    header('Location: /D-WarungS/auth/login.php');
    exit();
}

require_once '../config/db.php';

$page_title = 'Pesanan Saya';

// Get pesanan detail jika ada pesanan_id
$pesanan_detail = null;
if (isset($_GET['pesanan_id'])) {
    $pesanan_id = intval($_GET['pesanan_id']);
    $pesanan_detail = getRow("SELECT * FROM orders WHERE id = ? AND pembeli_id = ?", [$pesanan_id, $_SESSION['user_id']]);
}

// Get semua pesanan pembeli
$query = "SELECT * FROM orders WHERE pembeli_id = ? ORDER BY waktu_pesan DESC";
$pesanan = getRows($query, [$_SESSION['user_id']]);

// Get status stats
$status_stats = [];
$status_list = ['menunggu', 'dibayar', 'diproses', 'siap', 'selesai', 'batal'];
foreach ($status_list as $status) {
    $count = getRow("SELECT COUNT(*) as count FROM orders WHERE pembeli_id = ? AND status = ?", [$_SESSION['user_id'], $status])['count'];
    if ($count > 0) {
        $status_stats[$status] = $count;
    }
}
?>
<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/sidebar.php'; ?>

<div class="page-header">
    <h1 class="page-title">📦 Pesanan Saya</h1>
    <p class="page-subtitle">Kelola semua pesanan Anda</p>
</div>

<?php if ($pesanan_detail && isset($_GET['pesanan_id'])): ?>
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-header">
            <h3>Detail Pesanan #<?php echo $pesanan_detail['id']; ?></h3>
        </div>
        
        <div class="card-body">
            <div class="form-row">
                <div>
                    <strong style="color: #666;">Nomor Pesanan:</strong><br>
                    #<?php echo $pesanan_detail['id']; ?>
                </div>
                <div>
                    <strong style="color: #666;">Status:</strong><br>
                    <span class="status status-<?php echo $pesanan_detail['status']; ?>">
                        <?php echo ucfirst($pesanan_detail['status']); ?>
                    </span>
                </div>
                <div>
                    <strong style="color: #666;">Waktu Pesan:</strong><br>
                    <?php echo formatDateTime($pesanan_detail['waktu_pesan']); ?>
                </div>
                <div>
                    <strong style="color: #666;">Total Harga:</strong><br>
                    <span style="font-size: 1.2rem; color: #667eea; font-weight: 600;">
                        <?php echo formatCurrency($pesanan_detail['total_harga']); ?>
                    </span>
                </div>
            </div>
            
            <div style="margin-top: 2rem;">
                <h4 style="margin-bottom: 1rem;">Item Pesanan:</h4>
                <?php
                $items = getRows("SELECT oi.*, m.nama_menu FROM order_items oi JOIN menu m ON oi.menu_id = m.id WHERE oi.order_id = ?", [$pesanan_detail['id']]);
                ?>
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Menu</th>
                            <th style="text-align: right;">Harga</th>
                            <th style="text-align: center;">Qty</th>
                            <th style="text-align: right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?php echo esc($item['nama_menu']); ?></td>
                                <td style="text-align: right;"><?php echo formatCurrency($item['harga_satuan']); ?></td>
                                <td style="text-align: center;"><?php echo $item['qty']; ?></td>
                                <td style="text-align: right;"><?php echo formatCurrency($item['subtotal']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card-footer">
            <a href="pesanan.php" class="btn btn-secondary">← Kembali</a>
        </div>
    </div>
<?php endif; ?>

<!-- Status Stats -->
<?php if (!empty($status_stats)): ?>
    <div class="grid grid-4" style="margin-bottom: 2rem;">
        <?php foreach ($status_stats as $status => $count): ?>
            <div class="card">
                <div style="text-align: center; padding: 1rem;">
                    <div style="font-size: 2rem; font-weight: 700; color: #667eea;">
                        <?php echo $count; ?>
                    </div>
                    <div style="color: #666; margin-top: 0.5rem; text-transform: capitalize;">
                        <?php echo $status; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Pesanan List -->
<div class="card">
    <div class="card-header">
        <h3>Daftar Pesanan</h3>
    </div>
    
    <?php if (empty($pesanan)): ?>
        <div class="card-body">
            <div class="empty-state" style="padding: 2rem;">
                <div class="empty-state-icon">📦</div>
                <div class="empty-state-text">Anda belum memiliki pesanan</div>
                <div class="empty-state-action">
                    <a href="dashboard.php" class="btn btn-primary">
                        🛒 Mulai Pesan
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
                        <th>Status</th>
                        <th style="text-align: right;">Total</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pesanan as $p): ?>
                        <tr>
                            <td><strong>#<?php echo $p['id']; ?></strong></td>
                            <td><?php echo formatDateTime($p['waktu_pesan']); ?></td>
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
                                    👁️ Lihat
                                </a>
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
