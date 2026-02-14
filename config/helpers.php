<?php
/**
 * General Helper Functions
 */

/**
 * Handles file uploads with validation for size, extension, and MIME type.
 *
 * @param array $file The $_FILES['input_name'] array.
 * @param string $upload_dir The destination directory.
 * @param string $new_filename_prefix A prefix for the new unique filename.
 * @return array ['success' => bool, 'filename' => string|null, 'error' => string|null]
 */
function handleFileUpload($file, $upload_dir, $new_filename_prefix) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Tidak ada file yang diunggah atau terjadi kesalahan.'];
    }

    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $allowed_mime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB

    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $file_size = $file['size'];
    
    // Security: Check MIME type from the file content itself, not just the extension
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $file_mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($file_ext, $allowed_ext)) {
        return ['success' => false, 'error' => 'Format file tidak diizinkan! Gunakan: JPG, PNG, GIF, atau WebP.'];
    }
    if (!in_array($file_mime, $allowed_mime)) {
        return ['success' => false, 'error' => 'Tipe file tidak valid.'];
    }
    if ($file_size > $max_size) {
        return ['success' => false, 'error' => 'Ukuran file terlalu besar! Maksimal 5MB.'];
    }

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $filename = $new_filename_prefix . '_' . time() . '.' . $file_ext;
    $filepath = $upload_dir . $filename;

    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename];
    } else {
        return ['success' => false, 'error' => 'Gagal menyimpan file! Periksa izin folder.'];
    }
}

/**
 * Renders star rating display from a number.
 *
 * @param float $rating The rating value (e.g., 4.5).
 * @param int $max The maximum rating (usually 5).
 * @return string The HTML for the stars.
 */
function renderStars($rating, $max = 5) {
    $full_star = '★';
    $half_star = '½';
    $empty_star = '☆';
    $html = '';

    $rating = round($rating * 2) / 2; // Round to nearest 0.5
    $full_stars = floor($rating);
    $has_half_star = ($rating - $full_stars) >= 0.5;
    $empty_stars = $max - $full_stars - ($has_half_star ? 1 : 0);

    $html .= str_repeat($full_star, $full_stars);
    if ($has_half_star) $html .= $half_star;
    $html .= str_repeat($empty_star, $empty_stars);

    return $html;
}