<?php
/**
 * Database Configuration
 * VehiCare Service Management System
 * 
 * This file contains database connection settings and creates a global
 * PDO connection instance for use throughout the application.
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'vehicare_db');
define('DB_USER', 'root');  // Change as needed for production
define('DB_PASS', '');      // Change as needed for production
define('DB_CHARSET', 'utf8mb4');

// Application Configuration
define('APP_NAME', 'VehiCare');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/vehicare_db');

// Security Configuration
define('SESSION_TIMEOUT', 3600); // 1 hour
define('BCRYPT_COST', 12);
define('CSRF_TOKEN_LENGTH', 32);

// File Upload Configuration
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx']);

// Logging Configuration
define('LOG_PATH', __DIR__ . '/../logs/');
define('LOG_LEVEL', 'INFO'); // DEBUG, INFO, WARNING, ERROR

// Email Configuration (for notifications)
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('FROM_EMAIL', 'noreply@vehicare.com');
define('FROM_NAME', 'VehiCare System');

try {
    // Create PDO connection with proper error handling
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_FOUND_ROWS   => true,
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
    // Set timezone
    $pdo->exec("SET time_zone = '+00:00'");
    
} catch (PDOException $e) {
    error_log("Database Connection Error: " . $e->getMessage());
    
    // In production, show generic error message
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'production') {
        die('Database connection failed. Please try again later.');
    } else {
        die('Database Error: ' . $e->getMessage());
    }
}

/**
 * Legacy mysqli connection for backward compatibility
 * This maintains compatibility with existing code while transitioning to PDO
 */
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    error_log("MySQLi Connection Error: " . $conn->connect_error);
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset(DB_CHARSET);

/**
 * Utility Functions
 */

/**
 * Sanitize input data
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Generate CSRF token
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Log system events
 */
function log_event($level, $message, $context = []) {
    $log_file = LOG_PATH . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = sprintf("[%s] %s: %s", $timestamp, strtoupper($level), $message);
    
    if (!empty($context)) {
        $log_entry .= ' | Context: ' . json_encode($context);
    }
    
    $log_entry .= PHP_EOL;
    
    // Ensure logs directory exists
    if (!is_dir(LOG_PATH)) {
        mkdir(LOG_PATH, 0755, true);
    }
    
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

/**
 * Send JSON response
 */
function json_response($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Redirect with message
 */
function redirect_with_message($url, $message, $type = 'info') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    header("Location: $url");
    exit;
}

/**
 * Get flash message
 */
function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = [
            'text' => $_SESSION['flash_message'],
            'type' => $_SESSION['flash_type'] ?? 'info'
        ];
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return $message;
    }
    return null;
}

// Include authentication helper functions
require_once __DIR__ . '/../app/helpers/auth_helpers.php';

// Initialize RBAC system
require_once __DIR__ . '/../app/middleware/RBAC.php';
?>