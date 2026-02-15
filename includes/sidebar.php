<?php
/**
 * Sidebar Include
 */
?>
<style>
.nav-item { display: flex; align-items: center; padding: 0.75rem 1rem; color: #4a5568; text-decoration: none; border-radius: 0.5rem; margin-bottom: 0.25rem; transition: all 0.2s; }
.nav-item:hover { background-color: #edf2f7; color: #2d3748; }
.nav-item.active { background-color: #667eea; color: white; box-shadow: 0 4px 6px -1px rgba(102, 126, 234, 0.4); }
.nav-item i { width: 1.5rem; text-align: center; margin-right: 0.75rem; }
</style>
<aside class="app-sidebar">
    <nav class="sidebar-nav">
        <?php
        $role = $_SESSION['role'];
        $current_page = basename($_SERVER['PHP_SELF']);
        
        function active($page) {
            global $current_page;
            return $current_page === $page ? 'active' : '';
        }
        
        if ($role === 'pembeli'):
        ?>
            <a href="/D-WarungS/pembeli/dashboard.php" class="nav-item <?php echo active('dashboard.php'); ?>">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="/D-WarungS/pembeli/pesanan.php" class="nav-item <?php echo active('pesanan.php'); ?>">
                <i class="fas fa-receipt"></i>
                <span>Pesanan Aktif</span>
            </a>
            <a href="/D-WarungS/pembeli/pesanan_selesai.php" class="nav-item <?php echo active('pesanan_selesai.php'); ?>">
                <i class="fas fa-check-circle"></i>
                <span>Riwayat Pesanan</span>
            </a>
            <a href="/D-WarungS/pembeli/favorit.php" class="nav-item <?php echo active('favorit.php'); ?>">
                <i class="fas fa-heart"></i>
                <span>Favorit</span>
            </a>
            <a href="/D-WarungS/pembeli/rating.php" class="nav-item <?php echo active('rating.php'); ?>">
                <i class="fas fa-star"></i>
                <span>Rating & Review</span>
            </a>
            <a href="/D-WarungS/pembeli/notifikasi.php" class="nav-item <?php echo active('notifikasi.php'); ?>">
                <i class="fas fa-bell"></i>
                <span>Notifikasi</span>
            </a>
            <a href="/D-WarungS/pembeli/profile.php" class="nav-item <?php echo active('profile.php'); ?>">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
        
        <?php elseif ($role === 'pedagang'): ?>
            <a href="/D-WarungS/penjual/dashboard.php" class="nav-item <?php echo active('dashboard.php'); ?>">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="/D-WarungS/penjual/tambah_menu.php" class="nav-item <?php echo active('tambah_menu.php'); ?>">
                <i class="fas fa-plus-circle"></i>
                <span>Tambah Menu</span>
            </a>
            <a href="/D-WarungS/penjual/pesanan.php" class="nav-item <?php echo active('pesanan.php'); ?>">
                <i class="fas fa-receipt"></i>
                <span>Pesanan Masuk</span>
            </a>
            <a href="/D-WarungS/penjual/pesanan_selesai.php" class="nav-item <?php echo active('pesanan_selesai.php'); ?>">
                <i class="fas fa-check-circle"></i>
                <span>Pesanan Selesai</span>
            </a>
            <a href="/D-WarungS/penjual/rating_menu.php" class="nav-item <?php echo active('rating_menu.php'); ?>">
                <i class="fas fa-star"></i>
                <span>Rating Menu</span>
            </a>
            <a href="/D-WarungS/penjual/notifikasi.php" class="nav-item <?php echo active('notifikasi.php'); ?>">
                <i class="fas fa-bell"></i>
                <span>Notifikasi</span>
            </a>
            <a href="/D-WarungS/penjual/profile.php" class="nav-item <?php echo active('profile.php'); ?>">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
        
        <?php elseif ($role === 'kasir'): ?>
            <a href="/D-WarungS/kasir/dashboard.php" class="nav-item <?php echo active('dashboard.php'); ?>">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="/D-WarungS/kasir/penjualan.php" class="nav-item <?php echo active('penjualan.php'); ?>">
                <i class="fas fa-money-bill-wave"></i>
                <span>Penjualan</span>
            </a>
            <a href="/D-WarungS/kasir/profile.php" class="nav-item <?php echo active('profile.php'); ?>">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
        <?php endif; ?>
    </nav>
</aside>
<main class="app-main">
