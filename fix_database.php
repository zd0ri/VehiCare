<?php
/**
 * Simple Database Fixes for Client Module
 * This script fixes the missing client_id column by using the existing config
 */

try {
    // Use file-based connection since mysqli might not be available
    $config_content = file_get_contents('includes/config.php');
    
    // Extract database credentials from config
    preg_match('/\$host\s*=\s*["\']([^"\']*)["\']/', $config_content, $host_match);
    preg_match('/\$username\s*=\s*["\']([^"\']*)["\']/', $config_content, $user_match);
    preg_match('/\$password\s*=\s*["\']([^"\']*)["\']/', $config_content, $pass_match);
    preg_match('/\$database\s*=\s*["\']([^"\']*)["\']/', $config_content, $db_match);
    
    $host = $host_match[1] ?? 'localhost';
    $username = $user_match[1] ?? 'root';
    $password = $pass_match[1] ?? '';
    $database = $db_match[1] ?? 'vehicare_db';
    
    echo "=== VehiCare Client Module Database Fix ===\n\n";
    echo "Host: $host\n";
    echo "Database: $database\n";
    echo "User: $username\n\n";
    
    // Use PDO instead of mysqli
    $dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "✓ Connected to database successfully\n\n";
    
    // Check existing tables
    $stmt = $pdo->query("SHOW TABLES");
    $existing_tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Existing tables:\n";
    foreach ($existing_tables as $table) {
        echo "- $table\n";
    }
    echo "\n";
    
    // Fix invoices table - add client_id column if missing
    echo "=== Fixing invoices table ===\n";
    
    try {
        $stmt = $pdo->query("DESCRIBE invoices");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (!in_array('client_id', $columns)) {
            echo "Adding client_id column to invoices table...\n";
            $pdo->exec("ALTER TABLE invoices ADD COLUMN client_id INT(11) NOT NULL DEFAULT 1 AFTER invoice_id");
            echo "✓ Added client_id column\n";
        } else {
            echo "✓ client_id column already exists\n";
        }
    } catch (PDOException $e) {
        echo "Note: invoices table may not exist yet: " . $e->getMessage() . "\n";
        
        // Create basic invoices table if it doesn't exist
        echo "Creating invoices table...\n";
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS invoices (
                invoice_id INT(11) PRIMARY KEY AUTO_INCREMENT,
                client_id INT(11) NOT NULL,
                appointment_id INT(11) DEFAULT NULL,
                invoice_date DATE NOT NULL,
                subtotal DECIMAL(10,2) DEFAULT 0.00,
                tax_amount DECIMAL(10,2) DEFAULT 0.00,
                grand_total DECIMAL(10,2) NOT NULL,
                payment_status ENUM('unpaid', 'partial', 'paid') DEFAULT 'unpaid',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        echo "✓ Created invoices table\n";
    }
    
    // Create essential client module tables
    echo "\n=== Creating Client Module Tables ===\n";
    
    // Reviews table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS reviews (
            review_id INT(11) PRIMARY KEY AUTO_INCREMENT,
            client_id INT(11) NOT NULL,
            appointment_id INT(11) NOT NULL,
            rating INT(1) NOT NULL,
            review_text TEXT NOT NULL,
            service_quality INT(1) DEFAULT NULL,
            staff_friendliness INT(1) DEFAULT NULL,
            timeliness INT(1) DEFAULT NULL,
            value_for_money INT(1) DEFAULT NULL,
            recommend ENUM('yes', 'no') DEFAULT 'yes',
            review_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            status ENUM('active', 'hidden', 'flagged') DEFAULT 'active'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✓ Reviews table ready\n";
    
    // Payments table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS payments (
            payment_id INT(11) PRIMARY KEY AUTO_INCREMENT,
            client_id INT(11) NOT NULL,
            invoice_id INT(11) NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            payment_method ENUM('cash', 'credit_card', 'debit_card', 'gcash', 'paymaya', 'bank_transfer') NOT NULL,
            payment_status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
            payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            reference_number VARCHAR(100) DEFAULT NULL,
            notes TEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✓ Payments table ready\n";
    
    // Notifications table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS notifications (
            notification_id INT(11) PRIMARY KEY AUTO_INCREMENT,
            client_id INT(11) NOT NULL,
            title VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            type ENUM('appointment', 'payment', 'reminder', 'promotion', 'system') DEFAULT 'system',
            is_read BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            read_at TIMESTAMP NULL DEFAULT NULL,
            related_id INT(11) DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✓ Notifications table ready\n";
    
    // Maintenance reminders table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS maintenance_reminders (
            reminder_id INT(11) PRIMARY KEY AUTO_INCREMENT,
            client_id INT(11) NOT NULL,
            vehicle_id INT(11) NOT NULL,
            reminder_type ENUM('mileage', 'time', 'both') DEFAULT 'time',
            service_type VARCHAR(100) NOT NULL,
            due_date DATE DEFAULT NULL,
            due_mileage INT(11) DEFAULT NULL,
            current_mileage INT(11) DEFAULT NULL,
            notes TEXT DEFAULT NULL,
            is_completed BOOLEAN DEFAULT FALSE,
            completed_at TIMESTAMP NULL DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✓ Maintenance reminders table ready\n";
    
    // User preferences table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_preferences (
            preference_id INT(11) PRIMARY KEY AUTO_INCREMENT,
            client_id INT(11) NOT NULL,
            email_notifications BOOLEAN DEFAULT TRUE,
            sms_notifications BOOLEAN DEFAULT TRUE,
            appointment_reminders BOOLEAN DEFAULT TRUE,
            maintenance_reminders BOOLEAN DEFAULT TRUE,
            promotional_emails BOOLEAN DEFAULT FALSE,
            newsletter_subscription BOOLEAN DEFAULT FALSE,
            language ENUM('en', 'fil') DEFAULT 'en',
            timezone VARCHAR(50) DEFAULT 'Asia/Manila',
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✓ User preferences table ready\n";
    
    // Client activity logs table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS client_activity_logs (
            log_id INT(11) PRIMARY KEY AUTO_INCREMENT,
            client_id INT(11) NOT NULL,
            activity_type ENUM('login', 'logout', 'appointment', 'payment', 'profile_update', 'vehicle_add', 'review', 'other') NOT NULL,
            activity_description TEXT NOT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent TEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✓ Client activity logs table ready\n";
    
    echo "\n=== Database Setup Complete! ===\n";
    echo "All necessary tables have been created.\n";
    echo "The client dashboard should now work properly.\n\n";
    
    // Test the setup by checking if we can query the invoices table
    echo "=== Testing Database ===\n";
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM invoices");
        $result = $stmt->fetch();
        echo "✓ Invoices table query successful (found {$result['count']} records)\n";
        
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM payments");
        $result = $stmt->fetch();
        echo "✓ Payments table query successful (found {$result['count']} records)\n";
        
        echo "\n🎉 Database is ready! You can now access the client dashboard.\n";
        
    } catch (PDOException $e) {
        echo "⚠️  Warning: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\nPlease check your database configuration in includes/config.php\n";
    exit(1);
}
?>