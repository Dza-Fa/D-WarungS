<?php
/**
 * Halaman Hapus Akun
 * Meminta konfirmasi password sebelum menghapus data user
 */

session_start();
require_once '../config/db.php';
require_once '../config/security.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Token keamanan tidak valid.';
    } else {
        $password = $_POST['password'] ?? '';
        
        if (empty($password)) {
            $error = 'Masukkan password konfirmasi!';
        } else {
            // Verifikasi password user saat ini
            $user = getRow("SELECT password, role FROM users WHERE id = ?", [$_SESSION['user_id']]);
            
            if ($user && password_verify($password, $user['password'])) {
                $user_id = $_SESSION['user_id'];
                $role = $user['role'];
                
                try {
                    // Mulai Transaksi
                    startTransaction();

                    // 1. Cek Tanggungan (Pesanan Aktif)
                    if ($role == 'pembeli') {
                        $active_orders = getRow("SELECT COUNT(*) as count FROM orders WHERE pembeli_id = ? AND status NOT IN ('selesai', 'batal')", [$user_id])['count'];
                        if ($active_orders > 0) {
                            throw new Exception("Selesaikan semua pesanan aktif sebelum menghapus akun.");
                        }
                    } elseif ($role == 'pedagang') {
                        $warung = getRow("SELECT id FROM warung WHERE pemilik_id = ?", [$user_id]);
                        if ($warung) {
                            $active_orders_warung = getRow("
                                SELECT COUNT(DISTINCT o.id) as count 
                                FROM orders o 
                                JOIN order_items oi ON o.id = oi.order_id 
                                JOIN menu m ON oi.menu_id = m.id 
                                WHERE m.warung_id = ? AND o.status NOT IN ('selesai', 'batal')
                            ", [$warung['id']])['count'];
                            
                            if ($active_orders_warung > 0) {
                                throw new Exception("Selesaikan semua pesanan pelanggan sebelum menghapus akun warung.");
                            }
                        }
                    }

                    // 2. Bersihkan data terkait (Manual Cleanup)
                    execute("DELETE FROM notifications WHERE user_id = ?", [$user_id]);
                    
                    if ($role == 'pembeli') {
                        execute("DELETE FROM favorites WHERE pembeli_id = ?", [$user_id]);
                        execute("DELETE FROM ratings WHERE pembeli_id = ?", [$user_id]);
                    } elseif ($role == 'pedagang') {
                        if ($warung) {
                            execute("DELETE FROM menu WHERE warung_id = ?", [$warung['id']]);
                            execute("DELETE FROM warung WHERE id = ?", [$warung['id']]);
                        }
                    }
                    
                    // 3. Hapus User
                    if (execute("DELETE FROM users WHERE id = ?", [$user_id])) {
                        // Commit jika semua berhasil
                        commitTransaction();
                        
                        session_destroy();
                        header('Location: login.php?success=' . urlencode('Akun Anda berhasil dihapus. Sampai jumpa!'));
                        exit();
                    } else {
                        throw new Exception("Gagal menghapus data user utama.");
                    }
                } catch (Exception $e) {
                    rollbackTransaction(); // Batalkan semua perubahan jika ada error
                    $error = 'Gagal: ' . $e->getMessage();
                }
            } else {
                $error = 'Password salah! Silakan coba lagi.';
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
    <title>Hapus Akun - D-Warung</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { background: linear-gradient(135deg, #e53e3e 0%, #9b2c2c 100%); display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 1rem; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        .auth-card { background: white; width: 100%; max-width: 450px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); padding: 2rem; }
        .auth-header { text-align: center; margin-bottom: 2rem; }
        .auth-header h2 { color: #c53030; font-size: 1.8rem; margin-bottom: 0.5rem; }
        .warning-box { background: #fff5f5; border-left: 4px solid #c53030; padding: 1rem; margin-bottom: 1.5rem; color: #c53030; font-size: 0.9rem; line-height: 1.5; border-radius: 4px; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; color: #4a5568; font-weight: 500; }
        .form-group input { width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 0.375rem; font-size: 1rem; box-sizing: border-box; }
        .btn-danger { background-color: #c53030; color: white; border: none; padding: 0.75rem; width: 100%; border-radius: 0.375rem; font-size: 1rem; font-weight: 600; cursor: pointer; transition: background 0.2s; }
        .btn-danger:hover { background-color: #9b2c2c; }
        .alert-danger { background-color: #fed7d7; color: #822727; padding: 1rem; border-radius: 0.375rem; margin-bottom: 1.5rem; }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="auth-header">
            <h2>⚠️ Hapus Akun</h2>
            <p class="text-muted">Apakah Anda yakin ingin pergi?</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo esc($error); ?></div>
        <?php endif; ?>

        <div class="warning-box">
            <strong>Perhatian:</strong> Tindakan ini bersifat permanen dan tidak dapat dibatalkan. Profil dan riwayat aktivitas Anda akan dihapus dari sistem.
        </div>

        <form method="POST">
            <?php csrf_field(); ?>
            
            <div class="form-group">
                <label for="password">Konfirmasi Password Anda</label>
                <input type="password" id="password" name="password" required placeholder="Masukkan password saat ini..." autofocus>
            </div>

            <button type="submit" class="btn-danger" onclick="return confirm('Yakin ingin menghapus akun secara permanen?');">
                🗑️ Ya, Hapus Akun Saya
            </button>
        </form>

        <div class="text-center mt-3" style="text-align: center; margin-top: 1.5rem;">
            <a href="javascript:history.back()" style="color: #718096; text-decoration: none; font-size: 0.9rem;">← Batalkan dan Kembali</a>
        </div>
    </div>
</body>
</html>
