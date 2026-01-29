<!DOCTYPE html>
<html>
<head>
    <title>VehiCare Database Migration</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .button { padding: 10px 20px; background: 
        .button:hover { background: 
        pre { background: 
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
    </style>
</head>
<body>
    <div class="container">
        <h1>VehiCare Database Migration</h1>
        <p>This will update the existing database structure to match the required schema.</p>
        
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/includes/config.php';
            
            echo '<h2>Migration Progress</h2>';
            echo '<pre>';
            
            $migrations = [
                
                "ALTER TABLE vehicles ADD COLUMN status ENUM('active', 'inactive', 'maintenance') DEFAULT 'active' AFTER color" => "Adding status column to vehicles",
                
                
                "ALTER TABLE payments ADD COLUMN status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending' AFTER payment_method" => "Adding status column to payments table",
                
                
                "ALTER TABLE vehicles ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER status" => "Adding created_at to vehicles",
                "ALTER TABLE vehicles ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at" => "Adding updated_at to vehicles",
                
                
                "ALTER TABLE users ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER status" => "Adding created_at to users",
                "ALTER TABLE users ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at" => "Adding updated_at to users",
                "ALTER TABLE users ADD COLUMN last_login TIMESTAMP NULL AFTER created_at" => "Adding last_login to users",
            ];
            
            $success_count = 0;
            $skipped_count = 0;
            $error_count = 0;
            $critical_errors = [];
            
            foreach ($migrations as $sql => $description) {
                echo "$description...\n";
                
                try {
                    if ($conn->query($sql)) {
                        $success_count++;
                        echo "  âœ“ Success\n";
                    } else {
                        $error_msg = $conn->error;
                        echo "  âœ— Error: $error_msg\n";
                        
                        
                        if (strpos($error_msg, 'Duplicate column name') !== false || 
                            strpos($error_msg, 'already exists') !== false) {
                            $skipped_count++;
                        } else {
                            $error_count++;
                            $critical_errors[] = $error_msg;
                        }
                    }
                } catch (Exception $e) {
                    $error_msg = $e->getMessage();
                    echo "  âœ— Error: $error_msg\n";
                    
                    if (strpos($error_msg, 'Duplicate column name') !== false || 
                        strpos($error_msg, 'already exists') !== false) {
                        $skipped_count++;
                    } else {
                        $error_count++;
                        $critical_errors[] = $error_msg;
                    }
                }
            }
            
            echo "\n=== Migration Summary ===\n";
            echo "Successful migrations: $success_count\n";
            echo "Skipped (already exist): $skipped_count\n";
            echo "Critical Errors: $error_count\n";
            
            
            echo "\n=== Verifying payments table ===\n";
            $result = $conn->query('DESCRIBE payments');
            if ($result) {
                echo "Payments table structure:\n";
                while ($row = $result->fetch_assoc()) {
                    echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
                }
            } else {
                echo "ERROR: " . $conn->error . "\n";
            }
            
            echo '</pre>';
            
            if (empty($critical_errors)) {
                echo '<p class="success"><strong>âœ“ Database migration completed successfully!</strong></p>';
                echo '<p>All required columns are now in place. You can now <a href="admins/dashboard.php">go to admin dashboard</a></p>';
            } else {
                echo '<p class="error"><strong>âš  Migration completed with some errors. Check above for details.</strong></p>';
            }
        } else {
            echo '<form method="POST">';
            echo '<button type="submit" class="button">Run Database Migration</button>';
            echo '</form>';
        }
        ?>
    </div>
</body>
</html>

