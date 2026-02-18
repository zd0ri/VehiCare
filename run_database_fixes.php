<?php
// Database Fix Script - Run this once to fix the database schema
require_once __DIR__ . '/includes/config.php';

echo "<h1>VehiCare Database Fix Script</h1>\n";
echo "<pre>\n";

// Read the SQL file content
$sqlFile = __DIR__ . '/database_fixes.sql';
$sqlCommands = file_get_contents($sqlFile);

// Remove comments and split into individual commands
$sqlCommands = preg_replace('/--.*$/m', '', $sqlCommands);
$commands = explode(';', $sqlCommands);

$successCount = 0;
$errorCount = 0;

foreach ($commands as $command) {
    $command = trim($command);
    if (empty($command)) continue;
    
    echo "Executing: " . substr($command, 0, 60) . "...\n";
    
    if ($conn->query($command)) {
        echo "✓ SUCCESS\n\n";
        $successCount++;
    } else {
        echo "✗ ERROR: " . $conn->error . "\n\n";
        $errorCount++;
    }
}

echo "\n=== SUMMARY ===\n";
echo "Successful commands: $successCount\n";
echo "Failed commands: $errorCount\n";

if ($errorCount == 0) {
    echo "\n🎉 ALL FIXES APPLIED SUCCESSFULLY!\n";
    echo "The database schema has been updated.\n";
    echo "You can now continue using the admin panel.\n";
} else {
    echo "\n⚠️  Some fixes failed. Please check the errors above.\n";
}

echo "</pre>\n";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Fix Script</title>
    <style>
        body { font-family: monospace; margin: 20px; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 5px; }
        h1 { color: #333; }
    </style>
</head>
<body>
    <p><strong>Note:</strong> After running this script successfully, you can delete this file for security.</p>
</body>
</html>