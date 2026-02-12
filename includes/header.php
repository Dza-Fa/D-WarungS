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
