<?php
/**
 * Profile Kasir
 */

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'kasir') {
    header('Location: /D-WarungS/auth/login.php');
    exit();
}

require_once '../config/db.php';
require_once '../config/security.php';

$page_title = 'Profile Kasir';

// Get user data
$user = getRow("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);

$message = '';
$error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Token keamanan tidak valid.';
    } else {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    if (empty($nama) || empty($email)) {
        $error = 'Semua field harus diisi!';
    } else {
        $check_email = getRow("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $_SESSION['user_id']]);
        
        if ($check_email) {
            $error = 'Email sudah digunakan!';
        } else {
            $query = "UPDATE users SET nama = ?, email = ? WHERE id = ?";
            if (executeUpdate($query, [$nama, $email, $_SESSION['user_id']])) {
                $_SESSION['nama'] = $nama;
                $user['nama'] = $nama;
                $user['email'] = $email;
                $message = 'Profile berhasil diperbarui!';
            } else {
                $error = 'Gagal memperbarui profile!';
            }
        }
    }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Token keamanan tidak valid.';
    } else {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'Semua field password harus diisi!';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Password baru tidak cocok!';
    } elseif (strlen($new_password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } else {
        if (password_verify($current_password, $user['password'])) {
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $query = "UPDATE users SET password = ? WHERE id = ?";
            if (executeUpdate($query, [$hashed_password, $_SESSION['user_id']])) {
                $message = 'Password berhasil diubah!';
            } else {
                $error = 'Gagal mengubah password!';
            }
        } else {
            $error = 'Password lama salah!';
        }
    }
    }
}

// Get statistics
$pembayaran_divalidasi = getRow("SELECT COUNT(*) as count FROM orders WHERE status != 'menunggu'")['count'] ?? 0;
$total_penjualan = getRow("SELECT SUM(total_harga) as total FROM orders WHERE status IN ('dibayar', 'diproses', 'siap')")["total"] ?? 0;
$pembayaran_menunggu = getRow("SELECT COUNT(*) as count FROM orders WHERE status = 'menunggu'")['count'] ?? 0;
?>
<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/sidebar.php'; ?>

<div class="page-header">
    <h1 class="page-title">👤 Profile Kasir</h1>
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
<div class="grid grid-3" style="margin-bottom: 2rem;">
    <div class="card">
        <div style="text-align: center; padding: 1rem;">
            <div style="font-size: 2rem; font-weight: 700; color: #f39c12;">
                <?php echo $pembayaran_menunggu; ?>
            </div>
            <div style="color: #666; margin-top: 0.5rem;">Pembayaran Menunggu</div>
        </div>
    </div>
    
    <div class="card">
        <div style="text-align: center; padding: 1rem;">
            <div style="font-size: 2rem; font-weight: 700; color: #27ae60;">
                <?php echo formatCurrency($total_penjualan); ?>
            </div>
            <div style="color: #666; margin-top: 0.5rem;">Total Penjualan</div>
        </div>
    </div>
    
    <div class="card">
        <div style="text-align: center; padding: 1rem;">
            <div style="font-size: 2rem; font-weight: 700; color: #667eea;">
                <?php echo $pembayaran_divalidasi; ?>
            </div>
            <div style="color: #666; margin-top: 0.5rem;">Pembayaran Divalidasi</div>
        </div>
    </div>
</div>

<!-- Profile Update Form -->
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header">
        <h3>Informasi Profile</h3>
    </div>
    
    <form method="POST" class="card-body">
        <?php csrf_field(); ?>
        <div class="form-row">
            <div class="form-group">
                <label for="nama">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" value="<?php echo esc($user['nama']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo esc($user['email']); ?>" required>
            </div>
        </div>
        
        <div class="card-footer">
            <button type="submit" name="update_profile" class="btn btn-primary">
                💾 Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<!-- Change Password Form -->
<div class="card">
    <div class="card-header">
        <h3>Ubah Password</h3>
    </div>
    
    <form method="POST" class="card-body">
        <?php csrf_field(); ?>
        <div class="form-group">
            <label for="current_password">Password Saat Ini</label>
            <input type="password" id="current_password" name="current_password" required>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="new_password">Password Baru</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
        </div>
        
        <div class="card-footer">
            <button type="submit" name="change_password" class="btn btn-primary">
                🔑 Ubah Password
            </button>
        </div>
    </form>
</div>

</main>
<?php require_once '../includes/footer.php'; ?>
