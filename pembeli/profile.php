<?php
/**
 * Halaman Profile Pembeli
 */

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pembeli') {
    header('Location: /D-WarungS/auth/login.php');
    exit();
}

require_once '../config/db.php';

$page_title = 'Profile';

// Get user data
$user = getRow("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);

// Handle profile update
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    // Validasi
    if (empty($nama) || empty($email)) {
        $error = 'Semua field harus diisi!';
    } else {
        // Check email uniqueness
        $check_email = getRow("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $_SESSION['user_id']]);
        
        if ($check_email) {
            $error = 'Email sudah digunakan!';
        } else {
            // Update user
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

// Handle password change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
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
        // Verify current password
        if (password_verify($current_password, $user['password'])) {
            // Hash new password
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            
            // Update password
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

// Get statistics
$total_pesanan = getRow("SELECT COUNT(*) as count FROM orders WHERE pembeli_id = ?", [$_SESSION['user_id']])['count'];
$total_pengeluaran = getRow("SELECT SUM(total_harga) as total FROM orders WHERE pembeli_id = ?", [$_SESSION['user_id']])['total'] ?? 0;
$pesanan_aktif = getRow("SELECT COUNT(*) as count FROM orders WHERE pembeli_id = ? AND status IN ('menunggu', 'dibayar', 'diproses')", [$_SESSION['user_id']])['count'];
?>
<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/sidebar.php'; ?>

<div class="page-header">
    <h1 class="page-title">👤 Profile Saya</h1>
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
                <?php echo $total_pesanan; ?>
            </div>
            <div style="color: #666; margin-top: 0.5rem;">Total Pesanan</div>
        </div>
    </div>
    
    <div class="card">
        <div style="text-align: center; padding: 1rem;">
            <div style="font-size: 2rem; font-weight: 700; color: #764ba2;">
                <?php echo formatCurrency($total_pengeluaran); ?>
            </div>
            <div style="color: #666; margin-top: 0.5rem;">Total Pengeluaran</div>
        </div>
    </div>
    
    <div class="card">
        <div style="text-align: center; padding: 1rem;">
            <div style="font-size: 2rem; font-weight: 700; color: #f39c12;">
                <?php echo $pesanan_aktif; ?>
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
