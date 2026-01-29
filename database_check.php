<!DOCTYPE html>
<html>
<head>
    <title>VehiCare Database Structure Check</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .button { padding: 10px 20px; background: 
        .button:hover { background: 
        pre { background: 
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid 
        th { background-color: 
    </style>
</head>
<body>
    <div class="container">
        <h1>VehiCare Database Structure Check</h1>
        
        <?php
        require_once __DIR__ . '/includes/config.php';
        
        echo '<h2>Current Table Structures</h2>';
        
        $tables = ['users', 'vehicles', 'payments', 'appointments'];
        
        foreach ($tables as $table) {
            echo "<h3>Table: $table</h3>";
            $result = $conn->query("DESCRIBE $table");
            if ($result) {
                echo '<table>';
                echo '<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>';
                while ($row = $result->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . $row['Field'] . '</td>';
                    echo '<td>' . $row['Type'] . '</td>';
                    echo '<td>' . $row['Null'] . '</td>';
                    echo '<td>' . $row['Key'] . '</td>';
                    echo '<td>' . ($row['Default'] ?? 'NULL') . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            } else {
                echo '<p class="error">Error: ' . $conn->error . '</p>';
            }
        }
        
        
        echo '<h2>Required Fixes</h2>';
        echo '<pre>';
        
        $migrations = [];
        
        
        $result = $conn->query("DESCRIBE vehicles");
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
        
        if (!in_array('status', $columns)) {
            echo "VEHICLES TABLE MISSING: status column\n";
            
            if (in_array('color', $columns)) {
                $migrations[] = [
                    "ALTER TABLE vehicles ADD COLUMN status ENUM('active', 'inactive', 'maintenance') DEFAULT 'active' AFTER color",
                    "Adding status to vehicles"
                ];
            } else {
                $migrations[] = [
                    "ALTER TABLE vehicles ADD COLUMN status ENUM('active', 'inactive', 'maintenance') DEFAULT 'active'",
                    "Adding status to vehicles"
                ];
            }
        } else {
            echo "âœ“ VEHICLES: status column exists\n";
        }
        
        if (!in_array('created_at', $columns)) {
            echo "VEHICLES TABLE MISSING: created_at column\n";
            $migrations[] = [
                "ALTER TABLE vehicles ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
                "Adding created_at to vehicles"
            ];
        } else {
            echo "âœ“ VEHICLES: created_at column exists\n";
        }
        
        if (!in_array('updated_at', $columns)) {
            echo "VEHICLES TABLE MISSING: updated_at column\n";
            $migrations[] = [
                "ALTER TABLE vehicles ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
                "Adding updated_at to vehicles"
            ];
        } else {
            echo "âœ“ VEHICLES: updated_at column exists\n";
        }
        
        
        $result = $conn->query("DESCRIBE payments");
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
        
        if (!in_array('status', $columns)) {
            echo "PAYMENTS TABLE MISSING: status column\n";
            $migrations[] = [
                "ALTER TABLE payments ADD COLUMN status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending' AFTER payment_method",
                "Adding status to payments"
            ];
        } else {
            echo "âœ“ PAYMENTS: status column exists\n";
        }
        
        echo '</pre>';
        
        if (!empty($migrations)) {
            echo '<h2>Apply Required Migrations</h2>';
            echo '<form method="POST">';
            echo '<button type="submit" class="button">Apply Fixes</button>';
            echo '</form>';
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                echo '<h2>Migration Results</h2>';
                echo '<pre>';
                
                foreach ($migrations as $migration) {
                    list($sql, $description) = $migration;
                    echo "$description...\n";
                    
                    try {
                        if ($conn->query($sql)) {
                            echo "  âœ“ Success\n";
                        } else {
                            echo "  âœ— Error: " . $conn->error . "\n";
                        }
                    } catch (Exception $e) {
                        echo "  âœ— Error: " . $e->getMessage() . "\n";
                    }
                }
                
                echo '</pre>';
                echo '<p class="success"><strong>âœ“ Migrations completed. Refresh the page to verify all changes.</strong></p>';
            }
        } else {
            echo '<p class="success"><strong>âœ“ All required columns are in place!</strong></p>';
            echo '<p>You can now <a href="admins/dashboard.php">go to admin dashboard</a></p>';
        }
        ?>
    </div>
</body>
</html>

