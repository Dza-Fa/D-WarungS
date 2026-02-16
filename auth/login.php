<?php
/**
 * Halaman Login
 */

session_start();
require_once '../config/db.php';

// Load security helper
if (file_exists('../config/security.php')) {
    require_once '../config/security.php';
} else {
    // Fallback minimal jika file security belum ada
    if (!isset($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    function csrf_field() { echo '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">'; }
    function validate_csrf_token($token) { return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token); }
    function esc($str) { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }
}

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$error = '';
$success = $_GET['success'] ?? '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Token keamanan tidak valid.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error = 'Email dan password wajib diisi!';
        } else {
            // Cek user di database
            $user = getRow("SELECT * FROM users WHERE email = ?", [$email]);
            
            if ($user && password_verify($password, $user['password'])) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['nama'] = $user['nama'];
                
                // Redirect sesuai role
                if ($user['role'] == 'pedagang') {
                    header('Location: ../penjual/dashboard.php');
                } elseif ($user['role'] == 'kasir') {
                    header('Location: ../kasir/dashboard.php');
                } else {
                    // Pembeli
                    header('Location: ../pembeli/dashboard.php');
                }
                exit();
            } else {
                $error = 'Email atau password salah!';
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
    <title>Login - D-Warung</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { background:  linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 1rem; }
        .auth-card { background: white; width: 100%; max-width: 400px; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); padding: 2rem; }
        .auth-header { text-align: center; margin-bottom: 2rem; }
        .auth-header h2 { color: #667eea; font-size: 1.8rem; margin-bottom: 0.5rem; }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="auth-header">
            <h2>🔓 Login</h2>
            <p class="text-muted">Masuk ke D-Warung</p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo esc($success); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo esc($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <?php csrf_field(); ?>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo esc($_POST['email'] ?? ''); ?>" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg">Masuk</button>
        </form>

        <div class="text-center mt-3">
            <p style="font-size: 0.9rem;">
                Belum punya akun? <a href="register.php" style="color: #667eea; text-decoration: none; font-weight: 600;">Daftar disini</a>
            </p>
            <a href="../index.php" style="color: #999; font-size: 0.85rem; text-decoration: none;">← Kembali ke Beranda</a>
        </div>
    </div>
</body>
</html>