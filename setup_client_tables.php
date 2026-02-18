<?php
/**
 * Run this file to create the additional tables needed for client module
 */
require_once __DIR__ . '/includes/config.php';

// Read and execute the SQL file
$sql_file = __DIR__ . '/database/client_module_tables.sql';
$sql_content = file_get_contents($sql_file);

// Split by semicolons to execute multiple statements
$statements = array_filter(array_map('trim', explode(';', $sql_content)));

$success_count = 0;
$error_count = 0;

echo "<h2>Creating Client Module Tables...</h2>";

foreach ($statements as $statement) {
    if (!empty($statement) && !str_starts_with(trim($statement), '--')) {
        try {
            $conn->query($statement);
            $success_count++;
            echo "<p style='color: green;'>✓ Executed: " . substr($statement, 0, 100) . "...</p>";
        } catch (Exception $e) {
            $error_count++;
            echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
            echo "<p>Statement: " . substr($statement, 0, 100) . "...</p>";
        }
    }
}

echo "<h3>Summary:</h3>";
echo "<p>Successful: $success_count</p>";
echo "<p>Errors: $error_count</p>";

if ($error_count === 0) {
    echo "<p style='color: green; font-weight: bold;'>All tables created successfully! You can now use the client module.</p>";
}
?>