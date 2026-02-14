<?php
/**
 * Database Query Helpers
 * Common queries untuk mengurangi duplikasi
 */

/**
 * Get user statistics
 * @param int $userId
 * @param string $userRole
 * @return array
 */
function getUserStatistics($userId, $userRole = 'pembeli')
{
    $stats = [];
    
    switch ($userRole) {
        case 'pembeli':
            $stats['total_pesanan'] = getRow(
                "SELECT COUNT(*) as count FROM orders WHERE pembeli_id = ?",
                [$userId]
            )['count'] ?? 0;
            
            $stats['total_pengeluaran'] = getRow(
                "SELECT SUM(total_harga) as total FROM orders WHERE pembeli_id = ?",
                [$userId]
            )['total'] ?? 0;
            
            $stats['pesanan_aktif'] = getRow(
                "SELECT COUNT(*) as count FROM orders WHERE pembeli_id = ? AND is_confirmed = 0",
                [$userId]
            )['count'] ?? 0;
            
            break;
            
        case 'pedagang':
            // Requires warung_id, handled separately
            break;
            
        case 'kasir':
            $stats['pembayaran_divalidasi'] = getRow(
                "SELECT COUNT(*) as count FROM orders WHERE status != 'menunggu'"
            )['count'] ?? 0;
            
            $stats['total_penjualan'] = getRow(
                "SELECT SUM(total_harga) as total FROM orders WHERE status IN ('dibayar', 'diproses', 'siap')"
            )['total'] ?? 0;
            
            $stats['pembayaran_menunggu'] = getRow(
                "SELECT COUNT(*) as count FROM orders WHERE status = 'menunggu'"
            )['count'] ?? 0;
            
            break;
    }
    
    return $stats;
}

/**
 * Get warung statistics
 * @param int $warungId
 * @return array
 */
function getWarungStatistics($warungId)
{
    return [
        'total_pesanan' => getRow(
            "SELECT COUNT(DISTINCT o.id) as count FROM orders o
            JOIN order_items oi ON o.id = oi.order_id
            JOIN menu m ON oi.menu_id = m.id
            WHERE m.warung_id = ?",
            [$warungId]
        )['count'] ?? 0,
        
        'total_penjualan' => getRow(
            // Perbaikan: Gunakan SUM(oi.subtotal) untuk menghindari perhitungan ganda jika
            // satu pesanan memiliki banyak item dari warung yang sama.
            // Tambahkan status 'selesai' agar konsisten dengan laporan penjualan.
            "SELECT SUM(oi.subtotal) as total
            FROM order_items oi
            JOIN menu m ON oi.menu_id = m.id
            JOIN orders o ON oi.order_id = o.id
            WHERE m.warung_id = ? AND o.status IN ('dibayar', 'diproses', 'siap', 'selesai')",
            [$warungId]
        )['total'] ?? 0,
        
        'total_menu' => getRow(
            "SELECT COUNT(*) as count FROM menu WHERE warung_id = ?",
            [$warungId]
        )['count'] ?? 0,
        
        'total_rating' => getRow(
            "SELECT COUNT(*) as count FROM ratings r
            JOIN menu m ON r.menu_id = m.id
            WHERE m.warung_id = ?",
            [$warungId]
        )['count'] ?? 0,
        
        'avg_rating' => getRow(
            "SELECT AVG(r.rating) as avg FROM ratings r
            JOIN menu m ON r.menu_id = m.id
            WHERE m.warung_id = ?",
            [$warungId]
        )['avg'] ?? 0
    ];
}

/**
 * Get order statistics for warung
 * @param int $warungId
 * @return array
 */
function getOrderStatistics($warungId)
{
    return [
        'dibayar' => getRow(
            "SELECT COUNT(DISTINCT o.id) as count FROM orders o
            JOIN order_items oi ON o.id = oi.order_id
            JOIN menu m ON oi.menu_id = m.id
            WHERE m.warung_id = ? AND o.status = ?",
            [$warungId, 'dibayar']
        )['count'] ?? 0,
        
        'diproses' => getRow(
            "SELECT COUNT(DISTINCT o.id) as count FROM orders o
            JOIN order_items oi ON o.id = oi.order_id
            JOIN menu m ON oi.menu_id = m.id
            WHERE m.warung_id = ? AND o.status = ?",
            [$warungId, 'diproses']
        )['count'] ?? 0,
        
        'siap' => getRow(
            "SELECT COUNT(DISTINCT o.id) as count FROM orders o
            JOIN order_items oi ON o.id = oi.order_id
            JOIN menu m ON oi.menu_id = m.id
            WHERE m.warung_id = ? AND o.status = ?",
            [$warungId, 'siap']
        )['count'] ?? 0
    ];
}

