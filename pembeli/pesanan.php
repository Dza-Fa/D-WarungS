<?php
/**
 * Halaman Pesanan Pembeli
 */

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pembeli') {
    header('Location: /D-WarungS/auth/login.php');
    exit();
}

require_once '../config/db.php';
require_once '../config/security.php';

$page_title = 'Pesanan Aktif';

// Handle mark notification as read (AJAX)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_read'])) {
    $notification_id = intval($_POST['notification_id'] ?? 0);
    if ($notification_id) {
        execute("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?", [$notification_id, $_SESSION['user_id']]);
    }
    exit;
}

// Handle pesanan confirmation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_pesanan'])) {
    if (validate_csrf_token($_POST['csrf_token'] ?? '')) {
    $pesanan_id = intval($_POST['pesanan_id']);
    $pesanan = getRow("SELECT * FROM orders WHERE id = ? AND pembeli_id = ?", [$pesanan_id, $_SESSION['user_id']]);
    
    if ($pesanan && $pesanan['status'] == 'siap') {
        $query = "UPDATE orders SET is_confirmed = 1 WHERE id = ?";
        if (execute($query, [$pesanan_id])) {
            // Kirim notifikasi ke penjual bahwa pesanan telah diterima
            $sellers = getRows("
                SELECT DISTINCT w.pemilik_id 
                FROM order_items oi
                JOIN menu m ON oi.menu_id = m.id
                JOIN warung w ON m.warung_id = w.id
                WHERE oi.order_id = ?
            ", [$pesanan_id]);

            foreach ($sellers as $seller) {
                $msg_seller = "Pesanan #$pesanan_id telah diterima oleh pembeli. Transaksi selesai.";
                execute("INSERT INTO notifications (user_id, order_id, type, message, role, is_read) VALUES (?, ?, 'order_update', ?, 'pedagang', 0)", 
                    [$seller['pemilik_id'], $pesanan_id, $msg_seller]);
            }
            
            header('Location: pesanan.php?pesanan_id=' . $pesanan_id . '&confirmed=1');
            exit();
        }
    }
    }
}

// Handle pesanan cancellation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel_pesanan'])) {
    if (validate_csrf_token($_POST['csrf_token'] ?? '')) {
    $pesanan_id = intval($_POST['pesanan_id']);
    $pesanan = getRow("SELECT * FROM orders WHERE id = ? AND pembeli_id = ?", [$pesanan_id, $_SESSION['user_id']]);
    
    // Can only cancel if status is 'menunggu' or 'dibayar'
    if ($pesanan && in_array($pesanan['status'], ['menunggu', 'dibayar'])) {
        $query = "UPDATE orders SET status = 'batal' WHERE id = ?";
        if (execute($query, [$pesanan_id])) {
            // Kembalikan stok
            $items = getRows("SELECT menu_id, qty FROM order_items WHERE order_id = ?", [$pesanan_id]);
            foreach ($items as $item) {
                execute("UPDATE menu SET stok = stok + ? WHERE id = ?", [$item['qty'], $item['menu_id']]);
            }
            
            // Kirim notifikasi ke semua kasir bahwa pesanan dibatalkan
            $kasirs = getRows("SELECT id FROM users WHERE role = 'kasir'");
            foreach ($kasirs as $k) {
                execute("INSERT INTO notifications (user_id, order_id, type, message, role, is_read) VALUES (?, ?, 'order_cancel', 'Pesanan dibatalkan oleh pembeli', 'kasir', 0)", [$k['id'], $pesanan_id]);
            }

            // Kirim notifikasi ke pedagang jika status sebelumnya 'dibayar' (karena sudah muncul di dashboard mereka)
            if ($pesanan['status'] == 'dibayar') {
                $sellers = getRows("SELECT DISTINCT w.pemilik_id FROM order_items oi JOIN menu m ON oi.menu_id = m.id JOIN warung w ON m.warung_id = w.id WHERE oi.order_id = ?", [$pesanan_id]);

                foreach ($sellers as $seller) {
                    execute("INSERT INTO notifications (user_id, order_id, type, message, role, is_read) VALUES (?, ?, 'order_cancel', 'Pesanan dibatalkan oleh pembeli', 'pedagang', 0)", 
                        [$seller['pemilik_id'], $pesanan_id]);
                }
            }
            
            header('Location: pesanan.php?pesanan_id=' . $pesanan_id . '&cancelled=1');
            exit();
        }
    }
    }
}

