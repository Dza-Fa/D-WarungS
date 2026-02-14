<?php
/**
 * Halaman Profile Pembeli - Refactored
 * Menggunakan centralized helpers untuk reduce duplication
 */

session_start();

require_once '../config/db.php';

// Auth check dengan helper function
requireRole('pembeli');

$page_title = 'Profile';
$userId = getUserId();
$user = getRow("SELECT * FROM users WHERE id = ?", [$userId]);
$message = '';
$error = '';

// Handle profile update
if (isPost() && hasPost('update_profile')) {
    if (!verifyCSRFToken()) {
        die('Invalid CSRF token');
    }
    
    $nama = getPost('nama');
    $email = getPost('email');
    
    // Validate dengan centralized validator
    $validation = validateProfileUpdate($nama, $email, $userId);
    
    if ($validation['valid']) {
        // Update user
        $query = "UPDATE users SET nama = ?, email = ? WHERE id = ?";
        if (executeUpdate($query, [$validation['data']['nama'], $validation['data']['email'], $userId])) {
            updateSessionUser('nama', $validation['data']['nama']);
            $user['nama'] = $validation['data']['nama'];
            $user['email'] = $validation['data']['email'];
            $message = 'Profile berhasil diperbarui!';
            logActivity($userId, 'update_profile', 'Nama: ' . $validation['data']['nama']);
        } else {
            $error = 'Gagal memperbarui profile!';
        }
    } else {
        $error = $validation['errors'][0];
    }
}

// Handle password change
if (isPost() && hasPost('change_password')) {
    if (!verifyCSRFToken()) {
        die('Invalid CSRF token');
    }
    
    $current_password = getPost('current_password', '', false);
    $new_password = getPost('new_password', '', false);
    $confirm_password = getPost('confirm_password', '', false);
    
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
            logActivity($userId, 'change_password', 'Password changed');
        } else {
            $error = 'Gagal mengubah password!';
        }
    } else {
        $error = $validation['errors'][0];
    }
}

// Get statistics dengan helper function
$stats = getUserStatistics($userId, 'pembeli');

// Session message jika ada
$sessionMessage = getSessionMessage();
if ($sessionMessage) {
    $message = $sessionMessage['text'];
}
?>
<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/sidebar.php'; ?>

<div class="page-header">
    <h1 class="page-title">👤 Profile Saya</h1>
</div>

<?php if (isset($message)): ?>
    <?php showAlert($message, 'success'); ?>
<?php endif; ?>

<?php if (isset($error)): ?>
    <?php showAlert($error, 'error'); ?>
<?php endif; ?>

<!-- Statistics -->
<div class="grid grid-4" style="margin-bottom: 2rem;">
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
        <?php echo csrfTokenInput(); ?>
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
        <?php echo csrfTokenInput(); ?>
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
