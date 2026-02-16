<?php
/**
 * Halaman Registrasi User Baru
 */

session_start();
require_once '../config/db.php';

// Coba load security helper jika ada, jika tidak definisikan fungsi dasar
if (file_exists('../config/security.php')) {
    require_once '../config/security.php';
} else {
    // Fallback minimal jika file security belum ada
    if (!isset($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    function csrf_field() { echo '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">'; }
    function validate_csrf_token($token) { return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token); }
    function esc($str) { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }
}

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Token keamanan tidak valid.';
    } else {
        $nama = trim($_POST['nama'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $role = $_POST['role'] ?? 'pembeli';

        // Validasi sederhana
        if (empty($nama) || empty($email) || empty($password)) {
            $error = 'Semua kolom wajib diisi!';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Format email tidak valid!';
        } elseif (strlen($password) < 6) {
            $error = 'Password minimal 6 karakter!';
        } elseif ($password !== $confirm_password) {
            $error = 'Konfirmasi password tidak cocok!';
        } elseif (!in_array($role, ['pembeli', 'pedagang'])) {
            $error = 'Role tidak valid!';
        } else {
            // Cek apakah email sudah terdaftar
            $existing = getRow("SELECT id FROM users WHERE email = ?", [$email]);
            if ($existing) {
                $error = 'Email sudah terdaftar! Silakan login.';
            } else {
                // Hash password (PENTING!)
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert ke database
                $query = "INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)";
                if (execute($query, [$nama, $email, $hashed_password, $role])) {
                    // Redirect ke login dengan pesan sukses
                    header('Location: login.php?success=' . urlencode('Registrasi berhasil! Silakan login.'));
                    exit();
                } else {
                    $error = 'Terjadi kesalahan sistem. Silakan coba lagi.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - D-Warung</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 1rem; }
        .auth-card { background: white; width: 100%; max-width: 450px; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); padding: 2rem; }
        .auth-header { text-align: center; margin-bottom: 2rem; }
        .auth-header h2 { color: #667eea; font-size: 1.8rem; margin-bottom: 0.5rem; }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="auth-header">
            <h2>📝 Daftar Akun</h2>
            <p class="text-muted">Bergabung dengan D-Warung</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo esc($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <?php csrf_field(); ?>
            
            <div class="form-group">
                <label for="nama">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" value="<?php echo esc($_POST['nama'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo esc($_POST['email'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="role">Daftar Sebagai</label>
                <select name="role" id="role" required>
                    <option value="pembeli" <?php echo (isset($_POST['role']) && $_POST['role'] == 'pembeli') ? 'selected' : ''; ?>>Pembeli (Siswa/Guru)</option>
                    <option value="pedagang" <?php echo (isset($_POST['role']) && $_POST['role'] == 'pedagang') ? 'selected' : ''; ?>>Pedagang Kantin</option>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required minlength="6">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Konfirmasi</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg">Daftar Sekarang</button>
        </form>

        <div class="text-center mt-3">
            <p style="font-size: 0.9rem;">
                Sudah punya akun? <a href="login.php" style="color: #667eea; text-decoration: none; font-weight: 600;">Login disini</a>
            </p>
            <a href="../index.php" style="color: #999; font-size: 0.85rem; text-decoration: none;">← Kembali ke Beranda</a>
        </div>
    </div>
</body>
</html>