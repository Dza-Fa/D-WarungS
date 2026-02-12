<?php
/**
 * Tambah Menu Warung
 */

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pedagang') {
    header('Location: /D-WarungS/auth/login.php');
    exit();
}

require_once '../config/db.php';

$page_title = 'Tambah Menu';

// Get warung milik penjual
$warung = getRow("SELECT * FROM warung WHERE pemilik_id = ?", [$_SESSION['user_id']]);

if (!$warung) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_menu'])) {
    $nama_menu = trim($_POST['nama_menu'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $harga = intval($_POST['harga'] ?? 0);
    $stok = intval($_POST['stok'] ?? 0);
    
    // Validasi
    if (empty($nama_menu)) {
        $error = 'Nama menu harus diisi!';
    } elseif ($harga <= 0) {
        $error = 'Harga harus lebih dari 0!';
    } elseif ($stok < 0) {
        $error = 'Stok tidak boleh negatif!';
    } else {
        // Insert menu dengan prepared statement
        $query = "INSERT INTO menu (warung_id, nama_menu, deskripsi, harga, stok) VALUES (?, ?, ?, ?, ?)";
        if (executeUpdate($query, [$warung['id'], $nama_menu, $deskripsi, $harga, $stok])) {
            header('Location: dashboard.php?success=Menu berhasil ditambahkan');
            exit();
        } else {
            $error = 'Gagal menambahkan menu!';
        }
    }
}
?>
<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/sidebar.php'; ?>

<div class="page-header">
    <a href="dashboard.php" style="color: #667eea; text-decoration: none; margin-bottom: 1rem; display: inline-block;">
        ← Kembali ke Dashboard
    </a>
    <h1 class="page-title">➕ Tambah Menu Baru</h1>
    <p class="page-subtitle">Warung: <?php echo esc($warung['nama_warung']); ?></p>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger">
        ✗ <?php echo esc($error); ?>
    </div>
<?php endif; ?>

<div class="card" style="max-width: 600px;">
    <form method="POST" class="card-body">
        <div class="form-group">
            <label for="nama_menu">Nama Menu *</label>
            <input type="text" id="nama_menu" name="nama_menu" placeholder="Contoh: Nasi Goreng" required>
        </div>
        
        <div class="form-group">
            <label for="deskripsi">Deskripsi</label>
            <textarea id="deskripsi" name="deskripsi" placeholder="Deskripsi menu (opsional)"></textarea>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="harga">Harga (Rp) *</label>
                <input type="number" id="harga" name="harga" placeholder="15000" min="1" required>
            </div>
            
            <div class="form-group">
                <label for="stok">Stok Awal *</label>
                <input type="number" id="stok" name="stok" placeholder="10" min="0" required>
            </div>
        </div>
        
        <div class="card-footer">
            <a href="dashboard.php" class="btn btn-secondary">← Batal</a>
            <button type="submit" name="tambah_menu" class="btn btn-primary">
                ✓ Simpan Menu
            </button>
        </div>
    </form>
</div>

</main>
<?php require_once '../includes/footer.php'; ?>
