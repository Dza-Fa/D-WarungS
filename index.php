<?php
/**
 * Landing Page / Home
 */

session_start();

require_once 'config/db.php';

// Jika sudah login, redirect ke dashboard
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

// Get count data untuk statistik
$total_warung = getRow("SELECT COUNT(*) as count FROM warung")['count'] ?? 0;
$total_menu = getRow("SELECT COUNT(*) as count FROM menu WHERE status_aktif = 1")['count'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>D-Warung - Sistem Pemesanan Kantin</title>
    <link rel="stylesheet" href="/D-WarungS/assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .hero-container {
            max-width: 1000px;
            text-align: center;
        }
        
        .hero-title {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }
        
        .hero-subtitle {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            opacity: 0.95;
        }
        
        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 3rem;
        }
        
        .btn-hero {
            padding: 1rem 2rem;
            border: 2px solid white;
            border-radius: 5px;
            text-decoration: none;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-block;
        }
        
        .btn-hero-primary {
            background: white;
            color: #667eea;
        }
        
        .btn-hero-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        .btn-hero-secondary {
            background: transparent;
            color: white;
        }
        
        .btn-hero-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-3px);
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
            padding-top: 3rem;
            border-top: 2px solid rgba(255, 255, 255, 0.2);
        }
        
        .stat-item {
            background: rgba(255, 255, 255, 0.1);
            padding: 2rem;
            border-radius: 8px;
            backdrop-filter: blur(10px);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            font-size: 1rem;
            opacity: 0.9;
        }
        
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2rem;
            }
            
            .hero-subtitle {
                font-size: 1rem;
            }
            
            .hero-buttons {
                flex-direction: column;
            }
            
            .btn-hero {
                width: 100%;
            }
            
            .stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="hero-container">
        <h1 class="hero-title">🍜 D-Warung</h1>
        <p class="hero-subtitle">Sistem Pemesanan Kantin Multi-Pedagang</p>
        
        <div class="hero-buttons">
            <a href="/D-WarungS/auth/login.php" class="btn-hero btn-hero-primary">
                🔓 Login
            </a>
        </div>
        
        <div class="stats">
            <div class="stat-item">
                <div class="stat-number"><?php echo $total_warung; ?></div>
                <div class="stat-label">Warung Aktif</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo $total_menu; ?></div>
                <div class="stat-label">Menu Tersedia</div>
            </div>
        </div>
    </div>
</body>
</html>
