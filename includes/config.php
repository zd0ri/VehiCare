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

// Include helper functions and middleware
require_once __DIR__ . '/../app/helpers/Auth.php';

// Initialize audit logging if middleware exists
if (file_exists(__DIR__ . '/../app/middleware/AuditLogger.php')) {
    require_once __DIR__ . '/../app/middleware/AuditLogger.php';
}

?>
