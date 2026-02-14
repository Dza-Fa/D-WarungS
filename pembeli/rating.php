<?php
/**
 * Halaman Rating & Review Pesanan
 */

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pembeli') {
    header('Location: /D-WarungS/auth/login.php');
    exit();
}

require_once '../config/db.php';
require_once '../config/queries.php';
require_once '../config/validators.php'; // Muat validator
require_once '../config/security.php'; // Tambahkan security helper

$page_title = 'Rating & Review';

// Ambil pesanan yang sudah selesai dan belum dirating
$pesanan_list = getUnratedOrders($_SESSION['user_id']);

// Handle submit rating
$error = '';
$message = '';
$rating_form_order_id = null;
$rating_form_menu_id = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_rating'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Sesi tidak valid atau sudah kedaluwarsa. Silakan coba lagi.';
        // Stop execution to prevent CSRF
    } else {
        $order_id = intval($_POST['order_id'] ?? 0);
        $menu_id = intval($_POST['menu_id'] ?? 0);
        $rating = intval($_POST['rating'] ?? 0);
        $review = trim($_POST['review'] ?? '');
        
        // Gunakan validator terpusat
        $validation = validateRating($rating, $review);
        
        if (!$order_id || !$menu_id) {
            $error = 'Data pesanan tidak valid!';
        } elseif (!$validation['valid']) {
            $error = implode(', ', $validation['errors']);
        } else {
            // Check apakah menu ada di order DAN order milik user yang login
            $item_check = getRow(
                "SELECT oi.id FROM order_items oi JOIN orders o ON oi.order_id = o.id WHERE oi.order_id = ? AND oi.menu_id = ? AND o.pembeli_id = ?",
                [$order_id, $menu_id, $_SESSION['user_id']]
            );
            
            if (!$item_check) {
                $error = 'Item tidak ada di pesanan!';
            } else {
                // Gunakan data yang sudah divalidasi
                $validated_rating = $validation['data']['rating'];
                $validated_review = $validation['data']['review'];
                
                if (saveRating($order_id, $menu_id, $_SESSION['user_id'], $validated_rating, $validated_review)) {
                    $message = 'Rating berhasil disimpan! Terima kasih atas review Anda.';
                } else {
                    $error = 'Gagal menyimpan rating!';
                }
            }
        }
        
        $rating_form_order_id = $order_id;
        $rating_form_menu_id = $menu_id;
    }
}

// Optimasi: Ambil semua data yang dibutuhkan dengan query yang lebih sedikit (hindari N+1)
$items_by_order = [];
$ratings_by_item = [];

if (!empty($pesanan_list)) {
    $order_ids = array_column($pesanan_list, 'id');
    $placeholders = implode(',', array_fill(0, count($order_ids), '?'));

    // 1. Ambil semua item untuk semua pesanan yang ditampilkan
    $all_items = getRows(
        "SELECT oi.*, m.nama_menu, m.gambar FROM order_items oi 
         JOIN menu m ON oi.menu_id = m.id 
         WHERE oi.order_id IN ($placeholders)",
        $order_ids
    );
    foreach ($all_items as $item) {
        $items_by_order[$item['order_id']][] = $item;
    }

    // 2. Ambil semua rating yang sudah ada untuk pesanan tersebut
    $all_ratings = getRows(
        "SELECT * FROM ratings WHERE pembeli_id = ? AND order_id IN ($placeholders)",
        array_merge([$_SESSION['user_id']], $order_ids)
    );
    // Buat map untuk akses cepat
    foreach ($all_ratings as $r) {
        $ratings_by_item[$r['order_id'] . '_' . $r['menu_id']] = $r;
    }
}
?>
<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/sidebar.php'; ?>

