<?php
/**
 * Fix Missing Columns in VehiCare Database
 * This script adds missing columns to existing tables
 */

require_once 'includes/config.php';

echo "=== Fixing VehiCare Database Schema ===\n\n";

try {
    // Check if invoices table exists and what columns it has
    echo "Checking invoices table structure...\n";
    $result = $conn->query("DESCRIBE invoices");
    
    if ($result) {
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
            echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
        }
        
        echo "\nChecking for missing columns...\n";
        
        // Check if client_id column exists
        if (!in_array('client_id', $columns)) {
            echo "Adding client_id column to invoices table...\n";
            $conn->query("ALTER TABLE invoices ADD COLUMN client_id INT(11) NOT NULL DEFAULT 1 AFTER invoice_id");
            echo "✓ Added client_id column\n";
        } else {
            echo "✓ client_id column exists\n";
        }
        
        // Check if payment_status column exists
        if (!in_array('payment_status', $columns)) {
            echo "Adding payment_status column to invoices table...\n";
            $conn->query("ALTER TABLE invoices ADD COLUMN payment_status ENUM('unpaid', 'partial', 'paid') DEFAULT 'unpaid'");
            echo "✓ Added payment_status column\n";
        } else {
            echo "✓ payment_status column exists\n";
        }
        
        // Check if subtotal column exists
        if (!in_array('subtotal', $columns)) {
            echo "Adding subtotal column to invoices table...\n";
            $conn->query("ALTER TABLE invoices ADD COLUMN subtotal DECIMAL(10,2) DEFAULT 0.00");
            echo "✓ Added subtotal column\n";
        } else {
            echo "✓ subtotal column exists\n";
        }
        
        // Check if tax_amount column exists
        if (!in_array('tax_amount', $columns)) {
            echo "Adding tax_amount column to invoices table...\n";
            $conn->query("ALTER TABLE invoices ADD COLUMN tax_amount DECIMAL(10,2) DEFAULT 0.00");
            echo "✓ Added tax_amount column\n";
        } else {
            echo "✓ tax_amount column exists\n";
        }
        
        // Check if grand_total column exists
        if (!in_array('grand_total', $columns)) {
            echo "Adding grand_total column to invoices table...\n";
            $conn->query("ALTER TABLE invoices ADD COLUMN grand_total DECIMAL(10,2) NOT NULL DEFAULT 0.00");
            echo "✓ Added grand_total column\n";
        } else {
            echo "✓ grand_total column exists\n";
        }
        
    } else {
        echo "Invoices table doesn't exist, creating it...\n";
        $conn->query("
            CREATE TABLE invoices (
                invoice_id INT(11) PRIMARY KEY AUTO_INCREMENT,
                client_id INT(11) NOT NULL,
                appointment_id INT(11) DEFAULT NULL,
                invoice_date DATE NOT NULL,
                subtotal DECIMAL(10,2) DEFAULT 0.00,
                tax_amount DECIMAL(10,2) DEFAULT 0.00,
                grand_total DECIMAL(10,2) NOT NULL,
                payment_status ENUM('unpaid', 'partial', 'paid') DEFAULT 'unpaid',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        echo "✓ Created invoices table\n";
    }
    
    echo "\n=== Testing Fixed Database ===\n";
    
    // Test the queries that were failing
    $test_query = "SELECT COUNT(*) as count FROM invoices WHERE payment_status = 'unpaid'";
    $result = $conn->query($test_query);
    if ($result) {
        $count = $result->fetch_assoc()['count'];
        echo "✓ payment_status query works: $count unpaid invoices\n";
    }
    
    $test_query2 = "SELECT COUNT(*) as count FROM invoices WHERE client_id = 1";
    $result2 = $conn->query($test_query2);
    if ($result2) {
        $count2 = $result2->fetch_assoc()['count'];
        echo "✓ client_id query works: $count2 invoices for client 1\n";
    }
    
    echo "\n🎉 Database schema is now fixed!\n";
    echo "✅ Client dashboard should work without errors now.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

$conn->close();
?>