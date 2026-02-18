<?php
require_once __DIR__ . '/includes/config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Setup - VehiCare</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .success { color: green; }
        .error { color: red; }
        .btn { padding: 10px 20px; background: #dc143c; color: white; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <h2>Creating Missing Database Tables</h2>

<?php
// Create notifications table
$notifications_sql = "CREATE TABLE IF NOT EXISTS `notifications` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notification_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($notifications_sql) === TRUE) {
    echo "<p class='success'>✓ Table 'notifications' created successfully</p>";
} else {
    echo "<p class='error'>✗ Error creating notifications table: " . $conn->error . "</p>";
}

// Create walk_in_bookings table
$walk_in_sql = "CREATE TABLE IF NOT EXISTS `walk_in_bookings` (
  `booking_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `vehicle_info` varchar(255) NOT NULL,
  `service_id` int(11) DEFAULT NULL,
  `booking_date` date NOT NULL,
  `booking_time` time NOT NULL,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`booking_id`),
  KEY `service_id` (`service_id`),
  CONSTRAINT `walk_in_bookings_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($walk_in_sql) === TRUE) {
    echo "<p class='success'>✓ Table 'walk_in_bookings' created successfully</p>";
} else {
    echo "<p class='error'>✗ Error creating walk_in_bookings table: " . $conn->error . "</p>";
}

// Add some sample notifications
$sample_notifications = "INSERT IGNORE INTO `notifications` (`user_id`, `message`, `type`) VALUES 
(1, 'Welcome to VehiCare Admin Panel!', 'info'),
(1, 'New appointment scheduled', 'success'),
(1, 'System maintenance scheduled for tonight', 'warning')";

$conn->query($sample_notifications);

$conn->close();
?>

    <br>
    <p><strong>Database setup complete!</strong></p>
    <p><a href="admins/index.php" class="btn">Go to Admin Dashboard</a></p>
    <p><a href="admins/notifications.php" class="btn">Test Notifications Page</a></p>
    <p><a href="admins/walk_in_booking.php" class="btn">Test Walk-In Bookings</a></p>
</body>
</html>
