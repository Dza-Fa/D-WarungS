<?php
/**
 * Logout Page
 */

session_start();

// Destroy session
session_destroy();

// Redirect ke halaman login
header('Location: /D-WarungS/auth/login.php');
exit();
?>
