<?php
/**
 * VehiCare Authentication System - Status Check
 * Visit this page to verify the authentication system is working
 */

require_once __DIR__ . '/includes/config.php';

$status = [];

// Check if users table exists
$usersTableExists = $conn->query("SELECT 1 FROM users LIMIT 1");
if ($usersTableExists) {
    $status['users_table'] = ['status' => 'OK', 'message' => 'Users table exists'];
} else {
    $status['users_table'] = ['status' => 'ERROR', 'message' => 'Users table does not exist - Run setup.php'];
}

// Check admin user
$adminCheck = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
$adminRow = $adminCheck->fetch_assoc();
$adminCount = $adminRow['count'];
if ($adminCount > 0) {
    $status['admin_user'] = ['status' => 'OK', 'message' => "Admin users: $adminCount"];
} else {
    $status['admin_user'] = ['status' => 'WARNING', 'message' => 'No admin users found - Run setup.php'];
}

// Check total users
$userCheck = $conn->query("SELECT COUNT(*) as count FROM users");
$userRow = $userCheck->fetch_assoc();
$totalUsers = $userRow['count'];
$status['total_users'] = ['status' => 'OK', 'message' => "Total users: $totalUsers"];

// Check files exist
$files = [
    'login.php' => '/vehicare_db/login.php',
    'register.php' => '/vehicare_db/register.php',
    'setup.php' => '/vehicare_db/setup.php',
    'client/dashboard.php' => '/vehicare_db/client/dashboard.php',
    'staff/dashboard.php' => '/vehicare_db/staff/dashboard.php',
];

foreach ($files as $name => $path) {
    $fullPath = __DIR__ . str_replace('/vehicare_db', '', $path);
    if (file_exists($fullPath)) {
        $status["file_$name"] = ['status' => 'OK', 'message' => "File exists: $name"];
    } else {
        $status["file_$name"] = ['status' => 'ERROR', 'message' => "Missing file: $name"];
    }
}

// Check session
$sessionOk = session_status() !== PHP_SESSION_DISABLED;
$status['sessions'] = ['status' => $sessionOk ? 'OK' : 'ERROR', 'message' => $sessionOk ? 'Sessions enabled' : 'Sessions disabled'];

// Check password functions
$passwordFunctionsOk = function_exists('password_hash') && function_exists('password_verify');
$status['password_functions'] = ['status' => $passwordFunctionsOk ? 'OK' : 'ERROR', 'message' => $passwordFunctionsOk ? 'Password functions available' : 'Password functions missing'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VehiCare - System Status</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', Arial, sans-serif;
            background: linear-gradient(135deg, #1a3a52 0%, #2d5a7b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            width: 100%;
            max-width: 700px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #1a3a52 0%, #2d5a7b 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 14px;
        }
        
        .content {
            padding: 30px;
        }
        
        .status-item {
            display: flex;
            align-items: flex-start;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            background: #f8f9fa;
            border-left: 4px solid #e0e0e0;
        }
        
        .status-item.ok {
            background: #f0fdf4;
            border-left-color: #22c55e;
        }
        
        .status-item.error {
            background: #fef2f2;
            border-left-color: #ef4444;
        }
        
        .status-item.warning {
            background: #fffbeb;
            border-left-color: #f59e0b;
        }
        
        .status-icon {
            font-size: 20px;
            margin-right: 15px;
            width: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .status-item.ok .status-icon {
            color: #22c55e;
        }
        
        .status-item.error .status-icon {
            color: #ef4444;
        }
        
        .status-item.warning .status-icon {
            color: #f59e0b;
        }
        
        .status-details {
            flex: 1;
        }
        
        .status-details h3 {
            font-size: 14px;
            margin-bottom: 4px;
            color: #1a3a52;
            font-weight: 600;
        }
        
        .status-details p {
            font-size: 12px;
            color: #666;
            margin: 0;
        }
        
        .actions {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(255,193,7,0.3);
        }
        
        .btn-secondary {
            background: #2d5a7b;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #1a3a52;
            transform: translateY(-2px);
        }
        
        .summary {
            background: #f0f8ff;
            border-left: 4px solid #0066cc;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #333;
        }
        
        .test-accounts {
            background: #f9f5ed;
            border-left: 4px solid #ffc107;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 12px;
        }
        
        .test-accounts h3 {
            margin-bottom: 10px;
            color: #1a3a52;
            font-size: 13px;
        }
        
        .test-account {
            background: white;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 8px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-car"></i> VehiCare</h1>
            <p>Authentication System Status Check</p>
        </div>
        
        <div class="content">
            <div class="summary">
                <strong>System Status:</strong> All components checked and verified. 
                Follow the steps below to get started.
            </div>
            
            <?php foreach ($status as $key => $item): ?>
                <div class="status-item <?php echo strtolower($item['status']); ?>">
                    <div class="status-icon">
                        <?php
                        if ($item['status'] === 'OK') {
                            echo '<i class="fas fa-check-circle"></i>';
                        } elseif ($item['status'] === 'ERROR') {
                            echo '<i class="fas fa-times-circle"></i>';
                        } else {
                            echo '<i class="fas fa-exclamation-circle"></i>';
                        }
                        ?>
                    </div>
                    <div class="status-details">
                        <h3><?php echo ucfirst(str_replace('_', ' ', $key)); ?></h3>
                        <p><?php echo $item['message']; ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div class="actions">
                <a href="/vehicare_db/setup.php" class="btn btn-primary">
                    <i class="fas fa-cogs"></i> Run Setup
                </a>
                <a href="/vehicare_db/login.php" class="btn btn-secondary">
                    <i class="fas fa-sign-in-alt"></i> Go to Login
                </a>
                <a href="/vehicare_db/register.php" class="btn btn-secondary">
                    <i class="fas fa-user-plus"></i> Go to Register
                </a>
            </div>
            
            <div class="test-accounts">
                <h3><i class="fas fa-key"></i> Test Credentials</h3>
                <div class="test-account">
                    <strong>Email:</strong> admin@vehicare.com<br>
                    <strong>Password:</strong> admin123<br>
                    <strong>Role:</strong> Admin
                </div>
                <p style="margin-top: 10px; color: #666;">
                    These are created automatically when you run setup.php
                </p>
            </div>
        </div>
    </div>
</body>
</html>
