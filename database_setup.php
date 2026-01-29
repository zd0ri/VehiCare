<!DOCTYPE html>
<html>
<head>
    <title>VehiCare Database Setup</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .button { padding: 10px 20px; background: 
        .button:hover { background: 
        pre { background: 
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <div class="container">
        <h1>VehiCare Database Setup</h1>
        <p>This will initialize the database with all required tables and schema.</p>
        
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/includes/config.php';
            
            echo '<h2>Setup Progress</h2>';
            echo '<pre>';
            
            
            $schema_file = __DIR__ . '/database_schema_complete.sql';
            if (!file_exists($schema_file)) {
                echo "ERROR: Schema file not found\n";
                exit;
            }
            
            $sql_content = file_get_contents($schema_file);
            $statements = array_filter(array_map('trim', explode(';', $sql_content)));
            
            $success_count = 0;
            $error_count = 0;
            $critical_errors = [];
            
            foreach ($statements as $statement) {
                if (empty($statement) || strpos(trim($statement), '--') === 0) {
                    continue;
                }
                
                $short_stmt = substr($statement, 0, 60);
                echo "Executing: $short_stmt...\n";
                
                try {
                    if ($conn->query($statement)) {
                        $success_count++;
                        echo "  âœ“ Success\n";
                    } else {
                        $error_msg = $conn->error;
                        echo "  âœ— Error: $error_msg\n";
                        
                        
                        if (strpos($error_msg, 'already exists') === false && 
                            strpos($error_msg, 'Duplicate key name') === false) {
                            $error_count++;
                            $critical_errors[] = $error_msg;
                        }
                    }
                } catch (Exception $e) {
                    $error_msg = $e->getMessage();
                    echo "  âœ— Error: $error_msg\n";
                    
                    
                    if (strpos($error_msg, 'already exists') === false && 
                        strpos($error_msg, 'Duplicate key name') === false) {
                        $error_count++;
                        $critical_errors[] = $error_msg;
                    }
                }
            }
            
            echo "\n=== Setup Summary ===\n";
            echo "Successful: $success_count\n";
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
                echo '<p class="success"><strong>âœ“ Database setup completed successfully!</strong></p>';
                echo '<p>You can now <a href="admins/dashboard.php">go to admin dashboard</a></p>';
            } else {
                echo '<p class="error"><strong>âš  Setup completed with some errors. Check above for details.</strong></p>';
            }
        } else {
            echo '<form method="POST">';
            echo '<button type="submit" class="button">Run Database Setup</button>';
            echo '</form>';
        }
        ?>
    </div>
</body>
</html>

