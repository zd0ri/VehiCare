<?php
require_once 'includes/config.php';

echo "=== Testing VehiCare Client Module Database ===\n\n";

try {
    // Test invoices table
    $result = $conn->query("SELECT COUNT(*) as count FROM invoices");
    $count = $result->fetch_assoc()['count'];
    echo "✓ Invoices table: $count records\n";
    
    // Test payments table
    $result = $conn->query("SELECT COUNT(*) as count FROM payments");
    $count = $result->fetch_assoc()['count'];
    echo "✓ Payments table: $count records\n";
    
    // Test reviews table
    $result = $conn->query("SELECT COUNT(*) as count FROM reviews");
    $count = $result->fetch_assoc()['count'];
    echo "✓ Reviews table: $count records\n";
    
    // Test notifications table
    $result = $conn->query("SELECT COUNT(*) as count FROM notifications");
    $count = $result->fetch_assoc()['count'];
    echo "✓ Notifications table: $count records\n";
    
    echo "\n🎉 All client module tables are working!\n";
    echo "✅ You can now access: http://localhost/vehicare_db/client/dashboard.php\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>