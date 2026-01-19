<?php
require_once __DIR__ . '/includes/config.php';

// Check if users table exists
$check_table = $conn->query("SHOW TABLES LIKE 'users'");
$table_exists = $check_table->num_rows > 0;

$message = '';
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_accounts'])) {
    if (!$table_exists) {
        $error = "Users table does not exist. Please run the SQL database setup first.";
    } else {
        try {
            // Check if admin account already exists
            $check_admin = $conn->query("SELECT * FROM users WHERE email = 'admin@vehicare.com'");
            
            if ($check_admin->num_rows > 0) {
                $error = "Admin account already exists in the database.";
            } else {
                // Generate proper bcrypt hashes
                $admin_password = password_hash('VehiCare@2026Admin', PASSWORD_BCRYPT);
                $staff_password = password_hash('VehiCare@2026Staff', PASSWORD_BCRYPT);

                // Escape strings for security
                $admin_email = $conn->real_escape_string('admin@vehicare.com');
                $admin_username = $conn->real_escape_string('admin_user');
                $admin_fullname = $conn->real_escape_string('VehiCare Administrator');
                $admin_phone = $conn->real_escape_string('+1-555-0100');

                $staff_email = $conn->real_escape_string('staff@vehicare.com');
                $staff_username = $conn->real_escape_string('staff_user');
                $staff_fullname = $conn->real_escape_string('VehiCare Staff Member');
                $staff_phone = $conn->real_escape_string('+1-555-0200');

                // Insert Admin Account
                $admin_query = "INSERT INTO users (username, email, password, full_name, phone, role, status) 
                               VALUES ('$admin_username', '$admin_email', '$admin_password', '$admin_fullname', '$admin_phone', 'admin', 'active')";

                if (!$conn->query($admin_query)) {
                    throw new Exception("Error inserting admin account: " . $conn->error);
                }

                // Insert Staff Account
                $staff_query = "INSERT INTO users (username, email, password, full_name, phone, role, status) 
                               VALUES ('$staff_username', '$staff_email', '$staff_password', '$staff_fullname', '$staff_phone', 'staff', 'active')";

                if (!$conn->query($staff_query)) {
                    throw new Exception("Error inserting staff account: " . $conn->error);
                }

                $success = true;
                $message = "✅ Successfully created both admin and staff accounts!";
            }
        } catch (Exception $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VehiCare Setup - Create Admin Accounts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #d4794a 0%, #e8934b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .setup-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 700px;
            width: 100%;
            overflow: hidden;
        }

        .setup-header {
            background: linear-gradient(135deg, #0052cc 0%, #0052cc 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .setup-header i {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .setup-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin: 0;
        }

        .setup-header p {
            margin: 10px 0 0 0;
            opacity: 0.95;
            font-size: 14px;
        }

        .setup-body {
            padding: 40px;
        }

        .alert {
            border-radius: 8px;
            border: none;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .alert i {
            margin-right: 10px;
        }

        .account-info {
            background: #f8f9fa;
            border-left: 4px solid #0052cc;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }

        .account-info h4 {
            color: #0052cc;
            font-weight: 700;
            margin-bottom: 15px;
            font-size: 16px;
        }

        .account-detail {
            margin-bottom: 10px;
            font-size: 14px;
        }

        .account-detail label {
            font-weight: 600;
            color: #333;
            display: block;
            margin-bottom: 3px;
        }

        .account-detail code {
            background: white;
            padding: 6px 10px;
            border-radius: 4px;
            border: 1px solid #ddd;
            display: inline-block;
            margin-top: 3px;
            font-size: 13px;
            color: #d4794a;
        }

        .status-table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }

        .status-table th {
            background: #f0f0f0;
            padding: 10px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #ddd;
            font-size: 13px;
        }

        .status-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #ddd;
            font-size: 13px;
        }

        .status-table .status {
            font-weight: 600;
        }

        .status.success {
            color: #27ae60;
        }

        .status.error {
            color: #e74c3c;
        }

        .status.warning {
            color: #f39c12;
        }

        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 30px;
            justify-content: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, #0052cc 0%, #0052cc 100%);
            border: none;
            padding: 12px 40px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            color: white;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0, 82, 204, 0.3);
        }

        .btn-secondary {
            background: #f0f0f0;
            border: none;
            padding: 12px 40px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            color: #333;
            font-size: 14px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
        }

        .next-steps {
            background: #e7f3ff;
            border-left: 4px solid #0052cc;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .next-steps h5 {
            color: #0052cc;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .next-steps ol {
            margin: 0;
            padding-left: 20px;
            font-size: 14px;
            color: #333;
        }

        .next-steps li {
            margin-bottom: 8px;
        }

        .next-steps a {
            color: #0052cc;
            text-decoration: none;
            font-weight: 600;
        }

        .next-steps a:hover {
            text-decoration: underline;
        }

        @media (max-width: 600px) {
            .setup-body {
                padding: 25px;
            }

            .setup-header {
                padding: 30px 20px;
            }

            .button-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="setup-header">
            <i class="fas fa-cogs"></i>
            <h1>VehiCare Setup</h1>
            <p>Create Admin & Staff Accounts</p>
        </div>

        <div class="setup-body">
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                </div>

                <div class="account-info">
                    <h4><i class="fas fa-shield-alt"></i> Admin Account Created</h4>
                    <div class="account-detail">
                        <label>Email:</label>
                        <code>admin@vehicare.com</code>
                    </div>
                    <div class="account-detail">
                        <label>Password:</label>
                        <code>VehiCare@2026Admin</code>
                    </div>
                    <div class="account-detail">
                        <label>Role:</label>
                        <code>Administrator</code>
                    </div>
                </div>

                <div class="account-info">
                    <h4><i class="fas fa-wrench"></i> Staff Account Created</h4>
                    <div class="account-detail">
                        <label>Email:</label>
                        <code>staff@vehicare.com</code>
                    </div>
                    <div class="account-detail">
                        <label>Password:</label>
                        <code>VehiCare@2026Staff</code>
                    </div>
                    <div class="account-detail">
                        <label>Role:</label>
                        <code>Staff Member</code>
                    </div>
                </div>

                <div class="next-steps">
                    <h5><i class="fas fa-arrow-right"></i> Next Steps</h5>
                    <ol>
                        <li>Go to <a href="/vehicare_db/login.php">Login Page</a></li>
                        <li>Use the admin or staff credentials above to log in</li>
                        <li>Access the <a href="/vehicare_db/admins/dashboard.php">Admin Dashboard</a></li>
                        <li>Start managing your VehiCare system</li>
                    </ol>
                </div>

                <div class="button-group">
                    <a href="/vehicare_db/login.php" class="btn-primary">Go to Login</a>
                    <a href="/vehicare_db/index.php" class="btn-secondary">Back to Home</a>
                </div>

            <?php else: ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <h3 style="margin-bottom: 20px; color: #333;">Setup Information</h3>

                <p style="color: #666; margin-bottom: 20px;">
                    This setup will automatically create admin and staff accounts in your VehiCare database.
                </p>

                <div class="account-info">
                    <h4><i class="fas fa-shield-alt"></i> Admin Account</h4>
                    <div class="account-detail">
                        <label>Email:</label>
                        <code>admin@vehicare.com</code>
                    </div>
                    <div class="account-detail">
                        <label>Password:</label>
                        <code>VehiCare@2026Admin</code>
                    </div>
                </div>

                <div class="account-info">
                    <h4><i class="fas fa-wrench"></i> Staff Account</h4>
                    <div class="account-detail">
                        <label>Email:</label>
                        <code>staff@vehicare.com</code>
                    </div>
                    <div class="account-detail">
                        <label>Password:</label>
                        <code>VehiCare@2026Staff</code>
                    </div>
                </div>

                <table class="status-table">
                    <thead>
                        <tr>
                            <th>Component</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><i class="fas fa-database"></i> Database Connection</td>
                            <td class="status success"><i class="fas fa-check"></i> Connected</td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-table"></i> Users Table</td>
                            <td class="status <?php echo $table_exists ? 'success' : 'error'; ?>">
                                <i class="fas fa-<?php echo $table_exists ? 'check' : 'times'; ?>"></i>
                                <?php echo $table_exists ? 'Exists' : 'Not Found'; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <?php if ($table_exists): ?>
                    <form method="POST" style="margin-top: 30px;">
                        <button type="submit" name="create_accounts" class="btn-primary" style="width: 100%;">
                            <i class="fas fa-play"></i> Create Accounts Now
                        </button>
                    </form>
                <?php else: ?>
                    <div style="margin-top: 30px; padding: 20px; background: #fff3cd; border-radius: 8px; border-left: 4px solid #f39c12;">
                        <p style="margin: 0; color: #856404;">
                            <strong><i class="fas fa-exclamation-triangle"></i> Users table not found!</strong><br>
                            Please run the database SQL setup first. The users table must exist before creating accounts.
                        </p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
