<?php
/**
 * Database setup for authentication system
 * Run this once to create the users table
 */

require_once __DIR__ . '/includes/config.php';

// Create users table if it doesn't exist
$createUsersTable = "CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20),
  `role` enum('admin', 'staff', 'client') NOT NULL,
  `status` enum('active', 'inactive') DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if ($conn->query($createUsersTable)) {
    echo "Users table created successfully!<br>";
} else {
    echo "Error: " . $conn->error . "<br>";
}

// Create a test admin user if it doesn't exist
$testAdminEmail = 'admin@vehicare.com';
$testAdminPassword = password_hash('admin123', PASSWORD_BCRYPT);

$checkAdmin = $conn->query("SELECT user_id FROM users WHERE email = '$testAdminEmail'");
if ($checkAdmin->num_rows == 0) {
    $insertAdmin = "INSERT INTO users (username, email, password, full_name, role, status) 
                    VALUES ('admin', '$testAdminEmail', '$testAdminPassword', 'Admin User', 'admin', 'active')";
    
    if ($conn->query($insertAdmin)) {
        echo "Test admin user created successfully!<br>";
        echo "Email: admin@vehicare.com<br>";
        echo "Password: admin123<br>";
    } else {
        echo "Error creating admin: " . $conn->error . "<br>";
    }
} else {
    echo "Admin user already exists.<br>";
}

echo "Setup complete!";
?>
