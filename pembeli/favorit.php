<?php
/**
 * Halaman Menu Favorit Pembeli
 */

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pembeli') {
    header('Location: /D-WarungS/auth/login.php');
    exit();
}

require_once '../config/db.php';
require_once '../config/security.php';

$page_title = 'Menu Favorit';

// Get sort parameter - validasi
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'newest';
$allowed_sorts = ['newest', 'nama', 'harga_terendah', 'harga_tertinggi'];
if (!in_array($sort, $allowed_sorts)) {
    $sort = 'newest';
}

// Get favorit menu dengan warung info
$sort_clause = "f.created_at DESC";
if ($sort === 'harga_tertinggi') {
    $sort_clause = "m.harga DESC";
} elseif ($sort === 'harga_terendah') {
    $sort_clause = "m.harga ASC";
} elseif ($sort === 'nama') {
    $sort_clause = "m.nama_menu ASC";
}

$query = "
    SELECT f.*, m.nama_menu, m.harga, m.stok, m.gambar, m.deskripsi, w.id as warung_id, w.nama_warung
    FROM favorites f
    JOIN menu m ON f.menu_id = m.id
    JOIN warung w ON m.warung_id = w.id
    WHERE f.pembeli_id = ?
    ORDER BY $sort_clause
";
$favorit_menu = getRows($query, [$_SESSION['user_id']]);

// Handle add to cart from favorit
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    if (validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $menu_id = intval($_POST['menu_id']);
        $qty = intval($_POST['qty'] ?? 1);
    
    // Get menu data
    $menu_data = getRow("SELECT * FROM menu WHERE id = ?", [$menu_id]);
    
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
                'warung_id' => $menu_data['warung_id']
            ];
        }
        
        $message = 'Item ditambahkan ke keranjang!';
    }
    }
}

// Handle remove from favorit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_favorit'])) {
    if (validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $menu_id = intval($_POST['menu_id']);
        $query = "DELETE FROM favorites WHERE pembeli_id = ? AND menu_id = ?";
        if (execute($query, [$_SESSION['user_id'], $menu_id])) {
        header('Location: favorit.php?removed=1');
        exit();
    }
    }
}
?>
<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/sidebar.php'; ?>

<div class="page-header">
    <a href="dashboard.php" style="color: #667eea; text-decoration: none; margin-bottom: 1rem; display: inline-block;">
        ← Kembali ke Dashboard
    </a>
    <h1 class="page-title">❤️ Menu Favorit Saya</h1>
    <p class="page-subtitle"><?php echo count($favorit_menu); ?> menu favorit</p>
</div>

<?php if (isset($_GET['removed']) && $_GET['removed'] == '1'): ?>
    <div class="alert alert-success">
        ✓ Menu dihapus dari favorit
    </div>
<?php endif; ?>

<?php if ($message): ?>
    <div class="alert alert-success">
        ✓ <?php echo esc($message); ?>
    </div>
<?php endif; ?>

