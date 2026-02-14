<?php
/**
 * Halaman Keranjang Pembeli
 */

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pembeli') {
    header('Location: /D-WarungS/auth/login.php');
    exit();
}

require_once '../config/db.php';
require_once '../config/security.php';

$page_title = 'Keranjang';

// Handle remove from cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_item'])) {
    if (validate_csrf_token($_POST['csrf_token'] ?? '')) {
    $remove_id = intval($_POST['remove_id']);
    unset($_SESSION['cart'][$remove_id]);
    }
}

// Handle checkout
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['checkout'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Token keamanan tidak valid.';
    } else {
    if (empty($_SESSION['cart'])) {
        $error = 'Keranjang kosong!';
    } else {
        try {
            // Start Transaction
            $conn = getDBConnection();
            $conn->begin_transaction();

            // Calculate total
            $total = 0;
            foreach ($_SESSION['cart'] as $item) {
                $total += ($item['harga'] * $item['qty']);
            }
            
            // Insert order dengan prepared statement
            $query = "INSERT INTO orders (pembeli_id, status, total_harga, waktu_pesan) VALUES (?, 'menunggu', ?, NOW())";
            $stmt = executeQuery($query, [$_SESSION['user_id'], $total]);
            $order_id = getLastInsertId();
            
            if (!$order_id) {
                $error = 'Gagal membuat pesanan. Silakan coba lagi.';
            } else {
                // Insert order items
                foreach ($_SESSION['cart'] as $item) {
                    $subtotal = $item['harga'] * $item['qty'];
                    $query = "INSERT INTO order_items (order_id, menu_id, qty, harga_satuan, subtotal) VALUES (?, ?, ?, ?, ?)";
                    executeQuery($query, [$order_id, $item['menu_id'], $item['qty'], $item['harga'], $subtotal]);
                    
                    // Kurangi Stok
                    executeQuery("UPDATE menu SET stok = stok - ? WHERE id = ?", [$item['qty'], $item['menu_id']]);
                }
                
                // Send notification to related penjual (owners of menu items)
                $warung_ids = [];
                foreach ($_SESSION['cart'] as $item) {
                    // Get warung_id from menu
                    $menu = getRow("SELECT warung_id FROM menu WHERE id = ?", [$item['menu_id']]);
                    if ($menu && !in_array($menu['warung_id'], $warung_ids)) {
                        $warung_ids[] = $menu['warung_id'];
                        
                        // Get penjual from warung
                        $warung = getRow("SELECT pemilik_id FROM warung WHERE id = ?", [$menu['warung_id']]);
                        if ($warung) {
                            // Insert notification for penjual
                            $notif_query = "INSERT INTO notifications (user_id, order_id, type, message, role, is_read) VALUES (?, ?, 'order', 'Pesanan baru dari pembeli', 'pedagang', 0)";
                            execute($notif_query, [$warung['pemilik_id'], $order_id]);
                        }
                    }
                }
                
                // Send notification to ALL kasirs (Fix: Agar semua kasir dapat notifikasi)
                $kasirs = getRows("SELECT id FROM users WHERE role = 'kasir'");
                foreach ($kasirs as $k) {
                    execute("INSERT INTO notifications (user_id, order_id, type, message, role, is_read) VALUES (?, ?, 'order', 'Pesanan baru masuk', 'kasir', 0)", [$k['id'], $order_id]);
                }
                
                // Commit Transaction
                $conn->commit();
                
                // Clear cart
                unset($_SESSION['cart']);
                
                $success = 'Pesanan berhasil dibuat! Silakan menunggu konfirmasi.';
                
                // Redirect to pesanan detail setelah 2 detik
                header('Refresh: 2; url=pesanan.php?pesanan_id=' . $order_id);
            }
        } catch (Exception $e) {
            // Rollback jika ada error
            if (isset($conn)) $conn->rollback();
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
    }
}

// Get cart
$cart = $_SESSION['cart'] ?? [];
$total = 0;

if ($cart) {
    foreach ($cart as $item) {
        $subtotal = $item['harga'] * $item['qty'];
        $total += $subtotal;
    }
}
?>
<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/sidebar.php'; ?>

<div class="page-header">
    <h1 class="page-title">🛒 Keranjang Saya</h1>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger">
        ✗ <?php echo esc($error); ?>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success">
        ✓ <?php echo esc($success); ?>
    </div>
<?php endif; ?>

<?php if (empty($cart)): ?>
    <div class="empty-state">
        <div class="empty-state-icon">🛒</div>
        <div class="empty-state-text">Keranjang Anda kosong</div>
        <div class="empty-state-action">
            <a href="dashboard.php" class="btn btn-primary">
                ← Kembali ke Warung
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-header">
            <h3>Ringkasan Pesanan</h3>
        </div>
        
        <div class="card-body">
            <div class="table-responsive" style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Menu</th>
                            <th style="text-align: right;">Harga</th>
                            <th style="text-align: center;">Qty</th>
                            <th style="text-align: right;">Subtotal</th>
                            <th style="text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart as $item_id => $item): ?>
                            <tr>
                                <td><?php echo esc($item['nama_menu']); ?></td>
                                <td style="text-align: right;"><?php echo formatCurrency($item['harga']); ?></td>
                                <td style="text-align: center;"><?php echo $item['qty']; ?></td>
                                <td style="text-align: right;">
                                    <?php echo formatCurrency($item['harga'] * $item['qty']); ?>
                                </td>
                                <td style="text-align: center;">
                                    <form method="POST" style="display: inline;">
                                        <?php csrf_field(); ?>
                                        <input type="hidden" name="remove_id" value="<?php echo $item_id; ?>">
                                        <button type="submit" name="remove_item" class="btn btn-danger btn-sm" onclick="return confirm('Hapus dari keranjang?')">
                                            🗑️ Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card-footer">
            <div style="flex: 1; text-align: right; font-weight: 600;">
                Total Pesanan: <span style="font-size: 1.3rem; color: #667eea;">
                    <?php echo formatCurrency($total); ?>
                </span>
            </div>
        </div>
    </div>
    
    <div style="margin-top: 2rem; display: flex; gap: 1rem; justify-content: flex-end;">
        <a href="dashboard.php" class="btn btn-secondary">
            ← Lanjut Belanja
        </a>
        <form method="POST" style="display: inline;">
            <?php csrf_field(); ?>
            <button type="submit" name="checkout" class="btn btn-primary btn-lg">
                ✓ Checkout
            </button>
        </form>
    </div>
<?php endif; ?>

</main>
<?php require_once '../includes/footer.php'; ?>
