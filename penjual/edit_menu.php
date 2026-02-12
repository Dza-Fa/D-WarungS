<?php
/**
 * Edit Menu Warung
 */

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pedagang') {
    header('Location: /D-WarungS/auth/login.php');
    exit();
}

require_once '../config/db.php';

$page_title = 'Edit Menu';

// Get menu_id dari URL
$menu_id = intval($_GET['id'] ?? 0);

if (!$menu_id) {
    header('Location: dashboard.php');
    exit();
}

// Get warung milik penjual
$warung = getRow("SELECT * FROM warung WHERE pemilik_id = ?", [$_SESSION['user_id']]);

if (!$warung) {
    header('Location: dashboard.php');
    exit();
}

// Get menu
$menu = getRow("SELECT * FROM menu WHERE id = ? AND warung_id = ?", [$menu_id, $warung['id']]);

if (!$menu) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_menu'])) {
    $nama_menu = trim($_POST['nama_menu'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $harga = intval($_POST['harga'] ?? 0);
    $stok = intval($_POST['stok'] ?? 0);
    
    // Validasi
    if (empty($nama_menu)) {
        $error = 'Nama menu harus diisi!';
    } elseif ($harga <= 0) {
        $error = 'Harga harus lebih dari 0!';
    } elseif ($stok < 0) {
        $error = 'Stok tidak boleh negatif!';
    } else {
        // Update menu dengan prepared statement
        $query = "UPDATE menu SET nama_menu = ?, deskripsi = ?, harga = ?, stok = ? WHERE id = ?";
        if (executeUpdate($query, [$nama_menu, $deskripsi, $harga, $stok, $menu_id])) {
            header('Location: dashboard.php?success=Menu berhasil diperbarui');
            exit();
        } else {
            $error = 'Gagal memperbarui menu!';
        }
    }
}
?>
<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/sidebar.php'; ?>

<div class="page-header">
    <a href="dashboard.php" style="color: #667eea; text-decoration: none; margin-bottom: 1rem; display: inline-block;">
        ← Kembali ke Dashboard
    </a>
    <h1 class="page-title">✏️ Edit Menu</h1>
    <p class="page-subtitle"><?php echo esc($menu['nama_menu']); ?></p>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger">
        ✗ <?php echo esc($error); ?>
    </div>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">
        ✓ <?php echo esc($_GET['success']); ?>
    </div>
<?php endif; ?>

<div class="card" style="max-width: 600px;">
    <form method="POST" class="card-body">
        <div class="form-group">
            <label for="nama_menu">Nama Menu *</label>
            <input type="text" id="nama_menu" name="nama_menu" value="<?php echo esc($menu['nama_menu']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="deskripsi">Deskripsi</label>
            <textarea id="deskripsi" name="deskripsi"><?php echo esc($menu['deskripsi'] ?? ''); ?></textarea>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="harga">Harga (Rp) *</label>
                <input type="number" id="harga" name="harga" value="<?php echo $menu['harga']; ?>" min="1" required>
            </div>
            
            <div class="form-group">
                <label for="stok">Stok *</label>
                <input type="number" id="stok" name="stok" value="<?php echo $menu['stok']; ?>" min="0" required>
            </div>
        </div>
        
        <div style="background: #f8f9fa; padding: 1rem; border-radius: 5px; margin-bottom: 1.5rem;">
            <p style="margin: 0; color: #666; font-size: 0.9rem;">
                <strong>Dibuat:</strong> <?php echo formatDateTime($menu['created_at']); ?><br>
                <strong>Terakhir diubah:</strong> <?php echo formatDateTime($menu['updated_at']); ?>
            </p>
        </div>
        
        <div class="card-footer">
            <a href="dashboard.php" class="btn btn-secondary">← Batal</a>
            <button type="submit" name="edit_menu" class="btn btn-primary">
                ✓ Simpan Perubahan
            </button>
        </div>
    </form>
</div>

</main>
<?php require_once '../includes/footer.php'; ?>
