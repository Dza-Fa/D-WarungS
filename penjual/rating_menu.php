<?php
/**
 * Halaman Rating & Review Menu Penjual
 */

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pedagang') {
    header('Location: /D-WarungS/auth/login.php');
    exit();
}

require_once '../config/db.php';
require_once '../config/helpers.php'; // Tambahkan helper untuk renderStars

$page_title = 'Rating Menu Saya';

// Get warung milik penjual
$warung = getRow("SELECT * FROM warung WHERE pemilik_id = ?", [$_SESSION['user_id']]);

if (!$warung) {
    header('Location: dashboard.php');
    exit();
}

// Get rating untuk menu di warung ini
$query = "
    SELECT r.*, m.nama_menu, m.gambar, u.nama as pembeli_nama, 
           COUNT(*) OVER (PARTITION BY m.id) as total_rating,
           AVG(r.rating) OVER (PARTITION BY m.id) as avg_rating
    FROM ratings r
    JOIN menu m ON r.menu_id = m.id
    JOIN orders o ON r.order_id = o.id
    JOIN users u ON r.pembeli_id = u.id
    WHERE m.warung_id = ?
    ORDER BY m.id DESC, r.created_at DESC
";
$ratings = getRows($query, [$warung['id']]);

// Calculate menu stats
$menu_stats = [];
if (!empty($ratings)) {
    foreach ($ratings as $rating) {
        $menu_id = $rating['menu_id'];
        if (!isset($menu_stats[$menu_id])) {
            $menu_stats[$menu_id] = [
                'nama_menu' => $rating['nama_menu'],
                'foto' => $rating['gambar'],
                'total_ratings' => $rating['total_rating'], // Perbaikan: Gunakan nilai total_rating dari query
                'avg_rating' => round($rating['avg_rating'], 1), // Gunakan nilai avg_rating dari query
                'ratings' => []
            ];
        }
        $menu_stats[$menu_id]['ratings'][] = $rating;
    }
}

// Overall stats
$total_ratings = count($ratings);
$avg_overall = 0;
if ($total_ratings > 0) {
    $avg_overall = round(array_sum(array_column($ratings, 'rating')) / $total_ratings, 1);
}
?>
<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/sidebar.php'; ?>

<div class="page-header">
    <a href="dashboard.php" style="color: #667eea; text-decoration: none; margin-bottom: 1rem; display: inline-block;">
        ← Kembali ke Dashboard
    </a>
    <h1 class="page-title">⭐ Rating & Review Menu</h1>
    <p class="page-subtitle"><?php echo esc($warung['nama_warung']); ?></p>
</div>

<!-- Overall Stats -->
<?php if ($total_ratings > 0): ?>
    <div class="grid grid-3" style="margin-bottom: 2rem;">
        <div class="card">
            <div style="text-align: center; padding: 1.5rem;">
                <div style="font-size: 3rem; margin-bottom: 0.5rem;">
                    <?php echo renderStars($avg_overall); ?>
                </div>
                <div style="color: #666; font-size: 0.9rem; margin-bottom: 0.5rem;">
                    Rating Rata-rata
                </div>
                <div style="font-size: 1.5rem; font-weight: 700; color: #667eea;">
                    <?php echo $avg_overall; ?>/5
                </div>
            </div>
        </div>
        
        <div class="card">
            <div style="text-align: center; padding: 1.5rem;">
                <div style="font-size: 2.5rem; font-weight: 700; margin-bottom: 0.5rem;">
                    <?php echo $total_ratings; ?>
                </div>
                <div style="color: #666; font-size: 0.9rem;">
                    Total Rating Diterima
                </div>
            </div>
        </div>
        
        <div class="card">
            <div style="text-align: center; padding: 1.5rem;">
                <div style="font-size: 2.5rem; font-weight: 700; margin-bottom: 0.5rem;">
                    <?php echo count($menu_stats); ?>
                </div>
                <div style="color: #666; font-size: 0.9rem;">
                    Menu Dinilai Pembeli
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-body">
            <div class="empty-state" style="padding: 3rem;">
                <div class="empty-state-icon">⭐</div>
                <div class="empty-state-text">Belum ada rating untuk menu Anda</div>
                <div class="empty-state-subtext" style="font-size: 0.9rem; color: #999;">
                    Rating akan muncul setelah pembeli mengkonfirmasi pesanan dan memberikan review
                </div>
                <div class="empty-state-action">
                    <a href="dashboard.php" class="btn btn-primary">
                        📊 Lihat Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Menu Ratings -->
<?php if (!empty($menu_stats)): ?>
    <?php foreach ($menu_stats as $menu_id => $menu): ?>
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <div style="width: 60px; height: 60px; background: #f8f9fa; border-radius: 5px; overflow: hidden; flex-shrink: 0;">
                        <?php if (!empty($menu['foto']) && file_exists('../assets/uploads/menu/' . $menu['foto'])): ?>
                            <img src="../assets/uploads/menu/<?php echo esc($menu['foto']); ?>" 
                                 alt="<?php echo esc($menu['nama_menu']); ?>"
                                 style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">🍜</div>
                        <?php endif; ?>
                    </div>
                    
                    <div style="flex: 1;">
                        <h3 style="margin: 0; font-size: 1.1rem;"><?php echo esc($menu['nama_menu']); ?></h3>
                        <p style="margin: 0.5rem 0 0 0; color: #666; font-size: 0.9rem;">
                            <span style="color: #ffc107;">
                                <?php echo renderStars($menu['avg_rating']); ?>
                            </span>
                            <?php echo $menu['avg_rating']; ?>/5 
                            (<?php echo $menu['total_ratings']; ?> rating)
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <?php if (!empty($menu['ratings'])): ?>
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <?php foreach ($menu['ratings'] as $rating): ?>
                            <div style="padding: 1rem; background: #f8f9fa; border-radius: 5px; border-left: 3px solid #ffc107;">
                                <div style="display: flex; justify-content: space-between; align-items: start;">
                                    <div>
                                        <strong style="color: #333;"><?php echo esc($rating['pembeli_nama']); ?></strong>
                                        <div style="color: #666; font-size: 0.85rem; margin-top: 0.25rem;">
                                            <?php echo formatDateTime($rating['created_at']); ?>
                                        </div>
                                    </div>
                                    <div style="text-align: right;">
                                        <div style="color: #ffc107; font-size: 1.2rem; margin-bottom: 0.25rem;">
                                            <?php echo renderStars($rating['rating']); ?>
                                        </div>
                                        <span style="background: #ffc107; color: #fff; padding: 0.25rem 0.5rem; border-radius: 3px; font-size: 0.8rem; font-weight: 600;">
                                            <?php echo $rating['rating']; ?>/5
                                        </span>
                                    </div>
                                </div>
                                
                                <?php if (!empty($rating['review'])): ?>
                                    <p style="margin: 0.75rem 0 0 0; color: #555; line-height: 1.5;">
                                        "<?php echo esc($rating['review']); ?>"
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

</main>
<?php require_once '../includes/footer.php'; ?>