<div class="page-header">
    <a href="pesanan_selesai.php" style="color: #667eea; text-decoration: none; margin-bottom: 1rem; display: inline-block;">
        ← Kembali ke Riwayat Pesanan
    </a>
    <h1 class="page-title">⭐ Rating & Review</h1>
    <p class="page-subtitle">Bagikan pengalaman Anda dengan rating dan review</p>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger">
        ✗ <?php echo esc($error); ?>
    </div>
<?php endif; ?>

<?php if ($message): ?>
    <div class="alert alert-success">
        ✓ <?php echo esc($message); ?>
    </div>
<?php endif; ?>

<?php if (empty($pesanan_list)): ?>
    <div class="card">
        <div class="card-body">
            <div class="empty-state" style="padding: 3rem;">
                <div class="empty-state-icon">⭐</div>
                <div class="empty-state-text">Anda belum memiliki pesanan yang bisa dirating</div>
                <div class="empty-state-subtext" style="font-size: 0.9rem; color: #999;">
                    Hanya pesanan yang sudah dikonfirmasi diterima yang bisa dirating
                </div>
                <div class="empty-state-action">
                    <a href="pesanan.php" class="btn btn-primary">
                        📦 Lihat Pesanan
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <?php foreach ($pesanan_list as $pesanan): ?>
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <h3>Pesanan #<?php echo $pesanan['id']; ?> - <?php echo esc($pesanan['nama_warung']); ?></h3>
                <p style="margin: 0.5rem 0 0 0; color: #999; font-size: 0.9rem;">
                    <?php echo formatDateTime($pesanan['waktu_pesan']); ?> • 
                    <?php echo formatCurrency($pesanan['total_harga']); ?>
                </p>
            </div>
            
            <div class="card-body">
                <?php
                // Ambil item dari data yang sudah di-prefetch
                $items = $items_by_order[$pesanan['id']] ?? [];
                ?>
                
                <?php foreach ($items as $item): ?>
                    <?php
                    // Cek rating dari data yang sudah di-prefetch
                    $existing_rating = $ratings_by_item[$pesanan['id'] . '_' . $item['menu_id']] ?? null;
                    ?>
                    
                    <div style="padding: 1rem; border: 1px solid #eee; border-radius: 5px; margin-bottom: 1rem;">
                        <div style="display: flex; gap: 1rem;">
                            <!-- Item Image -->
                            <div style="width: 80px; height: 80px; flex-shrink: 0; background: #f8f9fa; border-radius: 5px; overflow: hidden;">
                                <?php if (!empty($item['gambar']) && file_exists('../assets/uploads/menu/' . $item['gambar'])): ?>
                                    <img src="../assets/uploads/menu/<?php echo esc($item['gambar']); ?>" 
                                         alt="<?php echo esc($item['nama_menu']); ?>"
                                         style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 2rem;">🍜</div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Item Info -->
                            <div style="flex: 1;">
                                <h5 style="margin: 0 0 0.5rem 0;"><?php echo esc($item['nama_menu']); ?></h5>
                                <p style="margin: 0; color: #666; font-size: 0.9rem;">
                                    Qty: <?php echo $item['qty']; ?> • 
                                    Harga: <?php echo formatCurrency($item['harga_satuan']); ?>
                                </p>
                                
                                <?php if ($existing_rating): ?>
                                    <div style="margin-top: 0.5rem;">
                                        <span style="color: #ffc107; font-size: 0.9rem;">
                                            ⭐ Rating: 
                                            <?php for ($i = 0; $i < $existing_rating['rating']; $i++): ?>
                                                ★
                                            <?php endfor; ?>
                                            <?php for ($i = $existing_rating['rating']; $i < 5; $i++): ?>
                                                ☆
                                            <?php endfor; ?>
                                            (<?php echo $existing_rating['rating']; ?>/5)
                                        </span>
                                    </div>
                                    <?php if (!empty($existing_rating['review'])): ?>
                                        <p style="margin: 0.5rem 0 0 0; color: #666; font-size: 0.85rem; font-style: italic;">
                                            "<?php echo esc($existing_rating['review']); ?>"
                                        </p>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <!-- Star Rating Input -->
                                    <div style="margin-top: 1rem;">
                                        <label style="margin: 0; color: #666; font-size: 0.9rem; display: block; margin-bottom: 0.5rem;">
                                            Berikan Rating:
                                        </label>
                                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                                            <?php
                                            $show_form = ($rating_form_order_id == $pesanan['id'] && $rating_form_menu_id == $item['menu_id']);
                                            ?>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" 
                                                    onclick="toggleRatingForm(<?php echo $pesanan['id']; ?>, <?php echo $item['menu_id']; ?>, this)">
                                                📝 Beri Rating
                                            </button>
                                        </div>
                                        
                                        <!-- Rating Form (Hidden by default) -->
                                        <div id="rating_form_<?php echo $pesanan['id']; ?>_<?php echo $item['menu_id']; ?>" 
                                             style="margin-top: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 5px; display: <?php echo $show_form ? 'block' : 'none'; ?>;">
                                            
                                            <form method="POST">
                                                <?php csrf_field(); ?>
                                                <input type="hidden" name="order_id" value="<?php echo $pesanan['id']; ?>">
                                                <input type="hidden" name="menu_id" value="<?php echo $item['menu_id']; ?>">
                                                
                                                <!-- Star Selection -->
                                                <div style="margin-bottom: 1rem;">
                                                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                                                        Rating (1-5 bintang) *
                                                    </label>
                                                    <div style="display: flex; gap: 0.5rem; font-size: 1.5rem;">
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <label style="cursor: pointer;">
                                                                <input type="radio" name="rating" value="<?php echo $i; ?>" required style="display: none;">
                                                                <span class="star" data-value="<?php echo $i; ?>" style="cursor: pointer; color: #ddd; transition: color 0.2s;">
                                                                    ★
                                                                </span>
                                                            </label>
                                                        <?php endfor; ?>
                                                    </div>
                                                </div>
                                                
                                                <!-- Review Textarea -->
                                                <div style="margin-bottom: 1rem;">
                                                    <label style="display: block; margin-bottom: 0.5rem;">
                                                        Review (opsional)
                                                    </label>
                                                    <textarea name="review" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 5px; font-family: inherit;" 
                                                              placeholder="Bagikan pengalaman Anda dengan membeli produk ini..." rows="3"></textarea>
                                                    <p style="margin: 0.5rem 0 0 0; font-size: 0.85rem; color: #999;">Max 500 karakter</p>
                                                </div>
                                                
                                                <!-- Buttons -->
                                                <div style="display: flex; gap: 0.5rem;">
                                                    <button type="submit" name="submit_rating" class="btn btn-primary btn-sm">
                                                        ✓ Simpan Rating
                                                    </button>
                                                    <button type="button" class="btn btn-secondary btn-sm" 
                                                            onclick="toggleRatingForm(<?php echo $pesanan['id']; ?>, <?php echo $item['menu_id']; ?>, this)">
                                                        ✕ Batal
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<script>
function toggleRatingForm(orderId, menuId, button) {
    const formId = `rating_form_${orderId}_${menuId}`;
    const form = document.getElementById(formId);
    if (form) {
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    }
}

// Add star rating interactivity
document.querySelectorAll('input[name="rating"]').forEach(radio => {
    radio.parentElement.addEventListener('click', function() {
        const value = this.querySelector('input').value;
        updateStarDisplay(this.closest('form'), value);
    });
    
    radio.addEventListener('change', function() {
        updateStarDisplay(this.closest('form'), this.value);
    });
});

function updateStarDisplay(form, rating) {
    form.querySelectorAll('.star').forEach((star, index) => {
        if (index < rating) {
            star.style.color = '#ffc107';
        } else {
            star.style.color = '#ddd';
        }
    });
}
</script>

</main>
<?php require_once '../includes/footer.php'; ?>