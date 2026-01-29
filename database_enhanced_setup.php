<!DOCTYPE html>
<html>
<head>
    <title>VehiCare Enhanced Database Setup</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: 
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        .button { padding: 12px 24px; background: 
        .button:hover { background: 
        .button.secondary { background: 
        .button.secondary:hover { background: 
        pre { background: 
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .info { color: 
        h1 { color: 
        h2 { color: 
        .status-box { padding: 15px; margin: 20px 0; border-radius: 5px; }
        .status-box.success { background: 
        .status-box.error { background: 
        .status-box.info { background: 
    </style>
</head>
<body>
    <div class="container">
        <h1>ðŸš— VehiCare Enhanced Database Setup</h1>
        <p>This will initialize all database tables including new features for complete system functionality.</p>
        
        <?php
        require_once __DIR__ . '/includes/config.php';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            
            if ($_POST['action'] === 'setup_enhanced') {
                echo '<h2>Setting Up Enhanced Database</h2>';
                echo '<pre>';
                
                
                $schema_file = __DIR__ . '/database_schema_enhancements.sql';
                if (!file_exists($schema_file)) {
                    echo "ERROR: Schema enhancements file not found\n";
                    exit;
                }
                
                $sql_content = file_get_contents($schema_file);
                $statements = array_filter(array_map('trim', explode(';', $sql_content)));
                
                $success_count = 0;
                $error_count = 0;
                $skipped_count = 0;
                
                foreach ($statements as $statement) {
                    if (empty($statement) || strpos(trim($statement), '--') === 0) {
                        continue;
                    }
                    
                    $short_stmt = substr($statement, 0, 70) . '...';
                    echo "Executing: $short_stmt\n";
                    
                    try {
                        if ($conn->query($statement)) {
                            $success_count++;
                            echo "  <span class='success'>âœ“ Success</span>\n";
                        } else {
                            $error_msg = $conn->error;
                            
                            if (strpos($error_msg, 'already exists') !== false || 
                                strpos($error_msg, 'Duplicate') !== false) {
                                $skipped_count++;
                                echo "  <span class='info'>âŠ˜ Skipped (already exists)</span>\n";
                            } else {
                                $error_count++;
                                echo "  <span class='error'>âœ— Error: $error_msg</span>\n";
                            }
                        }
                    } catch (Exception $e) {
                        echo "  <span class='error'>âœ— Error: " . $e->getMessage() . "</span>\n";
                        $error_count++;
                    }
                }
                
                echo '</pre>';
                echo '<div class="status-box success">';
                echo "<strong>Setup Summary:</strong><br>";
                echo "âœ“ Successful: $success_count<br>";
                echo "âŠ˜ Skipped: $skipped_count<br>";
                echo "âœ— Errors: $error_count<br>";
                echo '</div>';
                
                if ($error_count === 0) {
                    echo '<p class="success">âœ“ Enhanced database setup completed successfully!</p>';
                } else {
                    echo '<p class="error">âš  Setup completed with some errors.</p>';
                }
            }
            
            
            echo '<h2>Database Tables Status</h2>';
            echo '<pre>';
            
            $tables_to_check = [
                'users', 'vehicles', 'services', 'appointments', 'service_history',
                'payments', 'queue_management', 'inventory_parts', 'inventory_transactions',
                'staff_ratings', 'notifications', 'invoices', 'invoice_items',
                'audit_logs', 'appointment_services'
            ];
            
            foreach ($tables_to_check as $table) {
                $result = $conn->query("SHOW TABLES LIKE '$table'");
                if ($result && $result->num_rows > 0) {
                    echo "<span class='success'>âœ“ $table</span>\n";
                } else {
                    echo "<span class='error'>âœ— $table</span>\n";
                }
            }
            
            echo '</pre>';
            
        } else {
            
            echo '<h2>Available Actions</h2>';
            
            
            echo '<h3>Current Database Status</h3>';
            echo '<pre>';
            
            $tables_to_check = [
                'Core Tables' => ['users', 'vehicles', 'services', 'appointments'],
                'Enhancement Tables' => ['inventory_parts', 'staff_ratings', 'notifications', 'invoices', 'audit_logs']
            ];
            
            foreach ($tables_to_check as $category => $tables) {
                echo "\n$category:\n";
                foreach ($tables as $table) {
                    $result = $conn->query("SHOW TABLES LIKE '$table'");
                    if ($result && $result->num_rows > 0) {
                        echo "  <span class='success'>âœ“ $table</span>\n";
                    } else {
                        echo "  <span class='error'>âœ— $table (missing)</span>\n";
                    }
                }
            }
            
            echo '</pre>';
            
            echo '<h2>Setup Steps</h2>';
            echo '<ol>';
            echo '<li><strong>Step 1:</strong> Set up core database tables (if not already done)</li>';
            echo '<li><strong>Step 2:</strong> Set up enhanced database tables (new features)</li>';
            echo '<li><strong>Step 3:</strong> Configure admin dashboard</li>';
            echo '</ol>';
            
            echo '<form method="POST">';
            echo '<input type="hidden" name="action" value="setup_enhanced">';
            echo '<button type="submit" class="button">Setup Enhanced Database</button>';
            echo '</form>';
        }
        ?>
    </div>
</body>
</html>

