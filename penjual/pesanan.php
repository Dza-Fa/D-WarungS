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

$page_title = 'Pesanan Masuk';

// Get warung milik penjual
$warung = getRow("SELECT * FROM warung WHERE pemilik_id = ?", [$_SESSION['user_id']]);

if (!$warung) {
    header('Location: dashboard.php');
    exit();
}

$message = '';
$error = '';

// Handle update status pesanan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id'] ?? 0);
    $status = trim($_POST['status'] ?? '');
    
    // Validate status
    if (!in_array($status, ['dibayar', 'diproses', 'siap', 'selesai'])) {
        $error = 'Status tidak valid!';
    } else {
        // Update order status dengan prepared statement
        $query = "UPDATE orders SET status = ? WHERE id = ?";
        if (executeUpdate($query, [$status, $order_id])) {
            $message = 'Status pesanan berhasil diperbarui!';
        } else {
            $error = 'Gagal memperbarui status!';
        }
    }
}

// Get pesanan untuk warung ini
$query = "
    SELECT DISTINCT o.* FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN menu m ON oi.menu_id = m.id
    WHERE m.warung_id = ? AND o.status != 'menunggu' AND o.status != 'batal'
    ORDER BY o.waktu_pesan DESC
";
$pesanan = getRows($query, [$warung['id']]);

// Get pesanan baru (status dibayar)
$query_baru = "
    SELECT DISTINCT o.id, COUNT(DISTINCT oi.id) as item_count
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN menu m ON oi.menu_id = m.id
    WHERE m.warung_id = ? AND o.status = 'dibayar'
    GROUP BY o.id
";
$pesanan_baru = getRows($query_baru, [$warung['id']]);
?>
<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/sidebar.php'; ?>

<div class="page-header">
    <h1 class="page-title">📦 Pesanan Masuk</h1>
    <p class="page-subtitle">Kelola pesanan dari pembeli</p>
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
        WHERE m.warung_id = ? AND o.status = 'dibayar'",
        [$warung['id']]
    )['count'],
    'diproses' => getRow(
        "SELECT COUNT(DISTINCT o.id) as count FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN menu m ON oi.menu_id = m.id
        WHERE m.warung_id = ? AND o.status = 'diproses'",
        [$warung['id']]
    )['count'],
    'siap' => getRow(
        "SELECT COUNT(DISTINCT o.id) as count FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN menu m ON oi.menu_id = m.id
        WHERE m.warung_id = ? AND o.status = 'siap'",
        [$warung['id']]
    )['count'],
];
?>
<div class="grid grid-3" style="margin-bottom: 2rem;">
    <div class="card">
        <div style="text-align: center; padding: 1rem;">
            <div style="font-size: 2rem; font-weight: 700; color: #f39c12;">
                <?php echo $stats['dibayar']; ?>
            </div>
            <div style="color: #666; margin-top: 0.5rem;">Pesanan Baru</div>
        </div>
    </div>
    
    <div class="card">
        <div style="text-align: center; padding: 1rem;">
            <div style="font-size: 2rem; font-weight: 700; color: #3498db;">
                <?php echo $stats['diproses']; ?>
            </div>
            <div style="color: #666; margin-top: 0.5rem;">Sedang Diproses</div>
        </div>
    </div>
    
    <div class="card">
        <div style="text-align: center; padding: 1rem;">
            <div style="font-size: 2rem; font-weight: 700; color: #27ae60;">
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
    
    <?php if (empty($pesanan)): ?>
        <div class="card-body">
            <div class="empty-state" style="padding: 2rem;">
                <div class="empty-state-icon">📦</div>
                <div class="empty-state-text">Tidak ada pesanan</div>
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
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pesanan as $p): ?>
                        <?php
                        $pembeli = getRow("SELECT nama FROM users WHERE id = ?", [$p['pembeli_id']]);
                        $items = getRows("SELECT m.nama_menu, oi.qty FROM order_items oi JOIN menu m ON oi.menu_id = m.id WHERE oi.order_id = ?", [$p['id']]);
                        ?>
                        <tr>
                            <td><strong>#<?php echo $p['id']; ?></strong></td>
                            <td><?php echo esc($pembeli['nama']); ?></td>
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
                            <td><?php echo formatDateTime($p['waktu_pesan'], 'd M H:i'); ?></td>
                            <td style="text-align: center;">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $p['id']; ?>">
                                    <select name="status" style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 5px; font-size: 0.85rem;">
                                        <option value="dibayar" <?php echo $p['status'] == 'dibayar' ? 'selected' : ''; ?>>Dibayar</option>
                                        <option value="diproses" <?php echo $p['status'] == 'diproses' ? 'selected' : ''; ?>>Diproses</option>
                                        <option value="siap" <?php echo $p['status'] == 'siap' ? 'selected' : ''; ?>>Siap</option>
                                        <option value="selesai" <?php echo $p['status'] == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn btn-primary btn-sm">
                                        ✓ Update
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
