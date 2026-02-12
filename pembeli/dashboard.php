<?php
/**
 * Halaman Dashboard Pembeli - Daftar Warung
 */

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pembeli') {
    header('Location: /D-WarungS/auth/login.php');
    exit();
}

require_once '../config/db.php';

$page_title = 'Dashboard Pembeli';

// Get search query
$search = trim($_GET['search'] ?? '');

// Query warung dengan prepared statement
if ($search) {
    $query = "SELECT * FROM warung WHERE nama_warung LIKE ? OR deskripsi LIKE ? ORDER BY nama_warung ASC";
    $search_term = "%$search%";
    $warung = getRows($query, [$search_term, $search_term]);
} else {
    $query = "SELECT * FROM warung ORDER BY nama_warung ASC";
    $warung = getRows($query);
}
?>
<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/sidebar.php'; ?>

<div class="page-header">
    <h1 class="page-title">🏪 Warung Kantin</h1>
    <p class="page-subtitle">Pilih warung yang Anda inginkan</p>
</div>

<!-- Search Bar -->
<form method="GET" class="search-bar">
    <input type="text" name="search" placeholder="Cari warung..." value="<?php echo esc($search); ?>">
    <button type="submit">🔍 Cari</button>
</form>

<?php if (empty($warung)): ?>
    <div class="empty-state">
        <div class="empty-state-icon">🍽️</div>
        <div class="empty-state-text">
            <?php if ($search): ?>
                Warung tidak ditemukan
            <?php else: ?>
                Belum ada warung yang tersedia
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <div class="grid grid-3">
        <?php foreach ($warung as $w): ?>
            <div class="card">
                <div class="product-image" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); font-size: 3rem;">
                    🍜
                </div>
                <div class="product-info">
                    <h3 class="product-name"><?php echo esc($w['nama_warung']); ?></h3>
                    <p class="product-description">
                        <?php echo esc(substr($w['deskripsi'] ?? '', 0, 80)); ?>...
                    </p>
                    
                    <div class="product-stock" style="color: #666; font-size: 0.85rem;">
                        <div>📍 <?php echo esc($w['alamat'] ?? 'Tidak ada'); ?></div>
                        <div>🕐 <?php echo date('H:i', strtotime($w['jam_buka'])); ?> - <?php echo date('H:i', strtotime($w['jam_tutup'])); ?></div>
                    </div>
                    
                    <a href="menu.php?warung_id=<?php echo $w['id']; ?>" class="btn btn-primary btn-block">
                        Lihat Menu →
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

</main>
<?php require_once '../includes/footer.php'; ?>
