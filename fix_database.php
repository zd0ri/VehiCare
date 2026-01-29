<?php


require_once __DIR__ . '/includes/config.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>VehiCare - Database Fix</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: 
        .container { max-width: 800px; margin: 0 auto; background-color: white; padding: 20px; border-radius: 5px; }
        .success { color: green; background-color: 
        .error { color: red; background-color: 
        .warning { color: orange; background-color: 
        .info { color: blue; background-color: 
        h2 { color: 
        pre { background-color: 
    </style>
</head>
<body>
    <div class="container">
        <h1>VehiCare Database Fix & Verification</h1>
        
        <?php
        $fixes_applied = 0;
        $errors = [];
        $success_messages = [];
        
        
        $result = $conn->query("DESCRIBE vehicles");
        if (!$result) {
            $errors[] = "Vehicles table does not exist!";
        } else {
            $columns = [];
            while ($row = $result->fetch_assoc()) {
                $columns[$row['Field']] = $row;
            }
            
            if (!isset($columns['user_id'])) {
                echo "<div class='error'><strong>ERROR:</strong> vehicles table is missing 'user_id' column!</div>";
                echo "<div class='info'>Attempting to add user_id column...</div>";
                
                
                $alter_query = "ALTER TABLE vehicles ADD COLUMN user_id INT NOT NULL AFTER vehicle_id";
                if ($conn->query($alter_query)) {
                    $success_messages[] = "âœ“ Added 'user_id' column to vehicles table";
                    $fixes_applied++;
                } else {
                    $errors[] = "Failed to add user_id column: " . $conn->error;
                }
                
                
                $fk_query = "ALTER TABLE vehicles ADD CONSTRAINT fk_vehicles_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE";
                if ($conn->query($fk_query)) {
                    $success_messages[] = "âœ“ Added foreign key constraint for user_id";
                    $fixes_applied++;
                } else {
                    
                    if (strpos($conn->error, 'already exists') === false) {
                        echo "<div class='warning'>Warning adding FK: " . $conn->error . "</div>";
                    }
                }
                
                
                $index_query = "ALTER TABLE vehicles ADD INDEX idx_user_id (user_id)";
                if ($conn->query($index_query)) {
                    $success_messages[] = "âœ“ Added index on user_id";
                    $fixes_applied++;
                } else {
                    
                    if (strpos($conn->error, 'already exists') === false && strpos($conn->error, 'Duplicate') === false) {
                        echo "<div class='warning'>Warning adding index: " . $conn->error . "</div>";
                    }
                }
            } else {
                echo "<div class='success'>âœ“ vehicles table has user_id column</div>";
            }
        }
        
        
        $result = $conn->query("DESCRIBE appointments");
        if (!$result) {
            $errors[] = "Appointments table does not exist!";
        } else {
            $columns = [];
            while ($row = $result->fetch_assoc()) {
                $columns[$row['Field']] = $row;
            }
            echo "<div class='info'>âœ“ appointments table exists with " . count($columns) . " columns</div>";
        }
        
        
        $result = $conn->query("DESCRIBE service_history");
        if (!$result) {
            $errors[] = "Service_history table does not exist!";
        } else {
            $columns = [];
            while ($row = $result->fetch_assoc()) {
                $columns[$row['Field']] = $row;
            }
            echo "<div class='info'>âœ“ service_history table exists with " . count($columns) . " columns</div>";
        }
        
        
        foreach ($success_messages as $msg) {
            echo "<div class='success'>$msg</div>";
        }
        
        
        if (!empty($errors)) {
            echo "<h3>Errors Found:</h3>";
            foreach ($errors as $error) {
                echo "<div class='error'>âœ— $error</div>";
            }
        }
        
        
        echo "<h3>Summary</h3>";
        if ($fixes_applied > 0) {
            echo "<div class='success'>Database has been fixed! Applied $fixes_applied fixes.</div>";
            echo "<div class='info'>You can now try accessing the client dashboard again.</div>";
        } elseif (empty($errors)) {
            echo "<div class='success'>Database structure appears to be correct!</div>";
        }
        
        
        echo "<h3>Current Vehicles Table Structure:</h3>";
        echo "<pre>";
        $result = $conn->query("DESCRIBE vehicles");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                printf("%-20s %-30s %-10s %-10s\n", 
                    $row['Field'], 
                    $row['Type'], 
                    $row['Null'] == 'YES' ? 'NULL' : 'NOT NULL',
                    $row['Key'] ?: '-'
                );
            }
        }
        echo "</pre>";
        
        ?>
        
        <hr>
        <h3>Next Steps:</h3>
        <ol>
            <li>If the database was successfully fixed, try accessing the <a href="/vehicare_db/login.php">login page</a></li>
            <li>If errors persist, you may need to run the full setup at <a href="/vehicare_db/setup.php">setup.php</a></li>
            <li>If all tables are missing, run <a href="/vehicare_db/setup_manual.php">setup_manual.php</a> instead</li>
        </ol>
    </div>
</body>
</html>
?>

