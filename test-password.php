<?php
/**
 * Test Password Verification
 * File ini untuk memverifikasi bahwa password hashing bekerja dengan benar
 * Hapus file ini setelah testing!
 */

header('Content-Type: application/json');

$password = 'password123';
$hash = '$2y$10$V9yq0wyNqqlRCpCmM63LoORgXwHYKB42x0cUeFqghS3ppbPKNpPWy';

$result = [
    'password' => $password,
    'hash' => $hash,
    'password_verify_result' => password_verify($password, $hash),
    'php_version' => phpversion(),
    'test_hash_generation' => password_hash($password, PASSWORD_BCRYPT),
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
