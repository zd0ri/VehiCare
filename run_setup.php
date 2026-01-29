<?php


require_once __DIR__ . '/includes/config.php';

echo "=== VehiCare Database Setup ===\n\n";


$schema_file = __DIR__ . '/database_schema_complete.sql';
if (!file_exists($schema_file)) {
    die("ERROR: Schema file not found at " . $schema_file . "\n");
}

$sql_content = file_get_contents($schema_file);


$statements = array_filter(array_map('trim', explode(';', $sql_content)));

$success_count = 0;
$error_count = 0;
$errors = [];

foreach ($statements as $statement) {
    if (empty($statement) || strpos(trim($statement), '--') === 0) {
        continue;
    }
    
    echo "Executing: " . substr($statement, 0, 60) . "...\n";
    
    if ($conn->query($statement)) {
        $success_count++;
        echo "  âœ“ Success\n";
    } else {
        $error_count++;
        $error_msg = $conn->error;
        echo "  âœ— Error: " . $error_msg . "\n";
        
        
        if (strpos($error_msg, 'already exists') === false) {
            $errors[] = $error_msg;
        }
    }
}

echo "\n=== Setup Summary ===\n";
echo "Successful operations: $success_count\n";
echo "Errors (excluding existing tables): $error_count\n";

if (empty($errors)) {
    echo "\nâœ“ Database setup completed successfully!\n";
    echo "All tables are now created with the correct schema.\n";
} else {
    echo "\nâš  Some errors occurred:\n";
    foreach ($errors as $error) {
        echo "- $error\n";
    }
}


echo "\n=== Verifying payments table ===\n";
$result = $conn->query('DESCRIBE payments');
if ($result) {
    echo "Payments table columns:\n";
    while ($row = $result->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} else {
    echo "ERROR: Could not verify payments table: " . $conn->error . "\n";
}
?>

