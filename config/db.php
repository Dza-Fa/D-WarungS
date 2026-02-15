<?php
/**
 * Database Configuration File
 * Koneksi Database dengan Prepared Statement
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'd_warung');

// Error Reporting (disable di production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

/**
 * Connect to Database
 * @return mysqli object
 */
function getDBConnection()
{
    static $conn = null;
    
    if ($conn === null) {
        // Create connection
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        
        // Set charset to utf8mb4
        $conn->set_charset("utf8mb4");
    }
    
    return $conn;
}

// Get database connection
$conn = getDBConnection();

/**
 * Execute prepared statement query
 * @param string $query SQL query with ? placeholders
 * @param array $params Parameters to bind
 * @param string $types Types of parameters (s=string, i=integer, d=double, b=blob)
 * @return mysqli_result|bool Query result
 */
function executeQuery($query, $params = [], $types = '')
{
    $conn = getDBConnection();
    
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    
    // Bind parameters if provided
    if (!empty($params)) {
        // Auto-detect types if not provided
        if (empty($types)) {
            $types = '';
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_double($param)) {
                    $types .= 'd';
                } elseif (is_string($param)) {
                    $types .= 's';
                } elseif (is_bool($param)) {
                    $types .= 'i'; // MySQL uses tinyint(1) for boolean
                } else {
                    $types .= 's';
                }
            }
        }
        
        // Fix: bind_param requires references, not values
        $bind_params = [];
        foreach ($params as $key => $value) {
            $bind_params[$key] = &$params[$key];
        }
        $stmt->bind_param($types, ...$bind_params);
    }
    
    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }
    
    return $stmt;
}

/**
 * Get single row result
 * @param string $query SQL query with ? placeholders
 * @param array $params Parameters to bind
 * @return array|null Associative array or null
 */
function getRow($query, $params = [])
{
    $stmt = executeQuery($query, $params);
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row;
}

/**
 * Get all rows result
 * @param string $query SQL query with ? placeholders
 * @param array $params Parameters to bind
 * @return array Array of results
 */
function getRows($query, $params = [])
{
    $stmt = executeQuery($query, $params);
    $result = $stmt->get_result();
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    $stmt->close();
    return $rows;
}

/**
 * Execute insert, update, atau delete
 * @param string $query SQL query with ? placeholders
 * @param array $params Parameters to bind
 * @return bool true if success
 */
function executeUpdate($query, $params = [])
{
    $stmt = executeQuery($query, $params);
    $affected = $stmt->affected_rows;
    $stmt->close();
    return $affected > 0;
}

/**
 * Alias untuk execute INSERT, UPDATE, DELETE (sama dengan executeUpdate)
 * @param string $query SQL query with ? placeholders
 * @param array $params Parameters to bind
 * @return bool true if success
 */
function execute($query, $params = [])
{
    return executeUpdate($query, $params);
}

/**
 * Get last inserted ID
 * @return int Last insert id
 */
function getLastInsertId()
{
    $conn = getDBConnection();
    return $conn->insert_id;
}

/**
 * Get affected rows count
 * @return int Affected rows count
 */
function getAffectedRows()
{
    $conn = getDBConnection();
    return $conn->affected_rows;
}

/**
 * Escape output untuk HTML
 * @param string $string String to escape
 * @return string Escaped string
 */
function esc($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Format currency to IDR
 * @param int $amount Amount to format
 * @return string Formatted amount
 */
function formatCurrency($amount)
{
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

/**
 * Format datetime
 * @param string $datetime Datetime string
 * @param string $format Format to use
 * @return string Formatted datetime
 */
function formatDateTime($datetime, $format = 'd M Y H:i')
{
    $time = strtotime($datetime);
    return date($format, $time);
}

// Include helper files
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/validators.php';
require_once __DIR__ . '/queries.php';
