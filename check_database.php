<?php
require_once 'includes/config.php';

echo "=== VehiCare Database Structure Check ===\n\n";

// Check if database connection works
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . "\n");
}

echo "✓ Database connection successful\n\n";

// Show all tables
$result = $conn->query("SHOW TABLES");
if ($result) {
    echo "Existing tables:\n";
    while ($row = $result->fetch_array()) {
        echo "- " . $row[0] . "\n";
    }
} else {
    echo "Error showing tables: " . $conn->error . "\n";
}

echo "\n=== Checking for Client Module Tables ===\n";

$client_tables = [
    'reviews',
    'payments', 
    'notifications',
    'maintenance_reminders',
    'user_preferences',
    'client_activity_logs'
];

$missing_tables = [];
foreach ($client_tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echo "✓ $table - EXISTS\n";
    } else {
        echo "✗ $table - MISSING\n";
        $missing_tables[] = $table;
    }
}

if (count($missing_tables) > 0) {
    echo "\n=== Missing Tables Found ===\n";
    echo "The following tables need to be created:\n";
    foreach ($missing_tables as $table) {
        echo "- $table\n";
    }
    echo "\nRunning setup script...\n";
} else {
    echo "\n✓ All client module tables exist!\n";
}

echo "\n=== Checking invoices table for client_id column ===\n";
$result = $conn->query("DESCRIBE invoices");
if ($result) {
    $has_client_id = false;
    echo "Invoices table structure:\n";
    while ($row = $result->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
        if ($row['Field'] === 'client_id') {
            $has_client_id = true;
        }
    }
    
    if (!$has_client_id) {
        echo "\n✗ client_id column is MISSING from invoices table\n";
        echo "Adding client_id column...\n";
        
        $add_column = "ALTER TABLE invoices ADD COLUMN client_id INT(11) NOT NULL AFTER invoice_id";
        if ($conn->query($add_column)) {
            echo "✓ Added client_id column to invoices table\n";
            
            // Add foreign key constraint
            $add_fk = "ALTER TABLE invoices ADD CONSTRAINT fk_invoice_client FOREIGN KEY (client_id) REFERENCES users(user_id)";
            if ($conn->query($add_fk)) {
                echo "✓ Added foreign key constraint\n";
            } else {
                echo "✗ Failed to add foreign key: " . $conn->error . "\n";
            }
        } else {
            echo "✗ Failed to add client_id column: " . $conn->error . "\n";
        }
    } else {
        echo "\n✓ client_id column EXISTS in invoices table\n";
    }
} else {
    echo "✗ Could not check invoices table structure: " . $conn->error . "\n";
}

$conn->close();
?>