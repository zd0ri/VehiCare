<?php
/**
 * Database Configuration for VehiCare System
 */

// Database Connection Details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vehicare_db";

// Create Connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check Connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8");

// Define Base URL
define('BASE_URL', 'http://localhost/vehicare_db');
define('ADMIN_URL', 'http://localhost/vehicare_db/admins');

?>
