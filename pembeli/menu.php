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
require_once '../config/queries.php'; // Diperlukan untuk fungsi isFavorite dan toggleFavorite
require_once '../config/security.php';

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

// Get filter untuk favorit
$show_favorit = isset($_GET['favorit']) && $_GET['favorit'] == 'only' ? true : false;

// Get search query
$search = trim($_GET['search'] ?? '');

// Build menu query secara dinamis
$query = "SELECT m.* FROM menu m";
$params = [];

if ($show_favorit) {
    $query .= " JOIN favorites f ON m.id = f.menu_id AND f.pembeli_id = ?";
    $params[] = $_SESSION['user_id'];
}

$query .= " WHERE m.warung_id = ?";
$params[] = $warung_id;

if ($search) {
    $query .= " AND (m.nama_menu LIKE ? OR m.deskripsi LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
}

$query .= " ORDER BY m.nama_menu ASC";
$menu = getRows($query, $params);

// Get semua ID menu favorit user untuk halaman ini agar efisien (menghindari N+1 query)
$favorite_ids_rows = getRows("SELECT menu_id FROM favorites WHERE pembeli_id = ?", [$_SESSION['user_id']]);
$favorite_ids = array_column($favorite_ids_rows, 'menu_id');

// Handle add to cart
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        // Silent fail or redirect
    } else {
    $menu_id = intval($_POST['menu_id']);
    $qty = intval($_POST['qty']);
    
    // Get menu data
    $menu_data = getRow("SELECT * FROM menu WHERE id = ? AND warung_id = ?", [$menu_id, $warung_id]);
    
    if ($menu_data) {
        // Initialize cart if not exists
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        // Cek jumlah yang sudah ada di keranjang
        $current_qty_in_cart = isset($_SESSION['cart'][$menu_id]) ? $_SESSION['cart'][$menu_id]['qty'] : 0;
        $total_qty = $current_qty_in_cart + $qty;
        
        if ($total_qty <= $menu_data['stok']) {
            // Add or update cart item
            if (isset($_SESSION['cart'][$menu_id])) {
                $_SESSION['cart'][$menu_id]['qty'] += $qty;
            } else {
                $_SESSION['cart'][$menu_id] = [
                    'menu_id' => $menu_id,
                    'nama_menu' => $menu_data['nama_menu'],
                    'harga' => $menu_data['harga'],
                    'qty' => $qty,
                    'warung_id' => $warung_id
                ];
            }
            $message = 'Item ditambahkan ke keranjang!';
        } else {
            // Tampilkan pesan error jika stok tidak cukup (bisa dihandle UI lebih baik, tapi ini basic protection)
            // Karena struktur kode saat ini menggunakan $message untuk sukses, kita bisa set message warning atau biarkan diam
            // Untuk sekarang kita skip penambahan
        }
    }
    }
}

// Handle save to favorites
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_favorit'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        // Silent fail
    } else {
    $menu_id = intval($_POST['menu_id']);
    // Gunakan fungsi toggleFavorite yang sudah ada untuk mengurangi duplikasi
    $is_added = toggleFavorite($_SESSION['user_id'], $menu_id);
    if ($is_added) {
        $message = 'Menu ditambahkan ke favorit!';
    } else {
        $message = 'Menu dihapus dari favorit!';
    }
    // Refresh daftar favorit
    $favorite_ids_rows = getRows("SELECT menu_id FROM favorites WHERE pembeli_id = ?", [$_SESSION['user_id']]);
    $favorite_ids = array_column($favorite_ids_rows, 'menu_id');
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

<!-- Search & Filter Bar -->
<form method="GET" class="search-bar" style="display: flex; gap: 0.5rem; margin-bottom: 1rem; align-items: center;">
    <input type="hidden" name="warung_id" value="<?php echo $warung_id; ?>">
    <input type="text" name="search" placeholder="Cari menu..." value="<?php echo esc($search); ?>" style="flex: 1;">
    <button type="submit" style="white-space: nowrap;">🔍 Cari</button>
    
    <!-- Favorit Filter Toggle -->
    <a href="menu.php?warung_id=<?php echo $warung_id; ?>&favorit=<?php echo $show_favorit ? 'all' : 'only'; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
       class="btn <?php echo $show_favorit ? 'btn-warning' : 'btn-outline-secondary'; ?>" 
       style="white-space: nowrap;">
        ❤️ <?php echo $show_favorit ? 'Semua Menu' : 'Hanya Favorit'; ?>
    </a>
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
                <div class="product-image" style="background: #f8f9fa; padding: 1rem; height: 200px; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                    <?php if (!empty($m['foto']) && file_exists('../assets/uploads/menu/' . $m['foto'])): ?>
                        <img src="../assets/uploads/menu/<?php echo esc($m['foto']); ?>" 
                             alt="<?php echo esc($m['nama_menu']); ?>"
                             style="max-width: 100%; max-height: 100%; object-fit: cover; border-radius: 5px;">
                    <?php else: ?>
                        <div style="font-size: 3rem; opacity: 0.5;">🍜</div>
                    <?php endif; ?>
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
                        <div style="display: flex; gap: 0.5rem;">
                            <form method="POST" style="flex: 1; display: flex; gap: 0.5rem;">
                                <?php csrf_field(); ?>
                                <input type="hidden" name="menu_id" value="<?php echo $m['id']; ?>">
                                <input type="hidden" name="add_to_cart" value="1">
                                <input type="number" name="qty" value="1" min="1" max="<?php echo $m['stok']; ?>" style="width: 60px; padding: 0.5rem; border: 1px solid #ddd; border-radius: 5px;">
                                <button type="submit" class="btn btn-primary" style="flex: 1;">
                                    🛒 Pesan
                                </button>
                            </form>
                            <form method="POST" style="width: auto;">
                                <?php csrf_field(); ?>
                                <input type="hidden" name="menu_id" value="<?php echo $m['id']; ?>">
                                <input type="hidden" name="save_favorit" value="1">
                                <?php
                                // Cek apakah menu ini ada di daftar favorit user
                                $is_favorited = in_array($m['id'], $favorite_ids);
                                ?>
                                <button type="submit" class="btn <?php echo $is_favorited ? 'btn-warning' : 'btn-outline-primary'; ?>" style="padding: 0.5rem 0.75rem;" title="<?php echo $is_favorited ? 'Hapus dari favorit' : 'Simpan ke favorit'; ?>">
                                    <?php echo $is_favorited ? '★' : '❤️'; ?>
                                </button>
                            </form>
                        </div>
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
