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

<style>
.warung-card {
    transition: transform 0.2s, box-shadow 0.2s;
}
.warung-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
}
</style>

<div class="page-header" style="margin-bottom: 2rem;">
    <h1 class="page-title" style="font-size: 2rem; color: #2d3748;">🏪 Warung Kantin</h1>
    <p class="page-subtitle" style="color: #718096; font-size: 1.1rem;">Temukan makanan favoritmu dari berbagai warung di kantin</p>
</div>

<!-- Search Bar -->
<div class="card" style="margin-bottom: 2rem; border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
    <div class="card-body" style="padding: 1.5rem;">
        <form method="GET" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
            <div style="flex: 1; position: relative; min-width: 200px;">
                <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #a0aec0;">🔍</span>
                <input type="text" name="search" placeholder="Cari nama warung..." value="<?php echo esc($search); ?>" 
                       style="width: 100%; padding: 0.75rem 1rem 0.75rem 2.5rem; border: 1px solid #e2e8f0; border-radius: 0.5rem; font-size: 1rem; outline: none; transition: border-color 0.2s;">
            </div>
            <button type="submit" class="btn btn-primary" style="padding: 0.75rem 1.5rem; font-weight: 600;">
                Cari Warung
            </button>
        </form>
    </div>
</div>

<?php if (empty($warung)): ?>
    <div class="empty-state" style="text-align: center; padding: 4rem 2rem; background: white; border-radius: 1rem; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        <div class="empty-state-icon" style="font-size: 4rem; margin-bottom: 1rem;">🍽️</div>
        <div class="empty-state-text" style="font-size: 1.25rem; font-weight: 600; color: #2d3748; margin-bottom: 0.5rem;">
            <?php if ($search): ?>
                Warung tidak ditemukan
            <?php else: ?>
                Belum ada warung yang tersedia
            <?php endif; ?>
        </div>
        <p style="color: #718096;">Coba kata kunci lain atau kembali lagi nanti.</p>
        <?php if ($search): ?>
            <a href="dashboard.php" class="btn btn-outline-secondary" style="margin-top: 1rem;">Reset Pencarian</a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="grid grid-3" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
        <?php foreach ($warung as $w): ?>
            <?php
            // Cek status buka/tutup
            $current_time = date('H:i:s');
            $is_open = ($current_time >= $w['jam_buka'] && $current_time <= $w['jam_tutup']);
            ?>
            <div class="card warung-card" style="height: 100%; display: flex; flex-direction: column; border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.05); overflow: hidden;">
                <div class="product-image" style="height: 160px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; position: relative;">
                    <div style="font-size: 4rem;">🍜</div>
                    
                    <!-- Status Badge -->
                    <div style="position: absolute; top: 1rem; right: 1rem; padding: 0.25rem 0.75rem; border-radius: 9999px; background: <?php echo $is_open ? '#c6f6d5' : '#fed7d7'; ?>; color: <?php echo $is_open ? '#22543d' : '#822727'; ?>; font-size: 0.75rem; font-weight: 700; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <?php echo $is_open ? 'BUKA' : 'TUTUP'; ?>
                    </div>
                </div>
                <div class="product-info" style="padding: 1.5rem; flex: 1; display: flex; flex-direction: column;">
                    <h3 class="product-name" style="font-size: 1.25rem; font-weight: 700; color: #2d3748; margin-bottom: 0.5rem;"><?php echo esc($w['nama_warung']); ?></h3>
                    <p class="product-description" style="color: #718096; font-size: 0.9rem; line-height: 1.5; margin-bottom: 1.5rem; flex: 1;">
                        <?php echo esc(substr($w['deskripsi'] ?? '', 0, 100)); ?><?php echo strlen($w['deskripsi'] ?? '') > 100 ? '...' : ''; ?>
                    </p>
                    
                    <div class="warung-meta" style="margin-bottom: 1.5rem; font-size: 0.85rem; color: #4a5568;">
                        <div style="display: flex; align-items: center; margin-bottom: 0.5rem;">
                            <span style="margin-right: 0.5rem;">📍</span>
                            <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo esc($w['alamat'] ?? 'Lokasi tidak tersedia'); ?></span>
                        </div>
                        <div style="display: flex; align-items: center;">
                            <span style="margin-right: 0.5rem;">🕐</span>
                            <span><?php echo date('H:i', strtotime($w['jam_buka'])); ?> - <?php echo date('H:i', strtotime($w['jam_tutup'])); ?> WIB</span>
                        </div>
                    </div>
                    
                    <a href="menu.php?warung_id=<?php echo $w['id']; ?>" class="btn btn-primary btn-block" style="text-align: center; justify-content: center; <?php echo !$is_open ? 'background-color: #a0aec0; border-color: #a0aec0;' : ''; ?>">
                        <?php echo $is_open ? 'Lihat Menu & Pesan →' : 'Lihat Menu (Tutup)'; ?>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

</main>
<?php require_once '../includes/footer.php'; ?>
