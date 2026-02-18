<?php
/**
 * Client Module Database Setup Script
 * This script will create all necessary tables for the client module
 */

// Database connection settings
$host = 'localhost';
$username = 'root';
$database = 'vehicare_db';

// Prompt for password
echo "Enter MySQL root password: ";
$handle = fopen("php://stdin", "r");
$password = trim(fgets($handle));
fclose($handle);

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== VehiCare Client Module Database Setup ===\n\n";
    echo "✓ Connected to database successfully\n\n";
    
    // Check if client_id exists in invoices table
    echo "Checking invoices table structure...\n";
    $stmt = $pdo->query("DESCRIBE invoices");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('client_id', $columns)) {
        echo "Adding client_id column to invoices table...\n";
        $pdo->exec("ALTER TABLE invoices ADD COLUMN client_id INT(11) NOT NULL AFTER invoice_id");
        echo "✓ Added client_id column\n";
        
        // Add foreign key constraint
        try {
            $pdo->exec("ALTER TABLE invoices ADD CONSTRAINT fk_invoice_client FOREIGN KEY (client_id) REFERENCES users(user_id)");
            echo "✓ Added foreign key constraint\n";
        } catch (PDOException $e) {
            echo "Note: Foreign key constraint may already exist or users table needs setup\n";
        }
    } else {
        echo "✓ client_id column already exists in invoices table\n";
    }
    
    echo "\n=== Creating Client Module Tables ===\n";
    
    // Create reviews table
    echo "Creating reviews table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS reviews (
            review_id INT(11) PRIMARY KEY AUTO_INCREMENT,
            client_id INT(11) NOT NULL,
            appointment_id INT(11) NOT NULL,
            rating INT(1) NOT NULL CHECK (rating BETWEEN 1 AND 5),
            review_text TEXT NOT NULL,
            service_quality INT(1) DEFAULT NULL CHECK (service_quality BETWEEN 1 AND 5),
            staff_friendliness INT(1) DEFAULT NULL CHECK (staff_friendliness BETWEEN 1 AND 5),
            timeliness INT(1) DEFAULT NULL CHECK (timeliness BETWEEN 1 AND 5),
            value_for_money INT(1) DEFAULT NULL CHECK (value_for_money BETWEEN 1 AND 5),
            recommend ENUM('yes', 'no') DEFAULT 'yes',
            review_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            status ENUM('active', 'hidden', 'flagged') DEFAULT 'active',
            FOREIGN KEY (client_id) REFERENCES users(user_id) ON DELETE CASCADE,
            FOREIGN KEY (appointment_id) REFERENCES appointments(appointment_id) ON DELETE CASCADE,
            UNIQUE KEY unique_client_appointment (client_id, appointment_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✓ Reviews table created\n";
    
    // Create payments table
    echo "Creating payments table...\n";
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
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (client_id) REFERENCES users(user_id) ON DELETE CASCADE,
            FOREIGN KEY (invoice_id) REFERENCES invoices(invoice_id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✓ Payments table created\n";
    
    // Create notifications table
    echo "Creating notifications table...\n";
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
            related_id INT(11) DEFAULT NULL COMMENT 'Related appointment_id, payment_id, etc.',
            FOREIGN KEY (client_id) REFERENCES users(user_id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✓ Notifications table created\n";
    
    // Create maintenance_reminders table
    echo "Creating maintenance_reminders table...\n";
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
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (client_id) REFERENCES users(user_id) ON DELETE CASCADE,
            FOREIGN KEY (vehicle_id) REFERENCES vehicles(vehicle_id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✓ Maintenance reminders table created\n";
    
    // Create user_preferences table
    echo "Creating user_preferences table...\n";
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
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (client_id) REFERENCES users(user_id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✓ User preferences table created\n";
    
    // Create client_activity_logs table
    echo "Creating client_activity_logs table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS client_activity_logs (
            log_id INT(11) PRIMARY KEY AUTO_INCREMENT,
            client_id INT(11) NOT NULL,
            activity_type ENUM('login', 'logout', 'appointment', 'payment', 'profile_update', 'vehicle_add', 'review', 'other') NOT NULL,
            activity_description TEXT NOT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent TEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (client_id) REFERENCES users(user_id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✓ Client activity logs table created\n";
    
    echo "\n=== Database Setup Complete! ===\n";
    echo "All client module tables have been created successfully.\n";
    echo "You can now use the client dashboard and all its features.\n\n";
    
    // Insert default user preferences for existing clients
    echo "Setting up default preferences for existing clients...\n";
    $stmt = $pdo->query("SELECT user_id FROM users WHERE role = 'client'");
    $clients = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($clients as $client_id) {
        $checkPref = $pdo->prepare("SELECT preference_id FROM user_preferences WHERE client_id = ?");
        $checkPref->execute([$client_id]);
        
        if (!$checkPref->fetch()) {
            $insertPref = $pdo->prepare("INSERT INTO user_preferences (client_id) VALUES (?)");
            $insertPref->execute([$client_id]);
        }
    }
    
    echo "✓ Default preferences created for " . count($clients) . " clients\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>