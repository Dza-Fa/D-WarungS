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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_warung'])) {
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
            <div style="font-size: 2rem; font-weight: 700; color: #667eea;">
                <?php echo $total_menu; ?>
            </div>
            <div style="color: #666; margin-top: 0.5rem;">Total Menu</div>
        </div>
    </div>
    
    <div class="card">
        <div style="text-align: center; padding: 1rem;">
            <div style="font-size: 2rem; font-weight: 700; color: #764ba2;">
                <?php echo $total_stok; ?>
            </div>
            <div style="color: #666; margin-top: 0.5rem;">Total Stok</div>
        </div>
    </div>
    
    <div class="card">
        <div style="text-align: center; padding: 1rem;">
            <div style="font-size: 2rem; font-weight: 700; color: #f39c12;">
                <?php echo $pesanan_baru; ?>
            </div>
            <div style="color: #666; margin-top: 0.5rem;">Pesanan Baru</div>
        </div>
    </div>
    
    <div class="card">
        <div style="text-align: center; padding: 1rem;">
            <div style="font-size: 2rem; font-weight: 700; color: #27ae60;">
                <?php echo $total_terjual; ?>
            </div>
            <div style="color: #666; margin-top: 0.5rem;">Total Terjual</div>
        </div>
    </div>
</div>

<!-- Warung Info -->
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header">
        <h3>Informasi Warung</h3>
    </div>
    
    <form method="POST" class="card-body">
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
                <tbody>
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
                                    <a href="hapus_menu.php?id=<?php echo $m['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus menu ini?')">
                                        🗑️ Hapus
                                    </a>
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
<?php require_once '../includes/footer.php'; ?>
