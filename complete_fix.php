<?php


require_once __DIR__ . '/includes/config.php';


ini_set('display_errors', 0);
set_time_limit(300);

?>
<!DOCTYPE html>
<html>
<head>
    <title>VehiCare - Complete Database Fix</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, 
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 700px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 { color: 
        .subtitle { color: 
        .status {
            padding: 12px 15px;
            margin: 10px 0;
            border-radius: 6px;
            font-size: 14px;
            border-left: 4px solid;
        }
        .success {
            background: 
            color: 
            border-left-color: 
        }
        .error {
            background: 
            color: 
            border-left-color: 
        }
        .info {
            background: 
            color: 
            border-left-color: 
        }
        .button {
            display: inline-block;
            background: 
            color: white;
            padding: 12px 24px;
            border-radius: 6px;
            text-decoration: none;
            margin-top: 20px;
            transition: background 0.3s;
        }
        .button:hover { background: 
        hr { border: none; border-top: 1px solid 
    </style>
</head>
<body>
    <div class="container">
        <h1>ðŸ”§ VehiCare Database Fix</h1>
        <p class="subtitle">Automatically fixing database structure...</p>

        <?php
        $fixed = 0;
        $errors = [];
        
        
        $fixes = [
            ['appointments', 'user_id', 'INT NOT NULL', 'appointment_id'],
            ['service_history', 'user_id', 'INT NOT NULL', 'history_id'],
            ['payments', 'user_id', 'INT NOT NULL', 'payment_id'],
        ];
        
        
        foreach ($fixes as $fix) {
            list($table, $column, $type, $after) = $fix;
            
            
            $check = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
            
            if ($check && $check->num_rows > 0) {
                echo "<div class='status info'>âœ“ $table.$column already exists</div>";
            } else {
                
                $sql = "ALTER TABLE `$table` ADD COLUMN `$column` $type AFTER `$after`";
                if ($conn->query($sql)) {
                    echo "<div class='status success'>âœ“ Added $column to $table table</div>";
                    $fixed++;
                    
                    
                    $fk_sql = "ALTER TABLE `$table` ADD CONSTRAINT `fk_{$table}_user` FOREIGN KEY (`$column`) REFERENCES `users`(`user_id`) ON DELETE CASCADE";
                    $conn->query($fk_sql); 
                } else {
                    if (strpos($conn->error, 'Duplicate') !== false) {
                        echo "<div class='status info'>â„¹ $column already exists in $table (ignoring)</div>";
                    } else {
                        echo "<div class='status error'>âœ— Error adding $column to $table: " . htmlspecialchars($conn->error) . "</div>";
                        $errors[] = $conn->error;
                    }
                }
            }
        }
        
        echo "<hr>";
        
        
        echo "<h3>Verification:</h3>";
        
        $verify_tables = ['appointments', 'service_history', 'payments', 'vehicles'];
        foreach ($verify_tables as $table) {
            $result = $conn->query("SHOW COLUMNS FROM `$table` LIKE 'user_id'");
            if ($result && $result->num_rows > 0) {
                echo "<div class='status success'>âœ“ $table has user_id column</div>";
            } else {
                echo "<div class='status error'>âœ— $table still missing user_id</div>";
            }
        }
        
        echo "<hr>";
        
        if ($fixed > 0) {
            echo "<div class='status success'><strong>Success!</strong> Fixed $fixed tables. Your database should now work correctly.</div>";
        } elseif (empty($errors)) {
            echo "<div class='status success'><strong>Good news!</strong> All required columns already exist.</div>";
        } else {
            echo "<div class='status error'><strong>Some issues remain.</strong> Please check above.</div>";
        }
        
        ?>
        
        <p style="margin-top: 20px; font-size: 14px; color: 
            Next step: Try accessing your dashboard again. The error should be resolved!
        </p>
        
        <a href="/vehicare_db/client/dashboard.php" class="button">Go to Dashboard</a>
        <a href="/vehicare_db/login.php" class="button" style="background: 
    </div>
</body>
</html>
?>