/**
 * Get unread notifications count
 * @param int $userId
 * @param string $role
 * @return int
 */
function getUnreadNotificationsCount($userId, $role = 'pembeli')
{
    $count = getRow(
        "SELECT COUNT(*) as count FROM notifications 
        WHERE user_id = ? AND is_read = 0 AND role = ?",
        [$userId, $role]
    );
    return $count['count'] ?? 0;
}

/**
 * Get notifications for user
 * @param int $userId
 * @param string $role
 * @param string|null $filter all|unread
 * @param int $limit
 * @return array
 */
function getNotifications($userId, $role = 'pembeli', $filter = 'all', $limit = 100)
{
    $where = "user_id = ? AND role = ?";
    $params = [$userId, $role];
    
    if ($filter === 'unread') {
        $where .= " AND is_read = 0";
    }
    
    return getRows(
        "SELECT * FROM notifications WHERE $where ORDER BY created_at DESC LIMIT ?",
        array_merge($params, [$limit])
    );
}

/**
 * Get warung by owner
 * @param int $userId
 * @return array|null
 */
function getWarungByOwner($userId)
{
    return getRow("SELECT * FROM warung WHERE pemilik_id = ?", [$userId]);
}

/**
 * Get menu for warung
 * @param int $warungId
 * @param string|null $search
 * @return array
 */
function getMenuByWarung($warungId, $search = null)
{
    if ($search) {
        $search_term = "%$search%";
        return getRows(
            "SELECT * FROM menu WHERE warung_id = ? AND (nama_menu LIKE ? OR deskripsi LIKE ?) ORDER BY nama_menu ASC",
            [$warungId, $search_term, $search_term]
        );
    }
    
    return getRows(
        "SELECT * FROM menu WHERE warung_id = ? ORDER BY nama_menu ASC",
        [$warungId]
    );
}

/**
 * Get top rated menu for warung
 * @param int $warungId
 * @param int $limit
 * @return array
 */
function getTopRatedMenu($warungId, $limit = 3)
{
    return getRows(
        "SELECT m.id, m.nama_menu, AVG(r.rating) as avg_rating, COUNT(r.id) as rating_count
        FROM menu m
        LEFT JOIN ratings r ON m.id = r.menu_id
        WHERE m.warung_id = ?
        GROUP BY m.id, m.nama_menu
        ORDER BY avg_rating DESC, rating_count DESC
        LIMIT ?",
        [$warungId, $limit]
    );
}

/**
 * Get orders for pembeli
 * @param int $pembeli_id
 * @param string $filter all|active|completed|status
 * @param string $status
 * @return array
 */
function getPembeliOrders($pembeli_id, $filter = 'active', $status = '')
{
    $where = "pembeli_id = ?";
    $params = [$pembeli_id];
    
    if ($filter === 'active') {
        $where .= " AND is_confirmed = 0";
    } elseif ($filter === 'completed') {
        $where .= " AND is_confirmed = 1";
    } elseif ($filter === 'status' && !empty($status)) {
        $where .= " AND status = ?";
        $params[] = $status;
    }
    
    return getRows(
        "SELECT * FROM orders WHERE $where ORDER BY waktu_pesan DESC",
        $params
    );
}

/**
 * Get orders for warung
 * @param int $warungId
 * @param string $filterStatus dibayar|diproses|siap
 * @param int|null $isConfirmed 0 for active, 1 for completed, null for all
 * @return array
 */
