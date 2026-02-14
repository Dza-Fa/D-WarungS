<?php
/**
 * Security Helper Functions
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generates a CSRF token and stores it in the session.
 *
 * @return string The generated token.
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validates the submitted CSRF token.
 *
 * @param string $token The token from the form submission.
 * @return bool True if valid, false otherwise.
 */
function validate_csrf_token($token) {
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }
    return true;
}

/**
 * Generates a hidden input field with the CSRF token.
 */
function csrf_field() {
    echo '<input type="hidden" name="csrf_token" value="' . generate_csrf_token() . '">';
}