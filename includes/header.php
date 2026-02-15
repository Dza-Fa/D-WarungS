<?php
/**
 * Header Include
 */

// Check session
if (!isset($_SESSION['user_id'])) {
    header('Location: /D-WarungS/auth/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? esc($page_title) . ' - D-Warung' : 'D-Warung'; ?></title>
    <link rel="stylesheet" href="/D-WarungS/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f7fafc; color: #2d3748; }
        .page-header { margin-bottom: 2rem; }
        .page-title { font-size: 1.75rem; font-weight: 700; color: #1a202c; margin-bottom: 0.5rem; }
        .page-subtitle { color: #718096; font-size: 1rem; }
        .card { border: none; border-radius: 0.75rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03); background: white; margin-bottom: 1.5rem; overflow: hidden; }
        .card-header { background: white; border-bottom: 1px solid #edf2f7; padding: 1.25rem 1.5rem; }
        .card-header h3 { font-size: 1.1rem; font-weight: 600; margin: 0; color: #2d3748; }
        .card-body { padding: 1.5rem; }
        .card-footer { background: #f7fafc; border-top: 1px solid #edf2f7; padding: 1rem 1.5rem; }
        .btn { border-radius: 0.5rem; font-weight: 500; padding: 0.5rem 1rem; transition: all 0.2s; }
        .btn-primary { background-color: #667eea; border-color: #667eea; }
        .btn-primary:hover { background-color: #5a67d8; border-color: #5a67d8; transform: translateY(-1px); }
        .form-control, input[type="text"], input[type="email"], input[type="password"], input[type="number"], input[type="date"], textarea {
            width: 100%; padding: 0.625rem 1rem; border: 1px solid #e2e8f0; border-radius: 0.5rem; outline: none; transition: border-color 0.2s;
        }
        .form-control:focus, input:focus, textarea:focus { border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }
        .table th { background-color: #f7fafc; color: #4a5568; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; padding: 0.75rem 1rem; border-bottom: 1px solid #edf2f7; }
        .table td { padding: 1rem; border-bottom: 1px solid #edf2f7; vertical-align: middle; }
        .alert { border-radius: 0.5rem; border: none; }
        .empty-state { text-align: center; padding: 3rem 1rem; }
        .empty-state-icon { font-size: 3rem; margin-bottom: 1rem; opacity: 0.8; }
        .grid { gap: 1.5rem; }
    </style>
</head>
<body>
    <div class="app-container">
        <header class="app-header">
            <div class="header-logo">
                <h2>🍜 D-Warung</h2>
            </div>
            <div class="header-right">
                <div class="notification-bell">
                    <?php
                    // Get unread notification count based on role
                    $role = $_SESSION['role'];
                    $unread = 0;
                    if ($role === 'pembeli') {
                        $unread = getRow("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND role = 'pembeli' AND is_read = 0", [$_SESSION['user_id']])['count'] ?? 0;
                    } elseif ($role === 'pedagang') {
                        $unread = getRow("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND role = 'pedagang' AND is_read = 0", [$_SESSION['user_id']])['count'] ?? 0;
                    }
                    ?><?php if ($role === 'pembeli'): ?>
                        <a href="notifikasi.php" style="color: #333; text-decoration: none; position: relative; display: inline-block;">
                            <i class="fas fa-bell"></i>
                            <?php if ($unread > 0): ?>
                                <span style="position: absolute; top: -5px; right: -10px; background: #ff6b6b; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: bold;">
                                    <?php echo $unread; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    <?php elseif ($role === 'pedagang'): ?>
                        <a href="notifikasi.php" style="color: #333; text-decoration: none; position: relative; display: inline-block;">
                            <i class="fas fa-bell"></i>
                            <?php if ($unread > 0): ?>
                                <span style="position: absolute; top: -5px; right: -10px; background: #ff6b6b; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: bold;">
                                    <?php echo $unread; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>
                </div>
                <div class="user-info">
                    <span><?php echo esc($_SESSION['nama']); ?></span>
                    <small>(<?php echo esc($_SESSION['role']); ?>)</small>
                </div>
                <a href="/D-WarungS/auth/logout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </header>
        
        <div class="app-body">