function getWarungOrders($warungId, $filterStatus = null, $isConfirmed = null)
{
    $where = "m.warung_id = ?";
    $params = [$warungId];
    
    // Filter by specific status or default active statuses
    $statusList = ['dibayar', 'diproses', 'siap'];
    if ($filterStatus && in_array($filterStatus, $statusList)) {
        $where .= " AND o.status = ?";
        $params[] = $filterStatus;
    } else {
        $where .= " AND o.status IN ('dibayar', 'diproses', 'siap')";
    }

    // Filter by confirmation status (pembeli terima barang)
    if ($isConfirmed !== null) {
        $where .= " AND o.is_confirmed = ?";
        $params[] = $isConfirmed;
    }
    
    return getRows(
        "SELECT DISTINCT o.* FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN menu m ON oi.menu_id = m.id
        WHERE $where
        ORDER BY 
            CASE o.status 
                WHEN 'dibayar' THEN 1
                WHEN 'diproses' THEN 2
                WHEN 'siap' THEN 3
                ELSE 4
            END,
            o.waktu_pesan ASC",
        $params
    );
}

/**
 * Get all warung with pagination
 * @param string|null $search
 * @param int $limit
 * @return array
 */
function getAllWarung($search = null, $limit = null)
{
    $query = "SELECT * FROM warung";
    $params = [];
    
    if ($search) {
        $query .= " WHERE nama_warung LIKE ? OR deskripsi LIKE ?";
        $search_term = "%$search%";
        $params = [$search_term, $search_term];
    }
    
    $query .= " ORDER BY nama_warung ASC";
    
    if ($limit) {
        $query .= " LIMIT ?";
        $params[] = $limit;
    }
    
    return getRows($query, $params);
}

/**
 * Get favorites for pembeli
 * @param int $pembeli_id
 * @param int $warungId untuk specific warung
 * @return array
 */
function getFavorites($pembeli_id, $warungId = null)
{
    if ($warungId) {
        return getRows(
            "SELECT m.* FROM menu m 
            JOIN favorites f ON m.id = f.menu_id 
            WHERE m.warung_id = ? AND f.pembeli_id = ?
            ORDER BY m.nama_menu ASC",
            [$warungId, $pembeli_id]
        );
    }
    
    return getRows(
        "SELECT f.*, m.nama_menu, m.harga, m.gambar, w.id as warung_id, w.nama_warung
        FROM favorites f
        JOIN menu m ON f.menu_id = m.id
        JOIN warung w ON m.warung_id = w.id
        WHERE f.pembeli_id = ?
        ORDER BY f.created_at DESC",
        [$pembeli_id]
    );
}

/**
 * Check if menu is favorite
 * @param int $pembeli_id
 * @param int $menu_id
 * @return bool
 */
function isFavorite($pembeli_id, $menu_id)
{
    $fav = getRow(
        "SELECT id FROM favorites WHERE pembeli_id = ? AND menu_id = ?",
        [$pembeli_id, $menu_id]
    );
    return $fav !== null;
}

/**
 * Toggle favorite
 * @param int $pembeli_id
 * @param int $menu_id
 * @return bool true jika ditambah, false jika dihapus
 */
function toggleFavorite($pembeli_id, $menu_id)
{
    $existing = getRow(
        "SELECT id FROM favorites WHERE pembeli_id = ? AND menu_id = ?",
        [$pembeli_id, $menu_id]
    );
    
    if ($existing) {
        return execute(
            "DELETE FROM favorites WHERE pembeli_id = ? AND menu_id = ?",
            [$pembeli_id, $menu_id]
        ) ? false : true;
    } else {
        return execute(
            "INSERT INTO favorites (pembeli_id, menu_id) VALUES (?, ?)",
            [$pembeli_id, $menu_id]
        );
    }
}

/**
 * Get unrated orders for a buyer
 * @param int $pembeliId
 * @return array
 */
