<?php
/**
 * Login Page
 */

session_start();

// Check jika sudah login
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'];
    if ($role === 'pembeli') {
        header('Location: /D-WarungS/pembeli/dashboard.php');
    } elseif ($role === 'pedagang') {
        header('Location: /D-WarungS/penjual/dashboard.php');
    } elseif ($role === 'kasir') {
        header('Location: /D-WarungS/kasir/dashboard.php');
    }
    exit();
}

require_once '../config/db.php';

$error = '';
$email = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    // Validasi input
    if (empty($email) || empty($password)) {
        $error = 'Email dan password harus diisi!';
    } else {
        // Query user berdasarkan email dengan prepared statement
        $query = "SELECT id, nama, email, password, role FROM users WHERE email = ?";
        $user = getRow($query, [$email]);
        
        if ($user && password_verify($password, $user['password'])) {
            // Password benar
            // Regenerate session ID untuk security
            session_regenerate_id(true);
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['login_time'] = time();
            
            // Redirect based on role
            switch ($user['role']) {
                case 'pembeli':
                    header('Location: /D-WarungS/pembeli/dashboard.php');
                    break;
                case 'pedagang':
                    header('Location: /D-WarungS/penjual/dashboard.php');
                    break;
                case 'kasir':
                    header('Location: /D-WarungS/kasir/dashboard.php');
                    break;
                default:
                    header('Location: /D-WarungS/index.php');
            }
            exit();
        } else {
            $error = 'Email atau password salah!';
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
    <link rel="stylesheet" href="/D-WarungS/assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h1 {
            color: #333;
            margin: 0;
            font-size: 28px;
        }
        
        .login-header p {
            color: #666;
            margin: 5px 0 0 0;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }
        
        .form-group input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
            box-sizing: border-box;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 5px rgba(102, 126, 234, 0.3);
        }
        
        .error-message {
            background-color: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #c33;
        }
        
        .login-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        .login-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }
        
        .demo-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 25px;
            border-top: 1px solid #ddd;
            padding-top: 25px;
        }
        
        .demo-section h4 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .demo-account {
            background: white;
            padding: 8px 12px;
            border-radius: 4px;
            margin-bottom: 8px;
            font-size: 12px;
            border-left: 3px solid #667eea;
        }
        
        .demo-account strong {
            display: block;
            color: #333;
        }
        
        .demo-account small {
            color: #999;
            display: block;
            margin-top: 2px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>🍜 D-Warung</h1>
            <p>Sistem Pemesanan Kantin</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo esc($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo esc($email); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="login-btn">Login</button>
        </form>
        
        <div class="demo-section">
            <h4>📝 Akun Demo</h4>
            
            <div class="demo-account">
                <strong>Pembeli</strong>
                <small>Email: budi@example.com</small>
                <small>Password: password123</small>
            </div>
            
            <div class="demo-account">
                <strong>Pedagang</strong>
                <small>Email: rini.pedagang@example.com</small>
                <small>Password: password123</small>
            </div>
            
            <div class="demo-account">
                <strong>Kasir</strong>
                <small>Email: tono.kasir@example.com</small>
                <small>Password: password123</small>
            </div>
        </div>
    </div>
</body>
</html>
