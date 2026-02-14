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