function getUnratedOrders($pembeliId) {
    return getRows("
        SELECT orders.id, orders.waktu_pesan, orders.total_harga, GROUP_CONCAT(DISTINCT warung.nama_warung SEPARATOR ', ') as nama_warung
        FROM orders
        JOIN order_items ON orders.id = order_items.order_id
        JOIN menu ON order_items.menu_id = menu.id
        JOIN warung ON menu.warung_id = warung.id
        WHERE orders.pembeli_id = ? AND orders.is_confirmed = 1
        GROUP BY orders.id
        ORDER BY orders.waktu_pesan DESC
    ", [$pembeliId]);
}

/**
 * Save or update rating
 * @param int $orderId
 * @param int $menuId
 * @param int $pembeliId
 * @param int $rating
 * @param string $review
 * @return bool
 */
function saveRating($orderId, $menuId, $pembeliId, $rating, $review) {
    // Check existing
    $existing = getRow(
        "SELECT id FROM ratings WHERE order_id = ? AND menu_id = ?",
        [$orderId, $menuId]
    );
    
    if ($existing) {
        return execute(
            "UPDATE ratings SET rating = ?, review = ? WHERE order_id = ? AND menu_id = ?",
            [$rating, $review, $orderId, $menuId]
        );
    } else {
        return execute(
            "INSERT INTO ratings (order_id, pembeli_id, menu_id, rating, review) VALUES (?, ?, ?, ?, ?)",
            [$orderId, $pembeliId, $menuId, $rating, $review]
        );
    }
}

/**
 * Get Sales Report Statistics
 * @param string $startDate
 * @param string $endDate
 * @return array
 */
function getSalesReportStats($startDate, $endDate) {
    $base_query = "WHERE o.status IN ('dibayar', 'diproses', 'siap', 'selesai') AND DATE(o.waktu_pesan) BETWEEN ? AND ?";
    
    $total_penjualan = getRow("SELECT SUM(o.total_harga) as total FROM orders o $base_query", [$startDate, $endDate])['total'] ?? 0;
    $total_transaksi = getRow("SELECT COUNT(*) as count FROM orders o $base_query", [$startDate, $endDate])['count'] ?? 0;
    
    return [
        'total_penjualan' => $total_penjualan,
        'total_transaksi' => $total_transaksi,
        'rata_transaksi' => $total_transaksi > 0 ? $total_penjualan / $total_transaksi : 0
    ];
}

/**
 * Get Top Selling Menu
 * @param string $startDate
 * @param string $endDate
 * @param int $limit
 * @return array
 */
function getTopSellingMenus($startDate, $endDate, $limit = 10) {
    return getRows(
        "SELECT m.nama_menu, w.nama_warung, SUM(oi.qty) as total_qty, SUM(oi.subtotal) as total_penjualan
        FROM order_items oi
        JOIN menu m ON oi.menu_id = m.id
        JOIN warung w ON m.warung_id = w.id
        JOIN orders o ON oi.order_id = o.id
        WHERE o.status IN ('dibayar', 'diproses', 'siap', 'selesai') AND DATE(o.waktu_pesan) BETWEEN ? AND ?
        GROUP BY m.id
        ORDER BY total_qty DESC
        LIMIT ?",
        [$startDate, $endDate, $limit]
    );
}

/**
 * Get Sales by Warung
 * @param string $startDate
 * @param string $endDate
 * @return array
 */
function getSalesByWarung($startDate, $endDate) {
    return getRows(
        "SELECT w.id, w.nama_warung, SUM(o.total_harga) as total, COUNT(DISTINCT o.id) as count
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN menu m ON oi.menu_id = m.id
        JOIN warung w ON m.warung_id = w.id
        WHERE o.status IN ('dibayar', 'diproses', 'siap', 'selesai') AND DATE(o.waktu_pesan) BETWEEN ? AND ?
        GROUP BY w.id, w.nama_warung
        ORDER BY total DESC",
        [$startDate, $endDate]
    );
}

/**
 * Get Transaction Details for Sales Report
 * @param string $startDate
 * @param string $endDate
 * @return array
 */
function getTransactionsForReport($startDate, $endDate) {
    return getRows(
        "SELECT o.id, o.total_harga, o.waktu_pesan, o.status, u.nama, 
        (SELECT GROUP_CONCAT(m.nama_menu SEPARATOR ', ') FROM order_items oi JOIN menu m ON oi.menu_id = m.id WHERE oi.order_id = o.id) as items
        FROM orders o
        JOIN users u ON o.pembeli_id = u.id
        WHERE o.status IN ('dibayar', 'diproses', 'siap', 'selesai') AND DATE(o.waktu_pesan) BETWEEN ? AND ?
        ORDER BY o.waktu_pesan DESC",
        [$startDate, $endDate]
    );
}
