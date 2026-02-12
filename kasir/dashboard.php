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

$page_title = 'Dashboard Kasir';

$message = '';
$error = '';

// Handle validasi pembayaran
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['validasi'])) {
    $order_id = intval($_POST['order_id'] ?? 0);
    
    // Update order status ke 'dibayar'
    $query = "UPDATE orders SET status = 'dibayar' WHERE id = ? AND status = 'menunggu'";
    if (executeUpdate($query, [$order_id])) {
        $message = 'Pembayaran berhasil divalidasi!';
    } else {
        $error = 'Gagal memvalidasi pembayaran!';
    }
}

// Get pesanan yang menunggu pembayaran
$query = "SELECT * FROM orders WHERE status = 'menunggu' ORDER BY waktu_pesan ASC";
$pesanan = getRows($query);

// Get statistics
$menunggu_count = count($pesanan);
$dibayar_total = getRow("SELECT SUM(total_harga) as total FROM orders WHERE status = 'dibayar'")['total'] ?? 0;
$semua_total = getRow("SELECT SUM(total_harga) as total FROM orders WHERE status IN ('dibayar', 'diproses', 'siap', 'selesai')")['total'] ?? 0;
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
            <div style="font-size: 2rem; font-weight: 700; color: #f39c12;">
                <?php echo $menunggu_count; ?>
            </div>
            <div style="color: #666; margin-top: 0.5rem;">Pembayaran Menunggu</div>
        </div>
    </div>
    
    <div class="card">
        <div style="text-align: center; padding: 1rem;">
            <div style="font-size: 2rem; font-weight: 700; color: #27ae60;">
                <?php echo formatCurrency($dibayar_total); ?>
            </div>
            <div style="color: #666; margin-top: 0.5rem;">Sudah Dibayar</div>
        </div>
    </div>
    
    <div class="card">
        <div style="text-align: center; padding: 1rem;">
            <div style="font-size: 2rem; font-weight: 700; color: #667eea;">
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
    
    <?php if (empty($pesanan)): ?>
        <div class="card-body">
            <div class="empty-state" style="padding: 2rem;">
                <div class="empty-state-icon">✅</div>
                <div class="empty-state-text">Semua pembayaran sudah divalidasi</div>
            </div>
        </div>
    <?php else: ?>
        <div class="card-body" style="overflow-x: auto;">
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
                <tbody>
                    <?php foreach ($pesanan as $p): ?>
                        <?php
                        $pembeli = getRow("SELECT nama FROM users WHERE id = ?", [$p['pembeli_id']]);
                        $items_count = getRow("SELECT COUNT(*) as count FROM order_items WHERE order_id = ?", [$p['id']])['count'];
                        ?>
                        <tr>
                            <td>
                                <strong>#<?php echo $p['id']; ?></strong><br>
                                <small style="color: #999;"><?php echo $items_count; ?> item</small>
                            </td>
                            <td><?php echo esc($pembeli['nama']); ?></td>
                            <td><?php echo formatDateTime($p['waktu_pesan']); ?></td>
                            <td style="text-align: right; font-weight: 600; font-size: 1.1rem;">
                                <?php echo formatCurrency($p['total_harga']); ?>
                            </td>
                            <td style="text-align: center;">
                                <form method="POST" style="display: inline;">
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
    <?php endif; ?>
</div>

</main>
<?php require_once '../includes/footer.php'; ?>
