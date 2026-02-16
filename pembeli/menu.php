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

$query .= " WHERE m.warung_id = ? AND m.status_aktif = 1";
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
$error = '';
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
            $error = 'Stok tidak mencukupi! Sisa stok: ' . $menu_data['stok'];
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

<style>
.menu-card { transition: transform 0.2s, box-shadow 0.2s; height: 100%; display: flex; flex-direction: column; }
.menu-card:hover { transform: translateY(-5px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important; }
</style>

<div class="page-header">
    <a href="dashboard.php" class="btn btn-outline-secondary btn-sm" style="margin-bottom: 1rem; display: inline-flex; align-items: center; gap: 0.5rem; border: 1px solid #e2e8f0;">
        <i class="fas fa-arrow-left"></i> Kembali ke Warung
    </a>
    <h1 class="page-title"><?php echo esc($warung['nama_warung']); ?></h1>
    <p class="page-subtitle"><?php echo esc($warung['deskripsi']); ?></p>
</div>

<?php if ($message): ?>
    <div class="alert alert-success" style="background-color: #c6f6d5; color: #22543d; padding: 1rem; margin-bottom: 1.5rem;">
        ✓ <?php echo esc($message); ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger" style="background-color: #fed7d7; color: #822727; padding: 1rem; margin-bottom: 1.5rem;">
        ✗ <?php echo esc($error); ?>
    </div>
<?php endif; ?>

<!-- Search & Filter Bar -->
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-body" style="padding: 1.25rem;">
        <form method="GET" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
            <input type="hidden" name="warung_id" value="<?php echo $warung_id; ?>">
            <div style="flex: 1; min-width: 200px;">
                <input type="text" name="search" placeholder="Cari menu makanan/minuman..." value="<?php echo esc($search); ?>" style="width: 100%;">
            </div>
            <button type="submit" class="btn btn-primary">🔍 Cari Menu</button>
            
            <!-- Favorit Filter Toggle -->
            <a href="menu.php?warung_id=<?php echo $warung_id; ?>&favorit=<?php echo $show_favorit ? 'all' : 'only'; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
               class="btn <?php echo $show_favorit ? 'btn-warning' : 'btn-outline-secondary'; ?>" 
               style="white-space: nowrap; border: 1px solid #e2e8f0;">
                ❤️ <?php echo $show_favorit ? 'Tampilkan Semua' : 'Hanya Favorit'; ?>
            </a>
        </form>
    </div>
</div>

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
    <div class="grid grid-4" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1.5rem;">
        <?php foreach ($menu as $m): ?>
            <div class="card menu-card">
                <div class="product-image" style="background: #f8f9fa; height: 180px; overflow: hidden; display: flex; align-items: center; justify-content: center; position: relative;">
                    <?php if (!empty($m['gambar']) && file_exists('../assets/uploads/menu/' . $m['gambar'])): ?>
                        <img src="../assets/uploads/menu/<?php echo esc($m['gambar']); ?>" 
                             alt="<?php echo esc($m['nama_menu']); ?>"
                             style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <div style="font-size: 3rem; opacity: 0.5;">🍜</div>
                    <?php endif; ?>
                </div>
                <div class="product-info" style="padding: 1.25rem; flex: 1; display: flex; flex-direction: column;">
                    <h3 class="product-name" style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0.5rem; color: #2d3748;"><?php echo esc($m['nama_menu']); ?></h3>
                    <p class="product-description" style="color: #718096; font-size: 0.9rem; line-height: 1.5; margin-bottom: 1rem; flex: 1;">
                        <?php echo esc(substr($m['deskripsi'] ?? '', 0, 80)); ?>
                    </p>
                    
                    <div class="product-price" style="font-size: 1.1rem; font-weight: 700; color: #667eea; margin-bottom: 0.5rem;">
                        <?php echo formatCurrency($m['harga']); ?>
                    </div>
                    
                    <div class="product-stock <?php echo $m['stok'] == 0 ? 'out' : ''; ?>" style="font-size: 0.85rem; color: #718096; margin-bottom: 1rem;">
                        <?php if ($m['stok'] > 0): ?>
                            Tersedia: <strong><?php echo $m['stok']; ?></strong> porsi
                        <?php else: ?>
                            <span style="color: #e53e3e;">❌ Stok Habis</span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($m['stok'] > 0): ?>
                        <div style="display: flex; gap: 0.5rem;">
                            <form method="POST" style="flex: 1; display: flex; gap: 0.5rem;">
                                <?php csrf_field(); ?>
                                <input type="hidden" name="menu_id" value="<?php echo $m['id']; ?>">
                                <input type="hidden" name="add_to_cart" value="1">
                                <input type="number" name="qty" value="1" min="1" max="<?php echo $m['stok']; ?>" style="width: 60px; text-align: center;">
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
