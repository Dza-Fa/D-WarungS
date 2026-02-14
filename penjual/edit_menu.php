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
require_once '../config/security.php'; // Tambahkan security helper
require_once '../config/helpers.php'; // Tambahkan file helper

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
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Sesi tidak valid atau sudah kedaluwarsa. Silakan coba lagi.';
        // Stop execution to prevent CSRF
    } else {
        $nama_menu = trim($_POST['nama_menu'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $harga = intval($_POST['harga'] ?? 0);
        $stok = intval($_POST['stok'] ?? 0);
        $foto = $menu['gambar']; // keep existing photo by default
        
        // Validasi
        if (empty($nama_menu)) {
            $error = 'Nama menu harus diisi!';
        } elseif ($harga <= 0) {
            $error = 'Harga harus lebih dari 0!';
        } elseif ($stok < 0) {
            $error = 'Stok tidak boleh negatif!';
        } else {
            // Handle file upload
            if (isset($_FILES['foto']) && !empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $upload_result = handleFileUpload($_FILES['foto'], '../assets/uploads/menu/', 'menu_' . $menu_id);
                if ($upload_result['success']) {
                    // Delete old photo if exists and a new one is uploaded
                    if (!empty($menu['gambar']) && file_exists('../assets/uploads/menu/' . $menu['gambar'])) {
                        unlink('../assets/uploads/menu/' . $menu['gambar']);
                    }
                    $foto = $upload_result['filename'];
                    $message = 'Foto menu berhasil diperbarui!';
                } else {
                    $error = $upload_result['error'];
                }
            }
            
            if (!$error) {
                // Update menu dengan prepared statement
                $query = "UPDATE menu SET nama_menu = ?, deskripsi = ?, harga = ?, stok = ?, gambar = ? WHERE id = ?";
                if (execute($query, [$nama_menu, $deskripsi, $harga, $stok, $foto, $menu_id])) {
                    // Refresh menu data
                    $menu = getRow("SELECT * FROM menu WHERE id = ?", [$menu_id]);
                    header('Location: dashboard.php?success=' . urlencode('Menu berhasil diperbarui!'));
                    exit();
                } else {
                    $error = 'Gagal memperbarui menu!';
                }
            }
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
    <form method="POST" class="card-body" enctype="multipart/form-data">
        <?php csrf_field(); ?>
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
        
        <!-- Photo Upload Section -->
        <div class="form-group">
            <label for="foto">Foto Menu</label>
            
            <?php if (!empty($menu['gambar']) && file_exists('../assets/uploads/menu/' . $menu['gambar'])): ?>
            <div style="margin-bottom: 1rem;">
                <p style="margin: 0 0 0.5rem 0; font-size: 0.9rem; color: #666;"><strong>Foto saat ini:</strong></p>
                <img src="../assets/uploads/menu/<?php echo esc($menu['gambar']); ?>" 
                     alt="<?php echo esc($menu['nama_menu']); ?>"
                     style="max-width: 200px; max-height: 200px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            </div>
            <p style="margin: 0.5rem 0; font-size: 0.85rem; color: #999;">Unggah foto baru untuk mengganti</p>
            <?php else: ?>
            <p style="margin: 0 0 0.5rem 0; font-size: 0.9rem; color: #666;">Belum ada foto. Unggah foto menu Anda:</p>
            <?php endif; ?>
            
            <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/gif,image/webp" style="padding: 0.75rem; border: 2px dashed #ddd; border-radius: 5px; width: 100%; box-sizing: border-box; cursor: pointer;">
            
            <p style="margin: 0.5rem 0 0 0; font-size: 0.85rem; color: #999;">
                Format: JPG, PNG, GIF, WebP | Max: 5MB
            </p>
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