<?php if (empty($favorit_menu)): ?>
    <div class="card">
        <div class="card-body">
            <div class="empty-state" style="padding: 3rem;">
                <div class="empty-state-icon">❤️</div>
                <div class="empty-state-text">Belum ada menu favorit</div>
                <div class="empty-state-subtext" style="font-size: 0.9rem; color: #999;">
                    Tekan tombol hati pada menu untuk menambahkan ke favorit
                </div>
                <div class="empty-state-action">
                    <a href="dashboard.php" class="btn btn-primary">
                        🛒 Belanja Menu
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- Sort Options -->
    <div style="margin-bottom: 1.5rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <span style="display: flex; align-items: center; color: #666; margin-right: 0.5rem;">Urutkan:</span>
        <a href="favorit.php?sort=newest" class="btn <?php echo $sort === 'newest' ? 'btn-primary' : 'btn-outline-secondary'; ?> btn-sm">
            🕐 Terbaru Ditambahkan
        </a>
        <a href="favorit.php?sort=nama" class="btn <?php echo $sort === 'nama' ? 'btn-primary' : 'btn-outline-secondary'; ?> btn-sm">
            🔤 Nama A-Z
        </a>
        <a href="favorit.php?sort=harga_terendah" class="btn <?php echo $sort === 'harga_terendah' ? 'btn-primary' : 'btn-outline-secondary'; ?> btn-sm">
            💰 Harga Terendah
        </a>
        <a href="favorit.php?sort=harga_tertinggi" class="btn <?php echo $sort === 'harga_tertinggi' ? 'btn-primary' : 'btn-outline-secondary'; ?> btn-sm">
            💸 Harga Tertinggi
        </a>
    </div>
    
    <!-- Menu Grid -->
    <div class="grid grid-4">
        <?php foreach ($favorit_menu as $menu): ?>
            <div class="card">
                <div class="product-image" style="background: #f8f9fa; padding: 1rem; height: 200px; overflow: hidden; display: flex; align-items: center; justify-content: center; position: relative;">
                    <?php if (!empty($menu['gambar']) && file_exists('../assets/uploads/menu/' . $menu['gambar'])): ?>
                        <img src="../assets/uploads/menu/<?php echo esc($menu['gambar']); ?>" 
                             alt="<?php echo esc($menu['nama_menu']); ?>"
                             style="max-width: 100%; max-height: 100%; object-fit: cover; border-radius: 5px;">
                    <?php else: ?>
                        <div style="font-size: 3rem; opacity: 0.5;">🍜</div>
                    <?php endif; ?>
                    
                    <!-- Favorit Badge -->
                    <div style="position: absolute; top: 0.5rem; right: 0.5rem; background: #ff6b6b; color: white; padding: 0.5rem; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; cursor: pointer;" title="Hapus dari favorit">
                        ❤️
                    </div>
                </div>
                
                <div class="product-info">
                    <div style="margin-bottom: 0.5rem;">
                        <small style="color: #999; font-size: 0.8rem;">
                            <?php echo esc($menu['nama_warung']); ?>
                        </small>
                    </div>
                    
                    <h3 class="product-name"><?php echo esc($menu['nama_menu']); ?></h3>
                    <p class="product-description">
                        <?php echo esc(substr($menu['deskripsi'] ?? '', 0, 60)); ?>
                    </p>
                    
                    <div class="product-price">
                        <?php echo formatCurrency($menu['harga']); ?>
                    </div>
                    
                    <div class="product-stock <?php echo $menu['stok'] == 0 ? 'out' : ''; ?>">
                        <?php if ($menu['stok'] > 0): ?>
                            Stok: <?php echo $menu['stok']; ?>
                        <?php else: ?>
                            ❌ Stok Habis
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($menu['stok'] > 0): ?>
                        <div style="display: flex; gap: 0.5rem;">
                            <form method="POST" style="flex: 1;">
                                <?php csrf_field(); ?>
                                <input type="hidden" name="menu_id" value="<?php echo $menu['menu_id']; ?>">
                                <input type="hidden" name="add_to_cart" value="1">
                                <button type="submit" class="btn btn-primary" style="width: 100%;">
                                    🛒 Pesan
                                </button>
                            </form>
                            
                            <form method="POST" style="width: auto;">
                                <?php csrf_field(); ?>
                                <input type="hidden" name="menu_id" value="<?php echo $menu['menu_id']; ?>">
                                <button type="submit" name="remove_favorit" class="btn btn-danger" style="width: 100%; padding: 0.5rem 0.75rem;" title="Hapus dari favorit">
                                    ✕
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div style="display: flex; gap: 0.5rem;">
                            <button class="btn btn-secondary btn-block" disabled style="flex: 1;">Stok Habis</button>
                            <form method="POST" style="width: auto;">
                                <?php csrf_field(); ?>
                                <input type="hidden" name="menu_id" value="<?php echo $menu['menu_id']; ?>">
                                <button type="submit" name="remove_favorit" class="btn btn-danger" style="width: 100%; padding: 0.5rem 0.75rem;" title="Hapus dari favorit">
                                    ✕
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

</main>
<?php require_once '../includes/footer.php'; ?>
