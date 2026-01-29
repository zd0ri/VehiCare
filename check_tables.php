<?php
$conn = new mysqli('localhost', 'root', '', 'vehicare_db');
if ($conn->connect_error) die('Connection failed: ' . $conn->connect_error);

echo "=== Tables in database ===\n";
$result = $conn->query("SHOW TABLES");
while ($row = $result->fetch_row()) {
    echo $row[0] . "\n";
}

echo "\n=== Count records in client-related tables ===\n";
$tables = array('clients', 'users', 'customers');
foreach ($tables as $table) {
    $check = $conn->query("SELECT COUNT(*) as cnt FROM `" . $table . "`");
    if ($check) {
        $row = $check->fetch_assoc();
        echo $table . ": " . $row['cnt'] . " records\n";
    } else {
        echo $table . ": Table does not exist (Error: " . $conn->error . ")\n";
    }
}

echo "\n=== Show actual structure of relevant tables ===\n";
$check = $conn->query("DESCRIBE users");
if ($check && $check->num_rows > 0) {
    echo "\nUSERS table structure:\n";
    while ($col = $check->fetch_assoc()) {
        echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
}

$check = $conn->query("DESCRIBE clients");
if ($check && $check->num_rows > 0) {
    echo "\nCLIENTS table structure:\n";
    while ($col = $check->fetch_assoc()) {
        echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
}
?>
