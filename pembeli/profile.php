<?php
/**
 * Halaman Profile Pembeli - Refactored
 * Menggunakan centralized helpers untuk reduce duplication
 */

session_start();

require_once '../config/db.php';
require_once '../config/security.php';
require_once '../config/validators.php';

// Auth check dengan helper function
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pembeli') {
    header('Location: /D-WarungS/auth/login.php');
    exit();
}

$page_title = 'Profile';
$userId = $_SESSION['user_id'];
$user = getRow("SELECT * FROM users WHERE id = ?", [$userId]);
$message = '';
$error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Token keamanan tidak valid.';
    }
    
    else {
        $nama = $_POST['nama'] ?? '';
        $email = $_POST['email'] ?? '';
    
    // Validate dengan centralized validator
    $validation = validateProfileUpdate($nama, $email, $userId);
    
    if ($validation['valid']) {
        // Update user
        $query = "UPDATE users SET nama = ?, email = ? WHERE id = ?";
        if (executeUpdate($query, [$validation['data']['nama'], $validation['data']['email'], $userId])) {
            $_SESSION['nama'] = $validation['data']['nama'];
            $user['nama'] = $validation['data']['nama'];
            $user['email'] = $validation['data']['email'];
            $message = 'Profile berhasil diperbarui!';
        } else {
            $error = 'Gagal memperbarui profile!';
        }
    } else {
        $error = $validation['errors'][0];
    }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Token keamanan tidak valid.';
    }
    
    else {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate password change
    $validation = validatePasswordChange(
        $current_password, 
        $new_password, 
        $confirm_password, 
        $user['password']
    );
    
    if ($validation['valid']) {
        // Hash dan update password
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        
        // Update password
        $query = "UPDATE users SET password = ? WHERE id = ?";
        if (executeUpdate($query, [$hashed_password, $userId])) {
            $message = 'Password berhasil diubah!';
        } else {
            $error = 'Gagal mengubah password!';
        }
    } else {
        $error = $validation['errors'][0];
    }
    }
}

// Get statistics dengan helper function
$stats = getUserStatistics($userId, 'pembeli');

// Fix: Ensure pesanan_aktif excludes cancelled orders explicitly
$stats['pesanan_aktif'] = getRow("SELECT COUNT(*) as count FROM orders WHERE pembeli_id = ? AND is_confirmed = 0 AND status != 'batal'", [$userId])['count'];
?>
<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/sidebar.php'; ?>

<div class="page-header">
    <h1 class="page-title">👤 Profile Saya</h1>
</div>

<?php if ($message): ?>
    <div class="alert alert-success" style="background-color: #c6f6d5; color: #22543d; padding: 1rem; margin-bottom: 1.5rem;">
        ✓ <?php echo esc($message); ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger" style="background-color: #fed7d7; color: #822727; padding: 1rem; margin-bottom: 1.5rem;">
        ✗ <?php echo esc($error); ?>
    </div>
<?php endif; ?>

<!-- Statistics -->
<div class="grid grid-3" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <div class="card">
        <div style="text-align: center; padding: 1rem;">
            <div style="font-size: 2rem; font-weight: 700; color: #667eea;">
                <?php echo $stats['total_pesanan']; ?>
            </div>
            <div style="color: #666; margin-top: 0.5rem;">Total Pesanan</div>
        </div>
    </div>
    
    <div class="card">
        <div style="text-align: center; padding: 1rem;">
            <div style="font-size: 2rem; font-weight: 700; color: #764ba2;">
                <?php echo formatCurrency($stats['total_pengeluaran']); ?>
            </div>
            <div style="color: #666; margin-top: 0.5rem;">Total Pengeluaran</div>
        </div>
    </div>
    
    <div class="card">
        <div style="text-align: center; padding: 1rem;">
            <div style="font-size: 2rem; font-weight: 700; color: #f39c12;">
                <?php echo $stats['pesanan_aktif']; ?>
            </div>
            <div style="color: #666; margin-top: 0.5rem;">Pesanan Aktif</div>
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
        <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1rem;">
            <div class="form-group">
                <label for="nama" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #4a5568;">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" value="<?php echo esc($user['nama']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #4a5568;">Email</label>
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
        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label for="current_password" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #4a5568;">Password Saat Ini</label>
            <input type="password" id="current_password" name="current_password" required>
        </div>
        
        <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label for="new_password" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #4a5568;">Password Baru</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #4a5568;">Konfirmasi Password</label>
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
