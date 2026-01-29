<?php


require_once __DIR__ . '/includes/config.php';


set_time_limit(300);
ini_set('display_errors', 0);

?>
<!DOCTYPE html>
<html>
<head>
    <title>VehiCare - Database Setup & Repair</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, 
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            padding: 40px;
        }
        h1 {
            color: 
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: 
            margin-bottom: 30px;
            font-size: 14px;
        }
        .status {
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
        }
        .status.success {
            background: 
            color: 
            border: 1px solid 
        }
        .status.error {
            background: 
            color: 
            border: 1px solid 
        }
        .status.info {
            background: 
            color: 
            border: 1px solid 
        }
        .status.warning {
            background: 
            color: 
            border: 1px solid 
        }
        .icon {
            font-weight: bold;
            font-size: 18px;
        }
        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        .button {
            flex: 1;
            min-width: 150px;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        .button-primary {
            background: 
            color: white;
        }
        .button-primary:hover {
            background: 
        }
        .button-secondary {
            background: 
            color: 
        }
        .button-secondary:hover {
            background: 
        }
        .details {
            background: 
            border: 1px solid 
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            font-size: 13px;
            color: 
            max-height: 200px;
            overflow-y: auto;
        }
        .details pre {
            background: transparent;
            border: none;
            margin: 0;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>ðŸš— VehiCare Database Setup</h1>
        <p class="subtitle">Initialize or repair your VehiCare database</p>

        <?php
        $issues = [];
        $fixes = [];
        
        
        $check_vehicles = $conn->query("SHOW TABLES LIKE 'vehicles'");
        if ($check_vehicles && $check_vehicles->num_rows > 0) {
            echo '<div class="status success"><span class="icon">âœ“</span> <strong>vehicles</strong> table exists</div>';
            
            
            $check_column = $conn->query("SHOW COLUMNS FROM vehicles LIKE 'user_id'");
            if ($check_column && $check_column->num_rows > 0) {
                echo '<div class="status success"><span class="icon">âœ“</span> <strong>user_id</strong> column exists in vehicles table</div>';
            } else {
                echo '<div class="status error"><span class="icon">âœ—</span> <strong>user_id</strong> column is MISSING in vehicles table</div>';
                $issues[] = "Missing user_id column in vehicles table";
            }
        } else {
            echo '<div class="status error"><span class="icon">âœ—</span> <strong>vehicles</strong> table DOES NOT EXIST</div>';
            $issues[] = "vehicles table not found";
        }
        
        
        $required_tables = ['users', 'appointments', 'service_history', 'services'];
        foreach ($required_tables as $table) {
            $check = $conn->query("SHOW TABLES LIKE '$table'");
            if ($check && $check->num_rows > 0) {
                echo '<div class="status success"><span class="icon">âœ“</span> <strong>' . $table . '</strong> table exists</div>';
            } else {
                echo '<div class="status warning"><span class="icon">!</span> <strong>' . $table . '</strong> table missing</div>';
                $issues[] = "$table table not found";
            }
        }
        
        echo '<hr style="border: none; border-top: 1px solid #e0e0e0; margin: 20px 0;">';
        
        if (empty($issues)) {
            echo '<div class="status success"><span class="icon">✓</span> Your database is properly set up!</div>';
            echo '<p style="margin: 15px 0; color: #666;">All required tables and columns are in place.</p>';
        } else {
            echo '<div class="status warning"><span class="icon">âš </span> Database issues detected - click below to fix automatically</div>';
            
            
            if (count($issues) > 0) {
                echo '<div style="margin: 20px 0;">';
                
                
                if (in_array("Missing user_id column in vehicles table", $issues)) {
                    $alter = $conn->query("ALTER TABLE vehicles ADD COLUMN user_id INT NOT NULL DEFAULT 1 AFTER vehicle_id");
                    if ($alter) {
                        echo '<div class="status success"><span class="icon">âœ“</span> Added <strong>user_id</strong> column to vehicles table</div>';
                        $fixes[] = "Added user_id column";
                    } else {
                        echo '<div class="status error"><span class="icon">âœ—</span> Failed to add user_id column: ' . htmlspecialchars($conn->error) . '</div>';
                    }
                    
                    
                    $fk = $conn->query("ALTER TABLE vehicles ADD CONSTRAINT fk_vehicles_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE");
                    if ($fk) {
                        echo '<div class="status success"><span class="icon">âœ“</span> Added foreign key constraint</div>';
                    } else {
                        if (strpos($conn->error, 'already exists') === false) {
                            echo '<div class="status info"><span class="icon">â„¹</span> Foreign key already exists or cannot be added</div>';
                        }
                    }
                    
                    
                    $idx = $conn->query("ALTER TABLE vehicles ADD INDEX idx_user_id (user_id)");
                    if ($idx) {
                        echo '<div class="status success"><span class="icon">âœ“</span> Added user_id index</div>';
                    }
                }
                
                echo '</div>';
            }
        }
        
        ?>

        <div class="button-group">
            <a href="/vehicare_db/login.php" class="button button-primary">
                <span>âœ“</span> Return to Login
            </a>
            <a href="/vehicare_db/setup.php" class="button button-secondary">
                <span>âš™</span> Full Setup
            </a>
        </div>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid 
            <p><strong>What is this page?</strong></p>
            <p>This page checks your VehiCare database structure and automatically fixes common issues. If you're seeing the "Unknown column 'user_id'" error, this page can repair it.</p>
            <p style="margin-top: 10px;"><strong>Next steps:</strong></p>
            <ol style="margin-left: 20px; margin-top: 5px;">
                <li>This page should have automatically fixed any missing columns</li>
                <li>Click "Return to Login" to go back and try accessing your account</li>
                <li>If issues persist, click "Full Setup" to recreate all database tables</li>
            </ol>
        </div>
    </div>
</body>
</html>
?>