// Get pesanan detail jika ada pesanan_id (bisa confirmed atau belum)
$pesanan_detail = null;
if (isset($_GET['pesanan_id'])) {
    $pesanan_id = intval($_GET['pesanan_id']);
    $pesanan_detail = getRow("SELECT * FROM orders WHERE id = ? AND pembeli_id = ?", [$pesanan_id, $_SESSION['user_id']]);
}

// Get pesanan yang MASIH BERLANGSUNG (belum dikonfirmasi dan tidak dibatalkan)
$query = "SELECT * FROM orders WHERE pembeli_id = ? AND is_confirmed = 0 AND status != 'batal' ORDER BY waktu_pesan DESC";
$pesanan = getRows($query, [$_SESSION['user_id']]);

// Get status stats HANYA untuk pesanan yang belum dikonfirmasi
$status_stats = [];
$status_list = ['menunggu', 'dibayar', 'diproses', 'siap'];
foreach ($status_list as $status) {
    $count = getRow("SELECT COUNT(*) as count FROM orders WHERE pembeli_id = ? AND status = ? AND is_confirmed = 0", [$_SESSION['user_id'], $status])['count'];
    if ($count > 0) {
        $status_stats[$status] = $count;
    }
}
?>
<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/sidebar.php'; ?>

<div class="page-header">
    <h1 class="page-title">📦 Pesanan Aktif</h1>
    <p class="page-subtitle">Pesanan yang sedang berlangsung</p>
</div>

<?php if (isset($_GET['confirmed']) && $_GET['confirmed'] == '1'): ?>
    <div class="alert alert-success" style="background-color: #c6f6d5; color: #22543d; padding: 1rem; margin-bottom: 1.5rem;">
        ✓ Pesanan berhasil dikonfirmasi! Data akan diperbarui dalam sistem.
    </div>
<?php endif; ?>

<?php if (isset($_GET['cancelled']) && $_GET['cancelled'] == '1'): ?>
    <div class="alert alert-warning" style="background-color: #feebc8; color: #744210; padding: 1rem; margin-bottom: 1.5rem;">
        ✓ Pesanan berhasil dibatalkan. Pesanan ini tidak akan diproses lebih lanjut.
    </div>
<?php endif; ?>

