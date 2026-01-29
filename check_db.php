<?php
require_once 'includes/config.php';


echo "=== PAYMENTS TABLE STRUCTURE ===\n";
$result = $conn->query('DESCRIBE payments');
if($result) {
    echo "Payments table found. Columns:\n";
    while($row = $result->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} else {
    echo "ERROR: " . $conn->error . "\n";
    echo "Payments table may not exist.\n";
}
?>

