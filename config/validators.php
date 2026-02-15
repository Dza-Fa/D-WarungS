<?php
/**
 * Input Validators
 * Centralized validation untuk mengurangi duplikasi
 */

/**
 * Validate email format
 * @param string $email
 * @return bool
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Check if email exists
 * @param string $email
 * @param int $excludeUserId
 * @return bool
 */
function emailExists($email, $excludeUserId = 0) {
    $user = getRow("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $excludeUserId]);
    return $user !== null;
}

/**
 * Validate password strength
 * @param string $password
 * @return array
 */
function validatePassword($password) {
    if (strlen($password) < 6) {
        return ['valid' => false, 'message' => 'Password minimal 6 karakter!'];
    }
    return ['valid' => true];
}

/**
 * Validate profile update input
 * @param string $nama
 * @param string $email
 * @param int $excludeUserId
 * @return array ['valid' => bool, 'errors' => array]
 */
function validateProfileUpdate($nama, $email, $excludeUserId = 0)
{
    $errors = [];
    
    $nama = trim($nama);
    $email = trim($email);
    
    if (empty($nama)) {
        $errors[] = 'Nama harus diisi!';
    } elseif (strlen($nama) < 3) {
        $errors[] = 'Nama minimal 3 karakter!';
    } elseif (strlen($nama) > 100) {
        $errors[] = 'Nama maksimal 100 karakter!';
    }
    
    if (empty($email)) {
        $errors[] = 'Email harus diisi!';
    } elseif (!isValidEmail($email)) {
        $errors[] = 'Format email tidak valid!';
    } elseif (emailExists($email, $excludeUserId)) {
        $errors[] = 'Email sudah digunakan!';
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Validate password change input
 * @param string $currentPassword
 * @param string $newPassword
 * @param string $confirmPassword
 * @param string $hashedPassword
 * @return array ['valid' => bool, 'errors' => array]
 */
function validatePasswordChange($currentPassword, $newPassword, $confirmPassword, $hashedPassword)
{
    $errors = [];
    
    // Check all fields filled
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $errors[] = 'Semua field password harus diisi!';
        return [
            'valid' => false,
            'errors' => $errors
        ];
    }
    
    // Verify current password
    if (!password_verify($currentPassword, $hashedPassword)) {
        $errors[] = 'Password lama salah!';
        return [
            'valid' => false,
            'errors' => $errors
        ];
    }
    
    // Check new password confirmation
    if ($newPassword !== $confirmPassword) {
        $errors[] = 'Password baru tidak cocok!';
    }
    
    // Check password strength
    $strength = validatePassword($newPassword);
    if (!$strength['valid']) {
        $errors[] = $strength['message'];
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Validate menu input
 * @param string $nama_menu
 * @param string $harga
 * @param string $stok
 * @return array ['valid' => bool, 'errors' => array]
 */
function validateMenuInput($nama_menu, $harga, $stok)
{
    $errors = [];
    
    $nama_menu = trim($nama_menu);
    $harga = intval($harga);
    $stok = intval($stok);
    
    if (empty($nama_menu)) {
        $errors[] = 'Nama menu harus diisi!';
    } elseif (strlen($nama_menu) < 3) {
        $errors[] = 'Nama menu minimal 3 karakter!';
    } elseif (strlen($nama_menu) > 100) {
        $errors[] = 'Nama menu maksimal 100 karakter!';
    }
    
    if ($harga <= 0) {
        $errors[] = 'Harga harus lebih dari 0!';
    }
    
    if ($stok < 0) {
        $errors[] = 'Stok tidak boleh negatif!';
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors,
        'data' => [
            'nama_menu' => $nama_menu,
            'harga' => $harga,
            'stok' => $stok
        ]
    ];
}

/**
 * Validate warung update input
 * @param string $nama_warung
 * @param string $deskripsi
 * @param string $alamat
 * @param string $nomor_telepon
 * @return array ['valid' => bool, 'errors' => array]
 */
function validateWarungUpdate($nama_warung, $deskripsi = '', $alamat = '', $nomor_telepon = '')
{
    $errors = [];
    
    $nama_warung = trim($nama_warung);
    $deskripsi = trim($deskripsi);
    $alamat = trim($alamat);
    $nomor_telepon = trim($nomor_telepon);
    
    if (empty($nama_warung)) {
        $errors[] = 'Nama warung harus diisi!';
    } elseif (strlen($nama_warung) < 3) {
        $errors[] = 'Nama warung minimal 3 karakter!';
    } elseif (strlen($nama_warung) > 100) {
        $errors[] = 'Nama warung maksimal 100 karakter!';
    }
    
    if (!empty($nomor_telepon) && !preg_match('/^[\d\-\+\s]+$/', $nomor_telepon)) {
        $errors[] = 'Format nomor telepon tidak valid!';
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors,
        'data' => [
            'nama_warung' => $nama_warung,
            'deskripsi' => $deskripsi,
            'alamat' => $alamat,
            'nomor_telepon' => $nomor_telepon
        ]
    ];
}

/**
 * Validate rating input
 * @param int $rating
 * @param string $review
 * @return array ['valid' => bool, 'errors' => array]
 */
function validateRating($rating, $review = '')
{
    $errors = [];
    
    $rating = intval($rating);
    $review = trim($review);
    
    if ($rating < 1 || $rating > 5) {
        $errors[] = 'Rating harus antara 1-5!';
    }
    
    if (strlen($review) > 500) {
        $errors[] = 'Review maksimal 500 karakter!';
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors,
        'data' => [
            'rating' => $rating,
            'review' => $review
        ]
    ];
}

/**
 * Validate order status update
 * @param string $status
 * @param array $allowedStatuses
 * @return array ['valid' => bool, 'error' => string]
 */
function validateOrderStatus($status, $allowedStatuses = ['dibayar', 'diproses', 'siap'])
{
    $status = trim($status);
    
    if (!in_array($status, $allowedStatuses)) {
        return [
            'valid' => false,
            'error' => 'Status tidak valid!'
        ];
    }
    
    return [
        'valid' => true,
        'error' => ''
    ];
}

/**
 * Validate search input
 * @param string $search
 * @param int $minLength
 * @param int $maxLength
 * @return array ['valid' => bool, 'value' => string, 'error' => string]
 */
function validateSearch($search, $minLength = 1, $maxLength = 100)
{
    $search = trim($search);
    
    if (empty($search)) {
        return [
            'valid' => true,
            'value' => '',
            'error' => ''
        ];
    }
    
    if (strlen($search) < $minLength || strlen($search) > $maxLength) {
        return [
            'valid' => false,
            'value' => $search,
            'error' => 'Search query harus antara ' . $minLength . '-' . $maxLength . ' karakter!'
        ];
    }
    
    return [
        'valid' => true,
        'value' => $search,
        'error' => ''
    ];
}

/**
 * Display validation errors
 * @param array $errors
 */
function displayValidationErrors($errors)
{
    if (empty($errors)) {
        return;
    }
    
    echo '<div class="alert alert-danger">';
    echo '✗ Terjadi kesalahan:<br>';
    echo '<ul style="margin: 0.5rem 0 0 1.5rem;">';
    foreach ($errors as $error) {
        echo '<li>' . esc($error) . '</li>';
    }
    echo '</ul>';
    echo '</div>';
}