<?php if ($pesanan_detail && isset($_GET['pesanan_id'])): ?>
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-header">
            <h3>Detail Pesanan #<?php echo $pesanan_detail['id']; ?></h3>
        </div>
        
        <div class="card-body">
            <div class="form-row">
                <div>
                    <strong style="color: #666;">Nomor Pesanan:</strong><br>
                    #<?php echo $pesanan_detail['id']; ?>
                </div>
                <div>
                    <strong style="color: #666;">Status:</strong><br>
                    <span class="status status-<?php echo $pesanan_detail['status']; ?>">
                        <?php echo ucfirst($pesanan_detail['status']); ?>
                    </span>
                </div>
                <div>
                    <strong style="color: #666;">Waktu Pesan:</strong><br>
                    <?php echo formatDateTime($pesanan_detail['waktu_pesan']); ?>
                </div>
                <div>
                    <strong style="color: #666;">Total Harga:</strong><br>
                    <span style="font-size: 1.2rem; color: #667eea; font-weight: 600;">
                        <?php echo formatCurrency($pesanan_detail['total_harga']); ?>
                    </span>
                </div>
            </div>
            
            <?php if ($pesanan_detail['is_confirmed'] == 1): ?>
                <div style="margin-top: 1.5rem; padding: 1rem; background: #d4edda; border-left: 4px solid #28a745; border-radius: 5px;">
                    <strong style="color: #155724;">✓ Pesanan Sudah Dikonfirmasi Diterima</strong><br>
                    <span style="color: #155724; font-size: 0.9rem;">Terima kasih telah mengkonfirmasi pesanan Anda.</span>
                </div>
            <?php elseif ($pesanan_detail['status'] == 'siap'): ?>
                <div style="margin-top: 1.5rem; padding: 1rem; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 5px;">
                    <strong style="color: #856404;">⚠️ Pesanan Siap Diambil</strong><br>
                    <span style="color: #856404; font-size: 0.9rem;">Pesanan Anda sudah siap. Silakan ambil dan konfirmasi penerimaan.</span>
                </div>
            <?php endif; ?>
            
            <div style="margin-top: 2rem;">
                <h4 style="margin-bottom: 1rem;">Item Pesanan:</h4>
                <?php
                $items = getRows("SELECT oi.*, m.nama_menu FROM order_items oi JOIN menu m ON oi.menu_id = m.id WHERE oi.order_id = ?", [$pesanan_detail['id']]);
                ?>
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Menu</th>
                            <th style="text-align: right;">Harga</th>
                            <th style="text-align: center;">Qty</th>
                            <th style="text-align: right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?php echo esc($item['nama_menu']); ?></td>
                                <td style="text-align: right;"><?php echo formatCurrency($item['harga_satuan']); ?></td>
                                <td style="text-align: center;"><?php echo $item['qty']; ?></td>
                                <td style="text-align: right;"><?php echo formatCurrency($item['subtotal']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card-footer">
            <a href="<?php echo $pesanan_detail['is_confirmed'] ? 'pesanan_selesai.php' : 'pesanan.php'; ?>" class="btn btn-secondary">
                ← <?php echo $pesanan_detail['is_confirmed'] ? 'Kembali ke Riwayat' : 'Kembali'; ?>
            </a>
            
            <?php if ($pesanan_detail['is_confirmed'] == 1): ?>
                <a href="rating.php" class="btn btn-warning">
                    ⭐ Beri Rating & Review
                </a>
            <?php elseif ($pesanan_detail['status'] == 'siap' && !$pesanan_detail['is_confirmed']): ?>
                <form method="POST" style="display: inline;">
                    <?php csrf_field(); ?>
                    <input type="hidden" name="pesanan_id" value="<?php echo $pesanan_detail['id']; ?>">
                    <button type="submit" name="confirm_pesanan" class="btn btn-success">
                        ✓ Konfirmasi Pesanan Diterima
                    </button>
                </form>
            <?php elseif (in_array($pesanan_detail['status'], ['menunggu', 'dibayar']) && !$pesanan_detail['is_confirmed']): ?>
                <form method="POST" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?');";>
                    <?php csrf_field(); ?>
                    <input type="hidden" name="pesanan_id" value="<?php echo $pesanan_detail['id']; ?>">
                    <button type="submit" name="cancel_pesanan" class="btn btn-danger">
                        ✕ Batalkan Pesanan
                    </button>
                </form>
            <?php elseif ($pesanan_detail['status'] == 'batal'): ?>
                <div style="padding: 1rem; background: #f8d7da; border-left: 4px solid #dc3545; border-radius: 5px; display: inline-block;">
                    <strong style="color: #721c24;">✕ Pesanan Dibatalkan</strong><br>
                    <span style="color: #721c24; font-size: 0.9rem;">Pesanan ini telah dibatalkan dan tidak akan diproses.</span>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Status Stats -->
<?php if (!empty($status_stats)): ?>
    <div class="grid grid-4" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <?php foreach ($status_stats as $status => $count): ?>
            <div class="card">
                <div style="text-align: center; padding: 1rem;">
                    <div style="font-size: 2.5rem; font-weight: 700; color: #667eea; line-height: 1;">
                        <?php echo $count; ?>
                    </div>
                    <div style="color: #718096; margin-top: 0.5rem; text-transform: capitalize; font-weight: 500;">
                        <?php echo $status; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Pesanan List -->
<div class="card">
    <div class="card-header">
        <h3>Daftar Pesanan</h3>
    </div>
    
    <?php if (empty($pesanan)): ?>
        <div class="card-body">
            <div class="empty-state" style="padding: 2rem;">
                <div class="empty-state-icon">📦</div>
                <div class="empty-state-text">Tidak ada pesanan yang sedang berlangsung</div>
                <div class="empty-state-subtext" style="font-size: 0.9rem; color: #999; margin-bottom: 1rem;">
                    Semua pesanan Anda yang sudah dikonfirmasi diterima dapat dilihat di halaman <strong>Pesanan Selesai</strong>
                </div>
                <div class="empty-state-action">
                    <a href="dashboard.php" class="btn btn-primary">
                        🛒 Mulai Pesan
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="card-body" style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 15%;">No. Pesanan</th>
                        <th style="width: 25%;">Waktu</th>
                        <th style="width: 20%;">Status</th>
                        <th style="text-align: right;">Total</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pesanan as $p): ?>
                        <tr>
                            <td><span style="font-family: monospace; font-weight: 700; color: #4a5568;">#<?php echo $p['id']; ?></span></td>
                            <td style="color: #718096;"><?php echo formatDateTime($p['waktu_pesan']); ?></td>
                            <td>
                                <span class="status status-<?php echo $p['status']; ?>" style="padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.85rem; font-weight: 600; text-transform: capitalize; background: #edf2f7; color: #4a5568;">
                                    <?php echo ucfirst($p['status']); ?>
                                </span>
                            </td>
                            <td style="text-align: right; font-weight: 600;">
                                <?php echo formatCurrency($p['total_harga']); ?>
                            </td>
                            <td style="text-align: center; display: flex; gap: 0.25rem; justify-content: center;">
                                <a href="pesanan.php?pesanan_id=<?php echo $p['id']; ?>" class="btn btn-info btn-sm">
                                    👁️ Lihat
                                </a>
                                <?php if (in_array($p['status'], ['menunggu', 'dibayar'])): ?>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Batalkan pesanan ini?');";>
                                        <?php csrf_field(); ?>
                                        <input type="hidden" name="pesanan_id" value="<?php echo $p['id']; ?>">
                                        <button type="submit" name="cancel_pesanan" class="btn btn-danger btn-sm" title="Batalkan pesanan">
                                            ✕
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])): ?>
            <div class="card-footer" style="text-align: center;">
                <a href="keranjang.php" class="btn btn-primary btn-lg">
                    🛒 Lihat Keranjang (<?php echo count($_SESSION['cart']); ?> item)
                </a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script src="/D-WarungS/assets/js/realtime-notifications.js?v=<?php echo time(); ?>"></script>
