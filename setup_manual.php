<?php


require_once __DIR__ . '/includes/config.php';

$messages = [];
$errors = [];


if (!$conn->query("SET FOREIGN_KEY_CHECKS = 0")) {
    $errors[] = "Could not disable foreign key checks: " . $conn->error;
}


$users_sql = "CREATE TABLE IF NOT EXISTS users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff', 'client') NOT NULL DEFAULT 'client',
    full_name VARCHAR(150),
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_role (role),
    INDEX idx_email (email)
)";

if ($conn->query($users_sql)) {
    $messages[] = "âœ“ Created 'users' table";
} else {
    if ($conn->errno !== 1050) {
        $errors[] = "Error creating users table: " . $conn->error;
    } else {
        $messages[] = "âœ“ 'users' table already exists";
    }
}


$profiles_sql = "CREATE TABLE IF NOT EXISTS customer_profiles (
    profile_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL UNIQUE,
    contact_number VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    province VARCHAR(100),
    postal_code VARCHAR(20),
    profile_image VARCHAR(255),
    is_profile_complete BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
)";

if ($conn->query($profiles_sql)) {
    $messages[] = "âœ“ Created 'customer_profiles' table";
} else {
    if ($conn->errno !== 1050) {
        $errors[] = "Error creating customer_profiles table: " . $conn->error;
    } else {
        $messages[] = "âœ“ 'customer_profiles' table already exists";
    }
}


$vehicles_sql = "CREATE TABLE IF NOT EXISTS vehicles (
    vehicle_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    vehicle_type VARCHAR(100) NOT NULL,
    make VARCHAR(100),
    model VARCHAR(100),
    year INT,
    plate_number VARCHAR(50) NOT NULL UNIQUE,
    color VARCHAR(50),
    mileage INT DEFAULT 0,
    description TEXT,
    status ENUM('active', 'inactive', 'maintenance') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_plate_number (plate_number),
    INDEX idx_status (status)
)";

if ($conn->query($vehicles_sql)) {
    $messages[] = "âœ“ Created 'vehicles' table";
} else {
    if ($conn->errno !== 1050) {
        $errors[] = "Error creating vehicles table: " . $conn->error;
    } else {
        $messages[] = "âœ“ 'vehicles' table already exists";
    }
}


$services_sql = "CREATE TABLE IF NOT EXISTS services (
    service_id INT PRIMARY KEY AUTO_INCREMENT,
    service_name VARCHAR(150) NOT NULL,
    description TEXT,
    estimated_duration INT,
    base_price DECIMAL(10, 2),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status)
)";

if ($conn->query($services_sql)) {
    $messages[] = "âœ“ Created 'services' table";
} else {
    if ($conn->errno !== 1050) {
        $errors[] = "Error creating services table: " . $conn->error;
    } else {
        $messages[] = "âœ“ 'services' table already exists";
    }
}


