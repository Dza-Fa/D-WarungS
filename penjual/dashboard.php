<?php
/**
 * Dashboard Penjual - Daftar Menu Warung
 */

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pedagang') {
    header('Location: /D-WarungS/auth/login.php');
    exit();
}

require_once '../config/db.php';
require_once '../config/security.php';

$page_title = 'Dashboard Penjual';

// Get warung milik penjual
$warung = getRow("SELECT * FROM warung WHERE pemilik_id = ?", [$_SESSION['user_id']]);

if (!$warung) {
    // Buat warung default
    $query = "INSERT INTO warung (nama_warung, pemilik_id, deskripsi, jam_buka, jam_tutup) VALUES (?, ?, ?, '06:00:00', '17:00:00')";
    executeQuery($query, [$_SESSION['nama'] . ' Warung', $_SESSION['user_id'], 'Warung makanan berkualitas']);
    $warung = getRow("SELECT * FROM warung WHERE pemilik_id = ?", [$_SESSION['user_id']]);
}

// Handle update warung info
$message = '';
$error = '';

// Tangkap pesan sukses dari redirect (misal: setelah edit/tambah menu)
if (isset($_GET['success'])) {
    $message = $_GET['success'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_warung'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Token keamanan tidak valid. Silakan refresh halaman.';
    } else {
    $nama_warung = trim($_POST['nama_warung'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $nomor_telepon = trim($_POST['nomor_telepon'] ?? '');
    
    if (empty($nama_warung)) {
        $error = 'Nama warung harus diisi!';
    } else {
        $query = "UPDATE warung SET nama_warung = ?, deskripsi = ?, alamat = ?, nomor_telepon = ? WHERE id = ?";
        if (executeUpdate($query, [$nama_warung, $deskripsi, $alamat, $nomor_telepon, $warung['id']])) {
            $warung['nama_warung'] = $nama_warung;
            $warung['deskripsi'] = $deskripsi;
            $warung['alamat'] = $alamat;
            $warung['nomor_telepon'] = $nomor_telepon;
            $message = 'Informasi warung berhasil diperbarui!';
        }
    }
    }
}

// Handle AJAX Updates for Realtime Stats
if (isset($_GET['get_updates'])) {
    if (ob_get_length()) ob_clean();
    
    // 1. Recalculate Menu & Order Stats
    $menu = getRows("SELECT * FROM menu WHERE warung_id = ?", [$warung['id']]);
    
    $total_menu = count($menu);
    $total_stok = 0;
    $total_terjual = 0;
    foreach ($menu as $m) {
        $total_stok += $m['stok'];
        $terjual = getRow("SELECT SUM(qty) as total FROM order_items WHERE menu_id = ?", [$m['id']])['total'] ?? 0;
        $total_terjual += $terjual;
    }

    $pesanan_baru = getRow(
        "SELECT COUNT(DISTINCT o.id) as count FROM orders o 
        JOIN order_items oi ON o.id = oi.order_id 
        JOIN menu m ON oi.menu_id = m.id 
        WHERE m.warung_id = ? AND o.status = 'dibayar'",
        [$warung['id']]
    )['count'];
    
    // 2. Recalculate Rating Stats
    $rating_stats = getRow("SELECT COUNT(r.id) as total_ratings, AVG(r.rating) as avg_rating FROM ratings r JOIN menu m ON r.menu_id = m.id WHERE m.warung_id = ?", [$warung['id']]);
    $total_ratings = $rating_stats['total_ratings'] ?? 0;
    $avg_rating = $rating_stats['avg_rating'] ? round($rating_stats['avg_rating'], 1) : 0;
    
    // 3. Generate Menu Table HTML
    $menu_list = getRows("SELECT * FROM menu WHERE warung_id = ? ORDER BY nama_menu ASC", [$warung['id']]);
    ob_start();
    foreach ($menu_list as $m) {
        $terjual = getRow("SELECT SUM(qty) as total FROM order_items WHERE menu_id = ?", [$m['id']])['total'] ?? 0;
        echo '<tr>';
        echo '<td>
                <strong>' . esc($m['nama_menu']) . '</strong><br>
                <small style="color: #999;">' . esc(substr($m['deskripsi'] ?? '', 0, 50)) . '</small>
              </td>';
        echo '<td style="text-align: right;">' . formatCurrency($m['harga']) . '</td>';
        echo '<td style="text-align: center;">' . $m['stok'] . '</td>';
        echo '<td style="text-align: center;">' . $terjual . '</td>';
        echo '<td style="text-align: center;">
                <div class="btn-group" style="justify-content: center;">
                    <a href="edit_menu.php?id=' . $m['id'] . '" class="btn btn-info btn-sm">✏️ Edit</a>
                    <form method="POST" action="hapus_menu.php" style="display:inline;" onsubmit="return confirm(\'Hapus menu ini?\');">
                        <input type="hidden" name="csrf_token" value="' . generate_csrf_token() . '">
                        <input type="hidden" name="id" value="' . $m['id'] . '">
                        <button type="submit" class="btn btn-danger btn-sm">🗑️ Hapus</button>
                    </form>
                </div>
              </td>';
        echo '</tr>';
    }
    $menu_html = ob_get_clean();
    
    header('Content-Type: application/json');
    echo json_encode([
        'total_menu' => $total_menu,
        'total_stok' => $total_stok,
        'pesanan_baru' => $pesanan_baru,
        'total_terjual' => $total_terjual,
        'avg_rating' => $avg_rating,
        'total_ratings' => $total_ratings,
        'menu_html' => $menu_html
    ]);
    exit;
}

// Get menu list
$menu = getRows("SELECT * FROM menu WHERE warung_id = ? ORDER BY nama_menu ASC", [$warung['id']]);

// Get statistics
$total_menu = count($menu);
$total_stok = 0;
$total_terjual = 0;
foreach ($menu as $m) {
    $total_stok += $m['stok'];
    $terjual = getRow("SELECT SUM(qty) as total FROM order_items WHERE menu_id = ?", [$m['id']])['total'] ?? 0;
    $total_terjual += $terjual;
}

$pesanan_baru = getRow(
    "SELECT COUNT(DISTINCT o.id) as count FROM orders o 
    JOIN order_items oi ON o.id = oi.order_id 
    JOIN menu m ON oi.menu_id = m.id 
    WHERE m.warung_id = ? AND o.status = 'dibayar'",
    [$warung['id']]
)['count'];

// Get rating statistics
$rating_stats = getRow(
    "SELECT 
        COUNT(r.id) as total_ratings,
        AVG(r.rating) as avg_rating
    FROM ratings r
    JOIN menu m ON r.menu_id = m.id
    WHERE m.warung_id = ?",
    [$warung['id']]
);
$total_ratings = $rating_stats['total_ratings'] ?? 0;
$avg_rating = $rating_stats['avg_rating'] ? round($rating_stats['avg_rating'], 1) : 0;

// Get top rated menu
$top_rated = getRows(
    "SELECT m.id, m.nama_menu, AVG(r.rating) as avg_rating, COUNT(r.id) as rating_count
    FROM menu m
    LEFT JOIN ratings r ON m.id = r.menu_id
    WHERE m.warung_id = ?
    GROUP BY m.id, m.nama_menu
    ORDER BY avg_rating DESC, rating_count DESC
    LIMIT 3",
    [$warung['id']]
);
?>
<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/sidebar.php'; ?>

<div class="page-header">
    <h1 class="page-title">🏪 Dashboard Penjual</h1>
    <p class="page-subtitle">Kelola warung dan menu Anda</p>
</div>

<?php if ($message): ?>
    <div class="alert alert-success">
        ✓ <?php echo esc($message); ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger">
        ✗ <?php echo esc($error); ?>
    </div>
<?php endif; ?>

<!-- Statistics -->
<div class="grid grid-4" style="margin-bottom: 2rem;">
    <div class="card">
        <div style="text-align: center; padding: 1rem;">
            <div id="stat-total-menu" style="font-size: 2rem; font-weight: 700; color: #667eea;">
                <?php echo $total_menu; ?>
            </div>
            <div style="color: #666; margin-top: 0.5rem;">Total Menu</div>
        </div>
    </div>
    
    <div class="card">
        <div style="text-align: center; padding: 1rem;">
            <div id="stat-total-stok" style="font-size: 2rem; font-weight: 700; color: #764ba2;">
                <?php echo $total_stok; ?>
            </div>
            <div style="color: #666; margin-top: 0.5rem;">Total Stok</div>
        </div>
    </div>
    
    <div class="card">
        <div style="text-align: center; padding: 1rem;">
            <div id="stat-pesanan-baru" style="font-size: 2rem; font-weight: 700; color: #f39c12;">
                <?php echo $pesanan_baru; ?>
            </div>
            <div style="color: #666; margin-top: 0.5rem;">Pesanan Baru</div>
        </div>
    </div>
    
    <div class="card">
        <div style="text-align: center; padding: 1rem;">
            <div id="stat-total-terjual" style="font-size: 2rem; font-weight: 700; color: #27ae60;">
                <?php echo $total_terjual; ?>
            </div>
            <div style="color: #666; margin-top: 0.5rem;">Total Terjual</div>
        </div>
    </div>
</div>

<!-- Rating Statistics Section -->
<?php if ($total_ratings > 0): ?>
<div class="card" style="margin-bottom: 2rem; background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); color: white;">
    <div class="card-header">
        <h3 style="color: #666; margin: 0;">⭐ Rating & Review dari Pembeli</h3>
    </div>
    
    <div class="card-body">
        <div class="grid grid-3" style="gap: 1rem; margin-bottom: 1.5rem;">
            <div style="text-align: center;">
                <div id="stat-avg-rating" style="font-size: 2.5rem; font-weight: 700; margin-bottom: 0.5rem;">
                    <?php echo $avg_rating; ?>/5
                </div>
                <div style="font-size: 0.9rem; opacity: 0.95;">Rating Rata-rata</div>
            </div>
            
            <div style="text-align: center;">
                <div id="stat-total-ratings" style="font-size: 2.5rem; font-weight: 700; margin-bottom: 0.5rem;">
                    <?php echo $total_ratings; ?>
                </div>
                <div style="font-size: 0.9rem; opacity: 0.95;">Total Rating</div>
            </div>
            
            <div style="text-align: center;">
                <div style="font-size: 2.5rem; font-weight: 700; margin-bottom: 0.5rem;">
                    <?php echo count($top_rated); ?>
                </div>
                <div style="font-size: 0.9rem; opacity: 0.95;">Menu Dinilai</div>
            </div>
        </div>
        
        <?php if (!empty($top_rated)): ?>
        <div style="background: rgba(255,255,255,0.15); padding: 1rem; border-radius: 5px;">
            <h4 style="margin: 0 0 1rem 0; color: white;">🏆 Menu Terlaris</h4>
            <ul style="margin: 0; padding-left: 1.5rem;">
                <?php foreach ($top_rated as $menu_item): ?>
                    <li style="margin-bottom: 0.75rem; color: white;">
                        <strong><?php echo esc($menu_item['nama_menu']); ?></strong>
                        <span style="opacity: 0.9;">
                            <?php
                            if ($menu_item['avg_rating']) {
                                for ($i = 0; $i < floor($menu_item['avg_rating']); $i++) {
                                    echo '★';
                                }
                                if ($menu_item['avg_rating'] - floor($menu_item['avg_rating']) >= 0.5) {
                                    echo '½';
                                }
                                echo ' (' . round($menu_item['avg_rating'], 1) . '/5 • ' . intval($menu_item['rating_count']) . ' rating)';
                            } else {
                                echo '(Belum ada rating)';
                            }
                            ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- Warung Info -->
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header">
        <h3>Informasi Warung</h3>
    </div>
    
    <form method="POST" class="card-body">
        <?php csrf_field(); ?>
        <div class="form-group">
            <label for="nama_warung">Nama Warung</label>
            <input type="text" id="nama_warung" name="nama_warung" value="<?php echo esc($warung['nama_warung']); ?>" required>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="alamat">Alamat</label>
                <textarea id="alamat" name="alamat"><?php echo esc($warung['alamat'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="nomor_telepon">Nomor Telepon</label>
                <input type="text" id="nomor_telepon" name="nomor_telepon" value="<?php echo esc($warung['nomor_telepon'] ?? ''); ?>">
            </div>
        </div>
        
        <div class="form-group">
            <label for="deskripsi">Deskripsi</label>
            <textarea id="deskripsi" name="deskripsi"><?php echo esc($warung['deskripsi'] ?? ''); ?></textarea>
        </div>
        
        <div class="card-footer">
            <button type="submit" name="update_warung" class="btn btn-primary">
                💾 Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<!-- Menu List -->
<div class="card">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem;">
            <h3>Daftar Menu</h3>
            <a href="tambah_menu.php" class="btn btn-primary btn-sm">
                ➕ Tambah Menu
            </a>
        </div>
    </div>
    
    <?php if (empty($menu)): ?>
        <div class="card-body">
            <div class="empty-state" style="padding: 2rem;">
                <div class="empty-state-icon">🍽️</div>
                <div class="empty-state-text">Belum ada menu di warung Anda</div>
                <div class="empty-state-action">
                    <a href="tambah_menu.php" class="btn btn-primary">
                        ➕ Tambah Menu Pertama
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="card-body" style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Menu</th>
                        <th style="text-align: right;">Harga</th>
                        <th style="text-align: center;">Stok</th>
                        <th style="text-align: center;">Terjual</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="menu-table-body">
                    <?php foreach ($menu as $m): ?>
                        <?php
                        $terjual = getRow("SELECT SUM(qty) as total FROM order_items WHERE menu_id = ?", [$m['id']])['total'] ?? 0;
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo esc($m['nama_menu']); ?></strong><br>
                                <small style="color: #999;">
                                    <?php echo esc(substr($m['deskripsi'] ?? '', 0, 50)); ?>
                                </small>
                            </td>
                            <td style="text-align: right;">
                                <?php echo formatCurrency($m['harga']); ?>
                            </td>
                            <td style="text-align: center;">
                                <?php echo $m['stok']; ?>
                            </td>
                            <td style="text-align: center;">
                                <?php echo $terjual; ?>
                            </td>
                            <td style="text-align: center;">
                                <div class="btn-group" style="justify-content: center;">
                                    <a href="edit_menu.php?id=<?php echo $m['id']; ?>" class="btn btn-info btn-sm">
                                        ✏️ Edit
                                    </a>
                                    <form method="POST" action="hapus_menu.php" style="display:inline;" onsubmit="return confirm('Hapus menu ini?');">
                                        <?php csrf_field(); ?>
                                        <input type="hidden" name="id" value="<?php echo $m['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">🗑️ Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

</main>

<script src="/D-WarungS/assets/js/realtime-notifications.js?v=<?php echo time(); ?>"></script>
<script>
    // Inisialisasi realtime untuk penjual dashboard
    document.addEventListener('DOMContentLoaded', function() {
        // Request permission untuk browser notifications
        RealtimeNotifications.requestNotificationPermission();
        
        const realtime = new RealtimeNotifications({
            endpoint: '/D-WarungS/api/realtime-notifications.php',
            reconnectInterval: 2000,
            onNotification: function(notif) {
                console.log('📩 Pesanan baru dari pembeli:', notif);
                
                // Untuk penjual, flash notifikasi kedatangan pesanan baru
                if (notif.type === 'order' || notif.pesanan_id) {
                    showPesananMasuk(notif);
                }
                
                // Update statistik dashboard secara realtime (untuk order atau review baru)
                if (notif.type === 'order' || notif.pesanan_id || notif.type === 'review') {
                    fetch('dashboard.php?get_updates=1')
                        .then(response => response.json())
                        .then(data => {
                            document.getElementById('stat-total-menu').innerText = data.total_menu;
                            document.getElementById('stat-total-stok').innerText = data.total_stok;
                            document.getElementById('stat-pesanan-baru').innerText = data.pesanan_baru;
                            document.getElementById('stat-total-terjual').innerText = data.total_terjual;
                            document.getElementById('stat-avg-rating').innerText = data.avg_rating + '/5';
                            document.getElementById('stat-total-ratings').innerText = data.total_ratings;
                            document.getElementById('menu-table-body').innerHTML = data.menu_html;
                        })
                        .catch(err => console.error('Gagal update stats:', err));
                }
            }
        });
        
        realtime.connect();
        
        window.addEventListener('beforeunload', function() {
            realtime.disconnect();
        });
        
        function showPesananMasuk(notif) {
            const notifEl = document.createElement('div');
            notifEl.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
                color: white;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 8px 32px rgba(0,0,0,0.2);
                z-index: 9999;
                max-width: 350px;
                animation: slideIn 0.3s ease-out;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto;
                cursor: pointer;
            `;
            
            notifEl.innerHTML = `
                <div style="font-weight: 600; font-size: 16px; margin-bottom: 8px;">🎉 Pesanan Masuk!</div>
                <div style="font-size: 14px; opacity: 0.9;">ID Pesanan: #${notif.pesanan_id}</div>
                <div style="font-size: 13px; opacity: 0.85; margin-top: 4px;">Total: Rp ${(notif.total_harga || 0).toLocaleString('id-ID')}</div>
                <div style="font-size: 12px; opacity: 0.8; margin-top: 8px; text-decoration: underline;">Klik untuk lihat pesanan →</div>
            `;
            
            notifEl.onclick = function() {
                window.location.href = '/D-WarungS/penjual/pesanan.php';
            };
            
            document.body.appendChild(notifEl);
            
            // Auto hide + flash effect
            setTimeout(() => {
                notifEl.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => notifEl.remove(), 300);
            }, 8000);
        }
    });
</script>

<style>
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
</style>

<?php require_once '../includes/footer.php'; ?>
