<!DOCTYPE html>
<html>
<head>
    <title>VehiCare Database Auto-Fix</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .button { padding: 10px 20px; background: 
        .button:hover { background: 
        pre { background: 
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; }
    </style>
</head>
<body>
    <div class="container">
        <h1>VehiCare Database Auto-Fix</h1>
        
        <?php
        require_once __DIR__ . '/includes/config.php';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            echo '<h2>Applying Fixes</h2>';
            echo '<pre>';
            
            
            $result = $conn->query("DESCRIBE vehicles");
            $existing_columns = [];
            while ($row = $result->fetch_assoc()) {
                $existing_columns[] = $row['Field'];
            }
            
            echo "Current vehicles columns:\n";
            foreach ($existing_columns as $col) {
                echo "- $col\n";
            }
            echo "\n";
            
            $queries = [];
            
            
            if (!in_array('status', $existing_columns)) {
                $queries[] = "ALTER TABLE vehicles ADD status ENUM('active', 'inactive', 'maintenance') DEFAULT 'active'";
            }
            
            
            if (!in_array('created_at', $existing_columns)) {
                $queries[] = "ALTER TABLE vehicles ADD created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
            }
            
            
            if (!in_array('updated_at', $existing_columns)) {
                $queries[] = "ALTER TABLE vehicles ADD updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
            }
            
            
            foreach ($queries as $sql) {
                echo "Executing: $sql\n";
                if ($conn->query($sql)) {
                    echo "  <span class='success'>âœ“ Success</span>\n";
                } else {
                    echo "  <span class='error'>âœ— Error: " . $conn->error . "</span>\n";
                }
            }
            
            
            echo "\n=== Final Vehicles Table Structure ===\n";
            $result = $conn->query("DESCRIBE vehicles");
            while ($row = $result->fetch_assoc()) {
                echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
            }
            
            echo "\n=== Final Payments Table Structure ===\n";
            $result = $conn->query("DESCRIBE payments");
            while ($row = $result->fetch_assoc()) {
                echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
            }
            
            echo '</pre>';
            echo '<p class="success">âœ“ Database has been fixed!</p>';
            echo '<p><a href="admins/dashboard.php">Go to Admin Dashboard</a></p>';
        } else {
            echo '<h2>Current Database Status</h2>';
            echo '<pre>';
            
            
            echo "=== Vehicles Table ===\n";
            $result = $conn->query("DESCRIBE vehicles");
            $vehicles_cols = [];
            while ($row = $result->fetch_assoc()) {
                $vehicles_cols[] = $row['Field'];
                echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
            }
            
            
            echo "\n=== Payments Table ===\n";
            $result = $conn->query("DESCRIBE payments");
            $payments_cols = [];
            while ($row = $result->fetch_assoc()) {
                $payments_cols[] = $row['Field'];
                echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
            }
            
            echo "\n=== Status ===\n";
            echo (!in_array('status', $vehicles_cols) ? "âœ— VEHICLES: Missing 'status' column\n" : "âœ“ VEHICLES: Has 'status' column\n");
            echo (!in_array('created_at', $vehicles_cols) ? "âœ— VEHICLES: Missing 'created_at' column\n" : "âœ“ VEHICLES: Has 'created_at' column\n");
            echo (!in_array('updated_at', $vehicles_cols) ? "âœ— VEHICLES: Missing 'updated_at' column\n" : "âœ“ VEHICLES: Has 'updated_at' column\n");
            echo (in_array('status', $payments_cols) ? "âœ“ PAYMENTS: Has 'status' column\n" : "âœ— PAYMENTS: Missing 'status' column\n");
            
            echo '</pre>';
            
            if (!in_array('status', $vehicles_cols) || 
                !in_array('created_at', $vehicles_cols) || 
                !in_array('updated_at', $vehicles_cols)) {
                echo '<form method="POST">';
                echo '<button type="submit" class="button">Apply Fixes</button>';
                echo '</form>';
            } else {
                echo '<p class="success">âœ“ All required columns are in place!</p>';
                echo '<p><a href="admins/dashboard.php">Go to Admin Dashboard</a></p>';
            }
        }
        ?>
    </div>
</body>
</html>

