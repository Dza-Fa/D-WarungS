<?php
/**
 * Halaman Notifikasi Pembeli
 */

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pembeli') {
    header('Location: /D-WarungS/auth/login.php');
    exit();
}

require_once '../config/db.php';

$page_title = 'Notifikasi';

// Handle mark as read
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_read'])) {
    $notification_id = intval($_POST['notification_id']);
    $query = "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?";
    execute($query, [$notification_id, $_SESSION['user_id']]);
    
    header('Location: notifikasi.php');
    exit();
}

// Handle mark all as read
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_all_read'])) {
    $query = "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0";
    execute($query, [$_SESSION['user_id']]);
    
    header('Location: notifikasi.php');
    exit();
}

// Get filter
$filter = isset($_GET['filter']) ? trim($_GET['filter']) : 'all';
$where = "user_id = ? AND role = 'pembeli'";
$params = [$_SESSION['user_id']];

if ($filter === 'unread') {
    $where .= " AND is_read = 0";
}

// Get notifikasi
$query = "SELECT * FROM notifications WHERE $where ORDER BY created_at DESC LIMIT 100";
$notifikasi = getRows($query, $params);

// Get unread count
$unread_count = getRow("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0 AND role = 'pembeli'", [$_SESSION['user_id']])['count'] ?? 0;
?>
<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/sidebar.php'; ?>

<div class="page-header">
    <h1 class="page-title">🔔 Notifikasi</h1>
    <p class="page-subtitle">Informasi pesanan dan update penting</p>
</div>

<?php if ($unread_count > 0): ?>
    <div style="margin-bottom: 1.5rem; padding: 1rem; background: #cfe2ff; border-left: 4px solid #0d6efd; border-radius: 5px; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <strong style="color: #084298;">Anda memiliki <?php echo $unread_count; ?> notifikasi baru</strong>
        </div>
        <form method="POST" style="display: inline;">
            <button type="submit" name="mark_all_read" class="btn btn-sm btn-primary">
                ✓ Tandai Semua Sudah Dibaca
            </button>
        </form>
    </div>
<?php endif; ?>

<!-- Filter Tabs -->
<div style="margin-bottom: 1.5rem; display: flex; gap: 0.5rem;">
    <a href="notifikasi.php" class="btn <?php echo $filter === 'all' ? 'btn-primary' : 'btn-outline-secondary'; ?> btn-sm">
        📊 Semua (<?php echo count(getRows("SELECT id FROM notifications WHERE user_id = ?", [$_SESSION['user_id']])); ?>)
    </a>
    <a href="notifikasi.php?filter=unread" class="btn <?php echo $filter === 'unread' ? 'btn-primary' : 'btn-outline-secondary'; ?> btn-sm">
        💬 Belum Dibaca (<?php echo $unread_count; ?>)
    </a>
</div>

<?php if (empty($notifikasi)): ?>
    <div class="card">
        <div class="card-body">
            <div class="empty-state" style="padding: 3rem;">
                <div class="empty-state-icon">🔔</div>
                <div class="empty-state-text">Tidak ada notifikasi</div>
                <div class="empty-state-subtext" style="font-size: 0.9rem; color: #999;">
                    Notifikasi pesanan akan muncul di sini saat ada update
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <?php foreach ($notifikasi as $notif): ?>
        <div class="card" style="margin-bottom: 1rem; border-left: 4px solid <?php echo !$notif['is_read'] ? '#0d6efd' : '#e0e0e0'; ?>; background: <?php echo !$notif['is_read'] ? '#f0f7ff' : 'white'; ?>;">
            <div class="card-body" style="display: flex; justify-content: space-between; align-items: start;">
                <div style="flex: 1;">
                    <div style="display: flex; gap: 0.75rem; align-items: start;">
                        <div style="font-size: 1.5rem; margin-top: 0.25rem;">
                            <?php
                            if (strpos($notif['type'], 'order') !== false) {
                                echo '📦';
                            } elseif (strpos($notif['type'], 'review') !== false) {
                                echo '⭐';
                            } else {
                                echo '🔔';
                            }
                            ?>
                        </div>
                        <div style="flex: 1;">
                            <p style="margin: 0; font-weight: 600; color: #333;">
                                <?php echo esc($notif['message']); ?>
                            </p>
                            <p style="margin: 0.5rem 0 0 0; font-size: 0.85rem; color: #999;">
                                <?php echo formatDateTime($notif['created_at']); ?>
                            </p>
                            <?php if (!empty($notif['order_id'])): ?>
                                <a href="pesanan.php?pesanan_id=<?php echo $notif['order_id']; ?>" class="btn btn-sm btn-outline-primary" style="margin-top: 0.5rem;">
                                    👁️ Lihat Pesanan
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <?php if (!$notif['is_read']): ?>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="notification_id" value="<?php echo $notif['id']; ?>">
                        <button type="submit" name="mark_read" class="btn btn-sm btn-outline-secondary" style="white-space: nowrap;">
                            ✓ Tandai Dibaca
                        </button>
                    </form>
                <?php else: ?>
                    <span style="font-size: 0.8rem; color: #999; white-space: nowrap;">✓ Sudah Dibaca</span>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

</main>
<?php require_once '../includes/footer.php'; ?>
