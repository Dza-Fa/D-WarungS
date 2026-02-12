<?php
/**
 * Halaman Menu Warung
 */

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pembeli') {
    header('Location: /D-WarungS/auth/login.php');
    exit();
}

require_once '../config/db.php';

// Get warung_id dari URL
$warung_id = intval($_GET['warung_id'] ?? 0);

if (!$warung_id) {
    header('Location: dashboard.php');
    exit();
}

// Get warung info
$query = "SELECT * FROM warung WHERE id = ?";
$warung = getRow($query, [$warung_id]);

if (!$warung) {
    header('Location: dashboard.php');
    exit();
}

$page_title = 'Menu ' . $warung['nama_warung'];

// Get search query
$search = trim($_GET['search'] ?? '');

// Query menu dengan prepared statement
if ($search) {
    $query = "SELECT * FROM menu WHERE warung_id = ? AND (nama_menu LIKE ? OR deskripsi LIKE ?) ORDER BY nama_menu ASC";
    $search_term = "%$search%";
    $menu = getRows($query, [$warung_id, $search_term, $search_term]);
} else {
    $query = "SELECT * FROM menu WHERE warung_id = ? ORDER BY nama_menu ASC";
    $menu = getRows($query, [$warung_id]);
}

// Handle add to cart
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $menu_id = intval($_POST['menu_id']);
    $qty = intval($_POST['qty']);
    
    // Get menu data
    $menu_data = getRow("SELECT * FROM menu WHERE id = ? AND warung_id = ?", [$menu_id, $warung_id]);
    
    if ($menu_data && $qty > 0 && $qty <= $menu_data['stok']) {
        // Initialize cart if not exists
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        // Add or update cart item
        $cart_key = $menu_id;
        if (isset($_SESSION['cart'][$cart_key])) {
            $_SESSION['cart'][$cart_key]['qty'] += $qty;
        } else {
            $_SESSION['cart'][$cart_key] = [
                'menu_id' => $menu_id,
                'nama_menu' => $menu_data['nama_menu'],
                'harga' => $menu_data['harga'],
                'qty' => $qty,
                'warung_id' => $warung_id
            ];
        }
        
        $message = 'Item ditambahkan ke keranjang!';
    }
}
?>
<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/sidebar.php'; ?>

<div class="page-header">
    <a href="dashboard.php" style="color: #667eea; text-decoration: none; margin-bottom: 1rem; display: inline-block;">
        ← Kembali ke Warung
    </a>
    <h1 class="page-title"><?php echo esc($warung['nama_warung']); ?></h1>
    <p class="page-subtitle"><?php echo esc($warung['deskripsi']); ?></p>
</div>

<?php if ($message): ?>
    <div class="alert alert-success">
        ✓ <?php echo esc($message); ?>
    </div>
<?php endif; ?>

<!-- Search Bar -->
<form method="GET" class="search-bar">
    <input type="hidden" name="warung_id" value="<?php echo $warung_id; ?>">
    <input type="text" name="search" placeholder="Cari menu..." value="<?php echo esc($search); ?>">
    <button type="submit">🔍 Cari</button>
</form>

<?php if (empty($menu)): ?>
    <div class="empty-state">
        <div class="empty-state-icon">🍽️</div>
        <div class="empty-state-text">
            <?php if ($search): ?>
                Menu tidak ditemukan
            <?php else: ?>
                Belum ada menu di warung ini
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <div class="grid grid-4">
        <?php foreach ($menu as $m): ?>
            <div class="card">
                <div class="product-image" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); font-size: 3rem;">
                    🍜
                </div>
                <div class="product-info">
                    <h3 class="product-name"><?php echo esc($m['nama_menu']); ?></h3>
                    <p class="product-description">
                        <?php echo esc(substr($m['deskripsi'] ?? '', 0, 60)); ?>
                    </p>
                    
                    <div class="product-price">
                        <?php echo formatCurrency($m['harga']); ?>
                    </div>
                    
                    <div class="product-stock <?php echo $m['stok'] == 0 ? 'out' : ''; ?>">
                        <?php if ($m['stok'] > 0): ?>
                            Stok: <?php echo $m['stok']; ?>
                        <?php else: ?>
                            ❌ Stok Habis
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($m['stok'] > 0): ?>
                        <form method="POST" class="product-footer">
                            <input type="hidden" name="menu_id" value="<?php echo $m['id']; ?>">
                            <input type="hidden" name="add_to_cart" value="1">
                            <input type="number" name="qty" value="1" min="1" max="<?php echo $m['stok']; ?>" style="width: 60px; padding: 0.5rem; border: 1px solid #ddd; border-radius: 5px;">
                            <button type="submit" class="btn btn-primary" style="flex: 1;">
                                🛒 Pesan
                            </button>
                        </form>
                    <?php else: ?>
                        <button class="btn btn-secondary btn-block" disabled>Stok Habis</button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])): ?>
        <div style="margin-top: 2rem; text-align: center;">
            <a href="keranjang.php" class="btn btn-primary btn-lg">
                🛒 Lihat Keranjang (<?php echo count($_SESSION['cart']); ?> item)
            </a>
        </div>
    <?php endif; ?>
<?php endif; ?>

</main>
<?php require_once '../includes/footer.php'; ?>
