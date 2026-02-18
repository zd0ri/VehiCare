<?php
/**
 * Database Configuration for VehiCare System
 */

// Database Connection Details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vehicare_db";

// Create MySQL Connection (legacy support)
$conn = new mysqli($servername, $username, $password, $dbname);

// Check Connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8");

// Create PDO Connection (for new features like audit logging)
try {
    $dsn = "mysql:host=$servername;dbname=$dbname;charset=utf8";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    die("PDO Connection failed: " . $e->getMessage());
}

// Define Base URL
define('BASE_URL', 'http://localhost/vehicare_db');
define('ADMIN_URL', 'http://localhost/vehicare_db/admins');

// Security Configuration
define('SESSION_TIMEOUT', 3600); // 1 hour
define('BCRYPT_COST', 12);
define('CSRF_TOKEN_LENGTH', 32);

// Include helper functions and middleware
require_once __DIR__ . '/../app/helpers/auth_helpers.php';

// CSRF Token Functions
if (!function_exists('generate_csrf_token')) {
    function generate_csrf_token() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('verify_csrf_token')) {
    function verify_csrf_token($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

// Logging function
if (!function_exists('log_event')) {
    function log_event($level, $message, $context = []) {
        $log_entry = date('Y-m-d H:i:s') . " [{$level}] {$message}";
        if (!empty($context)) {
            $log_entry .= " Context: " . json_encode($context);
        }
        error_log($log_entry);
    }
}

// Initialize audit logging if middleware exists
if (file_exists(__DIR__ . '/../app/middleware/AuditLogger.php')) {
    require_once __DIR__ . '/../app/middleware/AuditLogger.php';
}

?>