<script>
    // Inisialisasi realtime notifications untuk pembeli
    document.addEventListener('DOMContentLoaded', function() {
        // Request permission untuk browser notifications
        RealtimeNotifications.requestNotificationPermission();
        
        // Flag untuk mencegah multiple reload
        let isReloading = false;

        // Buat instance realtime
        const realtime = new RealtimeNotifications({
            endpoint: '/D-WarungS/api/realtime-notifications.php',
            reconnectInterval: 3000,
            onNotification: function(notif) {
                console.log('📩 Notifikasi realtime:', notif);
                
                // Untuk pembeli, refresh halaman ketika status berubah
                if (!isReloading && notif.status && ['dibayar', 'diproses', 'siap', 'selesai', 'batal'].includes(notif.status)) {
                    console.log('Status pesanan #' + notif.pesanan_id + ' berubah ke: ' + notif.status);
                    isReloading = true;
                    
                    // Fix Spam Refresh: Tandai notifikasi sebagai sudah dibaca DULU sebelum reload
                    // agar setelah reload API tidak mengirim notifikasi yang sama lagi
                    fetch('pesanan.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'mark_read=1&notification_id=' + notif.id
                    }).then(() => {
                        // Force reload from server, ignoring cache
                        window.location.reload(true);
                    }).catch(() => {
                        window.location.reload(true);
                    });
                }
            },
            onError: function(error) {
                console.error('❌ Realtime error:', error);
            }
        });
        
        // Connect ke realtime notifications
        realtime.connect();
        
        // Cleanup saat keluar halaman
        window.addEventListener('beforeunload', function() {
            realtime.disconnect();
        });
    });
</script>

</main>
<?php require_once '../includes/footer.php'; ?>
