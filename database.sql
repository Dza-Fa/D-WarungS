-- ====================================================
-- DATABASE SISTEM PEMESANAN KANTIN MULTI-PEDAGANG
-- ====================================================

-- Drop existing database jika ada
DROP DATABASE IF EXISTS d_warung;

-- Create database
CREATE DATABASE d_warung;
USE d_warung;

-- ====================================================
-- TABLE USERS
-- ====================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('pembeli', 'pedagang', 'kasir') NOT NULL DEFAULT 'pembeli',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================
-- TABLE WARUNG
-- ====================================================
CREATE TABLE warung (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_warung VARCHAR(100) NOT NULL,
    pemilik_id INT NOT NULL,
    deskripsi TEXT,
    nomor_telepon VARCHAR(15),
    alamat TEXT,
    jam_buka TIME,
    jam_tutup TIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pemilik_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_pemilik_id (pemilik_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================
-- TABLE MENU
-- ====================================================
CREATE TABLE menu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    warung_id INT NOT NULL,
    nama_menu VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    harga INT NOT NULL,
    stok INT NOT NULL DEFAULT 0,
    gambar VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (warung_id) REFERENCES warung(id) ON DELETE CASCADE,
    INDEX idx_warung_id (warung_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================
-- TABLE ORDERS
-- ====================================================
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pembeli_id INT NOT NULL,
    status ENUM('menunggu', 'dibayar', 'diproses', 'siap', 'selesai', 'batal') NOT NULL DEFAULT 'menunggu',
    total_harga INT NOT NULL,
    catatan TEXT,
    waktu_pesan DATETIME DEFAULT CURRENT_TIMESTAMP,
    waktu_selesai DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pembeli_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_pembeli_id (pembeli_id),
    INDEX idx_status (status),
    INDEX idx_waktu_pesan (waktu_pesan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================
-- TABLE ORDER_ITEMS
-- ====================================================
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    menu_id INT NOT NULL,
    qty INT NOT NULL,
    harga_satuan INT NOT NULL,
    subtotal INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_id) REFERENCES menu(id),
    INDEX idx_order_id (order_id),
    INDEX idx_menu_id (menu_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================
-- INSERT DUMMY DATA
-- ====================================================

-- Password dummy: Semua menggunakan "password123" yang di-hash dengan bcrypt
-- VERIFIED HASH untuk "password123" yang VALID dan TESTED

INSERT INTO users (nama, email, password, role) VALUES
('Budi Santoso', 'budi@example.com', '$2y$10$KPmQXu4z1cdubRAE92cdau.oFNbOtyTY8C3y770gwONkkGXQO.KY6', 'pembeli'),
('Siti Nurhaliza', 'siti@example.com', '$2y$10$KPmQXu4z1cdubRAE92cdau.oFNbOtyTY8C3y770gwONkkGXQO.KY6', 'pembeli'),
('Ahmad Wijaya', 'ahmad@example.com', '$2y$10$KPmQXu4z1cdubRAE92cdau.oFNbOtyTY8C3y770gwONkkGXQO.KY6', 'pembeli'),
('Dewi Lestari', 'dewi@example.com', '$2y$10$KPmQXu4z1cdubRAE92cdau.oFNbOtyTY8C3y770gwONkkGXQO.KY6', 'pembeli'),
('Rini Kusuma', 'rini.pedagang@example.com', '$2y$10$KPmQXu4z1cdubRAE92cdau.oFNbOtyTY8C3y770gwONkkGXQO.KY6', 'pedagang'),
('Hendra Gunawan', 'hendra.pedagang@example.com', '$2y$10$KPmQXu4z1cdubRAE92cdau.oFNbOtyTY8C3y770gwONkkGXQO.KY6', 'pedagang'),
('Susi Rahayu', 'susi.pedagang@example.com', '$2y$10$KPmQXu4z1cdubRAE92cdau.oFNbOtyTY8C3y770gwONkkGXQO.KY6', 'pedagang'),
('Tono Kasir', 'tono.kasir@example.com', '$2y$10$KPmQXu4z1cdubRAE92cdau.oFNbOtyTY8C3y770gwONkkGXQO.KY6', 'kasir'),
('Rina Kasir', 'rina.kasir@example.com', '$2y$10$KPmQXu4z1cdubRAE92cdau.oFNbOtyTY8C3y770gwONkkGXQO.KY6', 'kasir');

-- Wajib ubah user_id sesuai dengan ID yang di-generate
INSERT INTO warung (nama_warung, pemilik_id, deskripsi, nomor_telepon, alamat, jam_buka, jam_tutup) VALUES
('Warung Rini Nasi Kuning', 5, 'Warung nasi kuning terpopuler di kantin, cita rasa autentik Sumatera', '08123456789', 'Kantin Blok A', '06:00:00', '17:00:00'),
('Warung Hendra Soto Ayam', 6, 'Soto ayam tradisional dengan resep turun temurun', '08198765432', 'Kantin Blok B', '06:30:00', '17:00:00'),
('Warung Susi Bakso Premium', 7, 'Bakso sapi premium dengan kuah gurih yang menggugah selera', '08111222333', 'Kantin Blok C', '07:00:00', '17:30:00');

-- Insert menu untuk Warung Rini
INSERT INTO menu (warung_id, nama_menu, deskripsi, harga, stok, gambar) VALUES
(1, 'Nasi Kuning Biasa', 'Nasi kuning dengan porsi standar, cocok untuk makan siang', 15000, 20, 'nasi_kuning.jpg'),
(1, 'Nasi Kuning Jumbo', 'Nasi kuning dengan porsi lebih besar untuk yang lapar', 20000, 15, 'nasi_kuning_jumbo.jpg'),
(1, 'Nasi Kuning + Ayam Goreng', 'Paket hemat dengan ayam goreng crispy', 25000, 10, 'nasi_kuning_ayam.jpg'),
(1, 'Nasi Kuning + Telur Ceplok', 'Nasi kuning dengan telur ceplok matang sempurna', 18000, 25, 'nasi_kuning_telur.jpg'),
(1, 'Nasi Kuning + Lalapan', 'Nasi kuning dengan sayuran segar dan sambal pedas', 16000, 18, 'nasi_kuning_lalapan.jpg');

-- Insert menu untuk Warung Hendra
INSERT INTO menu (warung_id, nama_menu, deskripsi, harga, stok, gambar) VALUES
(2, 'Soto Ayam Biasa', 'Soto ayam tradisional dengan rempah lengkap', 12000, 25, 'soto_ayam.jpg'),
(2, 'Soto Ayam + Nasi', 'Soto ayam lengkap dengan nasi putih hangat', 17000, 20, 'soto_ayam_nasi.jpg'),
(2, 'Soto Ayam Jumbo', 'Soto ayam dengan porsi daging ayam lebih banyak', 18000, 12, 'soto_ayam_jumbo.jpg'),
(2, 'Paket Soto Keluarga', 'Soto ayam untuk 3-4 orang, hemat dan bisa dinikmati bersama', 40000, 8, 'soto_keluarga.jpg');

-- Insert menu untuk Warung Susi
INSERT INTO menu (warung_id, nama_menu, deskripsi, harga, stok, gambar) VALUES
(3, 'Bakso Sapi Biasa', 'Bakso sapi dengan kuah kaldu yang gurih dan menyeduhkan', 14000, 30, 'bakso_biasa.jpg'),
(3, 'Bakso Sapi Premium', 'Bakso sapi premium dengan bola bakso yang besar-besar', 18000, 20, 'bakso_premium.jpg'),
(3, 'Bakso Sapi + Perkedel', 'Bakso sapi dengan perkedel goreng yang crispy', 17000, 15, 'bakso_perkedel.jpg'),
(3, 'Soto Babat', 'Soto dengan potongan babat yang lezat dan empuk', 16000, 10, 'soto_babat.jpg'),
(3, 'Paket Makan Hemat', 'Bakso dengan nasi, sayur, dan minuman', 21000, 25, 'paket_hemat.jpg');

-- ====================================================
-- INSERT SAMPLE ORDERS (opsional untuk testing)
-- ====================================================

INSERT INTO orders (pembeli_id, status, total_harga, catatan) VALUES
(1, 'menunggu', 37000, 'Pesan untuk makan di tempat'),
(1, 'dibayar', 25000, NULL),
(2, 'diproses', 31000, 'Tanpa sambal'),
(2, 'siap', 40000, 'Pesan untuk dibawa pulang'),
(3, 'menunggu', 50000, 'Untuk acara rapat');

-- Insert order items untuk order pertama
INSERT INTO order_items (order_id, menu_id, qty, harga_satuan, subtotal) VALUES
(1, 1, 1, 15000, 15000),
(1, 4, 1, 18000, 18000),
(1, 12, 1, 14000, 14000);

-- Insert order items untuk order kedua
INSERT INTO order_items (order_id, menu_id, qty, harga_satuan, subtotal) VALUES
(2, 3, 1, 25000, 25000);

-- Insert order items untuk order ketiga
INSERT INTO order_items (order_id, menu_id, qty, harga_satuan, subtotal) VALUES
(3, 6, 1, 17000, 17000),
(3, 13, 1, 14000, 14000);

-- Insert order items untuk order keempat
INSERT INTO order_items (order_id, menu_id, qty, harga_satuan, subtotal) VALUES
(4, 9, 2, 20000, 40000);

-- Insert order items untuk order kelima
INSERT INTO order_items (order_id, menu_id, qty, harga_satuan, subtotal) VALUES
(5, 14, 2, 21000, 42000),
(5, 7, 1, 12000, 12000);

-- ====================================================
-- VERIFIKASI DATA
-- ====================================================

-- Tampilkan ringkasan data
SELECT 'Users' as 'Data', COUNT(*) as 'Jumlah' FROM users
UNION ALL
SELECT 'Warung', COUNT(*) FROM warung
UNION ALL
SELECT 'Menu', COUNT(*) FROM menu
UNION ALL
SELECT 'Orders', COUNT(*) FROM orders
UNION ALL
SELECT 'Order Items', COUNT(*) FROM order_items;