$appointments_sql = "CREATE TABLE IF NOT EXISTS appointments (
    appointment_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    service_id INT,
    appointment_type ENUM('appointment', 'walk-in') DEFAULT 'appointment',
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    status ENUM('pending', 'confirmed', 'in-progress', 'completed', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    assigned_to INT,
    queue_number INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(vehicle_id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(service_id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_vehicle_id (vehicle_id),
    INDEX idx_appointment_date (appointment_date),
    INDEX idx_status (status),
    INDEX idx_appointment_type (appointment_type)
)";

if ($conn->query($appointments_sql)) {
    $messages[] = "âœ“ Created 'appointments' table";
} else {
    if ($conn->errno !== 1050) {
        $errors[] = "Error creating appointments table: " . $conn->error;
    } else {
        $messages[] = "âœ“ 'appointments' table already exists";
    }
}


$service_history_sql = "CREATE TABLE IF NOT EXISTS service_history (
    history_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    appointment_id INT,
    service_id INT,
    service_date DATE NOT NULL,
    service_time TIME,
    service_cost DECIMAL(10, 2),
    staff_member INT,
    description TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(vehicle_id) ON DELETE CASCADE,
    FOREIGN KEY (appointment_id) REFERENCES appointments(appointment_id) ON DELETE SET NULL,
    FOREIGN KEY (service_id) REFERENCES services(service_id) ON DELETE SET NULL,
    FOREIGN KEY (staff_member) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_vehicle_id (vehicle_id),
    INDEX idx_service_date (service_date)
)";

if ($conn->query($service_history_sql)) {
    $messages[] = "âœ“ Created 'service_history' table";
} else {
    if ($conn->errno !== 1050) {
        $errors[] = "Error creating service_history table: " . $conn->error;
    } else {
        $messages[] = "âœ“ 'service_history' table already exists";
    }
}


$queue_sql = "CREATE TABLE IF NOT EXISTS queue_management (
    queue_id INT PRIMARY KEY AUTO_INCREMENT,
    appointment_id INT NOT NULL,
    queue_number INT NOT NULL,
    status ENUM('waiting', 'in-service', 'completed', 'cancelled') DEFAULT 'waiting',
    estimated_wait_time INT,
    actual_start_time TIMESTAMP NULL,
    actual_end_time TIMESTAMP NULL,
    service_bay INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(appointment_id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_queue_number (queue_number),
    INDEX idx_appointment_id (appointment_id)
)";

if ($conn->query($queue_sql)) {
    $messages[] = "âœ“ Created 'queue_management' table";
} else {
    if ($conn->errno !== 1050) {
        $errors[] = "Error creating queue_management table: " . $conn->error;
    } else {
        $messages[] = "âœ“ 'queue_management' table already exists";
    }
}


$payments_sql = "CREATE TABLE IF NOT EXISTS payments (
    payment_id INT PRIMARY KEY AUTO_INCREMENT,
    appointment_id INT,
    history_id INT,
    user_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    payment_method ENUM('cash', 'credit_card', 'debit_card', 'online') DEFAULT 'cash',
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    reference_number VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(appointment_id) ON DELETE SET NULL,
    FOREIGN KEY (history_id) REFERENCES service_history(history_id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_payment_method (payment_method)
)";

if ($conn->query($payments_sql)) {
    $messages[] = "âœ“ Created 'payments' table";
} else {
    if ($conn->errno !== 1050) {
        $errors[] = "Error creating payments table: " . $conn->error;
    } else {
        $messages[] = "âœ“ 'payments' table already exists";
    }
}


$indexes = [
    "CREATE INDEX IF NOT EXISTS idx_appointments_created_at ON appointments(created_at)",
    "CREATE INDEX IF NOT EXISTS idx_service_history_created_at ON service_history(created_at)",
    "CREATE INDEX IF NOT EXISTS idx_queue_created_at ON queue_management(created_at)",
    "CREATE INDEX IF NOT EXISTS idx_vehicles_created_at ON vehicles(created_at)",
    "CREATE INDEX IF NOT EXISTS idx_users_created_at ON users(created_at)"
];

foreach ($indexes as $index_sql) {
    if ($conn->query($index_sql)) {
        $messages[] = "âœ“ Created index";
    } else {
        if ($conn->errno !== 1061) { 
            $errors[] = "Error creating index: " . $conn->error;
        }
    }
}


$sample_services = [
    "('Oil Change', 'Regular engine oil and filter change', 30, 500.00)",
    "('Tire Rotation', 'Rotate tires for even wear', 45, 750.00)",
    "('Battery Check', 'Battery health and charging system check', 20, 300.00)",
    "('Brake Inspection', 'Complete brake system inspection', 60, 1000.00)",
    "('AC Service', 'Air conditioning system maintenance', 90, 1500.00)",
    "('General Inspection', 'Comprehensive vehicle health check', 120, 2000.00)"
];

$services_inserted = 0;
foreach ($sample_services as $service) {
    $insert_service = "INSERT IGNORE INTO services (service_name, description, estimated_duration, base_price) VALUES " . $service;
    if ($conn->query($insert_service)) {
        $services_inserted++;
    }
}

if ($services_inserted > 0) {
    $messages[] = "âœ“ Inserted $services_inserted sample services";
}


$testAdminEmail = 'admin@vehicare.com';
$testAdminPassword = password_hash('admin123', PASSWORD_BCRYPT);

$checkAdmin = $conn->query("SELECT user_id FROM users WHERE email = '$testAdminEmail'");
if ($checkAdmin && $checkAdmin->num_rows == 0) {
    $insertAdmin = "INSERT INTO users (username, email, password, full_name, role, status) 
                    VALUES ('admin', '$testAdminEmail', '$testAdminPassword', 'Administrator', 'admin', 'active')";
    
    if ($conn->query($insertAdmin)) {
        $messages[] = "âœ“ Created admin test account (admin@vehicare.com / admin123)";
    } else {
        $errors[] = "Error creating admin: " . $conn->error;
    }
} else {
    $messages[] = "âœ“ Admin account already exists";
}


$testClientEmail = 'client@vehicare.com';
$testClientPassword = password_hash('client123', PASSWORD_BCRYPT);

$checkClient = $conn->query("SELECT user_id FROM users WHERE email = '$testClientEmail'");
if ($checkClient && $checkClient->num_rows == 0) {
    $insertClient = "INSERT INTO users (username, email, password, full_name, role, status) 
                     VALUES ('testclient', '$testClientEmail', '$testClientPassword', 'Test Customer', 'client', 'active')";
    
    if ($conn->query($insertClient)) {
        $messages[] = "âœ“ Created customer test account (client@vehicare.com / client123)";
        
        
        $clientResult = $conn->query("SELECT user_id FROM users WHERE email = '$testClientEmail' LIMIT 1");
        if ($clientResult && $clientResult->num_rows > 0) {
            $client = $clientResult->fetch_assoc();
            $clientId = $client['user_id'];
            
            $createProfile = "INSERT IGNORE INTO customer_profiles (user_id, is_profile_complete) VALUES ($clientId, FALSE)";
            if ($conn->query($createProfile)) {
                $messages[] = "âœ“ Created customer profile";
            }
        }
    } else {
        $errors[] = "Error creating client: " . $conn->error;
    }
} else {
    $messages[] = "âœ“ Customer account already exists";
}


$conn->query("SET FOREIGN_KEY_CHECKS = 1");


$conn->close();


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VehiCare - Manual Setup</title>
    <link href="https:
    <style>
        body {
            background: linear-gradient(135deg, 
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Poppins', sans-serif;
            padding: 20px;
        }
        .setup-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
            max-width: 700px;
        }
        h1 {
            color: 
            margin-bottom: 30px;
            text-align: center;
        }
        .message-item {
            padding: 12px;
            margin: 8px 0;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .success-msg {
            background: 
            color: 
            border-left: 4px solid 
        }
        .error-msg {
            background: 
            color: 
            border-left: 4px solid 
        }
        .credentials-box {
            background: 
            border: 2px solid 
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
        }
        .cred-row {
            margin: 12px 0;
            font-family: 'Courier New', monospace;
        }
        .cred-label {
            font-weight: bold;
            color: 
        }
        .btn-group-setup {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 30px;
        }
        .btn-setup {
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
        }
        .btn-login {
            background: linear-gradient(135deg, 
            color: white;
        }
        .btn-login:hover {
            color: white;
            text-decoration: none;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <h1>ðŸš— VehiCare System Setup</h1>
        
        <div style="margin-bottom: 30px;">
            <?php if (!empty($messages)): ?>
                <h5 style="color: 
                <?php foreach ($messages as $msg): ?>
                    <div class="message-item success-msg">
                        <span>âœ“</span>
                        <span><?php echo htmlspecialchars($msg); ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <h5 style="color: 
                <?php foreach ($errors as $err): ?>
                    <div class="message-item error-msg">
                        <span>âœ—</span>
                        <span><?php echo htmlspecialchars($err); ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="credentials-box">
            <h5 style="color: 
            
            <h6 style="color: 
            <div class="cred-row"><span class="cred-label">Email:</span> admin@vehicare.com</div>
            <div class="cred-row"><span class="cred-label">Password:</span> admin123</div>
            
            <h6 style="color: 
            <div class="cred-row"><span class="cred-label">Email:</span> client@vehicare.com</div>
            <div class="cred-row"><span class="cred-label">Password:</span> client123</div>
        </div>
        
        <div class="btn-group-setup">
            <a href="/vehicare_db/login.php" class="btn-setup btn-login">â†’ Go to Login</a>
        </div>
        
        <div style="text-align: center; margin-top: 30px; padding: 15px; background: 
            <p style="margin: 0; font-size: 14px; color: 
                <strong>Setup Complete!</strong> You can now login with the test credentials above.
            </p>
        </div>
    </div>
</body>
</html>

