<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

// Check if client is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: /vehicare_db/login.php");
    exit;
}

$client_id = $_SESSION['user_id'];

// Get user information
$user_query = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$user_query->bind_param("i", $client_id);
$user_query->execute();
$user = $user_query->get_result()->fetch_assoc();

// Handle form submissions
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update_profile':
            $full_name = trim($_POST['full_name']);
            $phone = trim($_POST['phone']);
            $address = trim($_POST['address']);
            
            try {
                $stmt = $conn->prepare("
                    UPDATE users 
                    SET full_name = ?, phone = ?, address = ?, updated_date = CURRENT_TIMESTAMP 
                    WHERE user_id = ?
                ");
                $stmt->bind_param("sssi", $full_name, $phone, $address, $client_id);
                
                if ($stmt->execute()) {
                    // Update session
                    $_SESSION['full_name'] = $full_name;
                    
                    // Log activity
                    log_event($client_id, "profile_updated", "Updated profile information");
                    
                    $_SESSION['success'] = "Profile updated successfully!";
                    header("Location: /vehicare_db/client/profile.php?tab=profile");
                    exit;
                }
            } catch (Exception $e) {
                $_SESSION['error'] = "Error updating profile: " . $e->getMessage();
            }
            break;
            
        case 'change_password':
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
            
            try {
                // Verify current password
                if (!password_verify($current_password, $user['password'])) {
                    throw new Exception("Current password is incorrect.");
                }
                
                if ($new_password !== $confirm_password) {
                    throw new Exception("New passwords do not match.");
                }
                
                if (strlen($new_password) < 6) {
                    throw new Exception("Password must be at least 6 characters long.");
                }
                
                // Update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ?, updated_date = CURRENT_TIMESTAMP WHERE user_id = ?");
                $stmt->bind_param("si", $hashed_password, $client_id);
                
                if ($stmt->execute()) {
                    log_event($client_id, "password_changed", "Changed account password");
                    $_SESSION['success'] = "Password changed successfully!";
                    header("Location: /vehicare_db/client/profile.php?tab=security");
                    exit;
                }
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
            }
            break;
            
        case 'update_preferences':
            // Handle preferences update
            $preferences = [
                'email_notifications' => isset($_POST['email_notifications']) ? 1 : 0,
                'sms_notifications' => isset($_POST['sms_notifications']) ? 1 : 0,
                'marketing_emails' => isset($_POST['marketing_emails']) ? 1 : 0,
                'appointment_reminders' => isset($_POST['appointment_reminders']) ? 1 : 0,
                'service_updates' => isset($_POST['service_updates']) ? 1 : 0
            ];
            
            try {
                foreach ($preferences as $pref_name => $pref_value) {
                    $stmt = $conn->prepare("
                        INSERT INTO user_preferences (user_id, preference_name, preference_value) 
                        VALUES (?, ?, ?) 
                        ON DUPLICATE KEY UPDATE 
                        preference_value = VALUES(preference_value), 
                        updated_date = CURRENT_TIMESTAMP
                    ");
                    $stmt->bind_param("iss", $client_id, $pref_name, $pref_value);
                    $stmt->execute();
                }
                
                log_event($client_id, "preferences_updated", "Updated notification preferences");
                $_SESSION['success'] = "Preferences updated successfully!";
                header("Location: /vehicare_db/client/profile.php?tab=preferences");
                exit;
            } catch (Exception $e) {
                $_SESSION['error'] = "Error updating preferences: " . $e->getMessage();
            }
            break;
    }
}

// Get user preferences
$current_preferences = [];
try {
    $prefs_result = $conn->query("SELECT preference_name, preference_value FROM user_preferences WHERE user_id = $client_id");
    while ($pref = $prefs_result->fetch_assoc()) {
        $current_preferences[$pref['preference_name']] = $pref['preference_value'];
    }
} catch (Exception $e) {
    // Table might not exist
}

// Get activity logs
$activity_logs = [];
try {
    $logs_result = $conn->query("
        SELECT * FROM client_activity_logs 
        WHERE client_id = $client_id 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    while ($log = $logs_result->fetch_assoc()) {
        $activity_logs[] = $log;
    }
} catch (Exception $e) {
    // Table might not exist
}

$active_tab = $_GET['tab'] ?? 'profile';

include __DIR__ . '/../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings - VehiCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #f8f9fa;
        }

        .profile-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .page-header {
            background: linear-gradient(135deg, #6c5ce7 0%, #a29bfe 100%);
            color: white;
            padding: 30px 40px;
            border-radius: 20px;
            margin-bottom: 30px;
            text-align: center;
        }

        .page-header h1 {
            margin: 0;
            font-weight: 700;
        }

        .profile-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .profile-sidebar {
            background: linear-gradient(135deg, #2d3436 0%, #636e72 100%);
            color: white;
            padding: 0;
        }

        .profile-nav {
            list-style: none;
            padding: 20px 0;
            margin: 0;
        }

        .profile-nav .nav-item {
            margin: 0;
        }

        .profile-nav .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 15px 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            border-radius: 0;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .profile-nav .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }

        .profile-nav .nav-link.active {
            background: rgba(255,255,255,0.15);
            color: white;
            border-left-color: #74b9ff;
        }

        .profile-content {
            padding: 30px;
        }

        .content-section {
            display: none;
        }

        .content-section.active {
            display: block;
        }

        .section-title {
            font-size: 1.5em;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ecf0f1;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #6c5ce7;
            box-shadow: 0 0 0 0.2rem rgba(108, 92, 231, 0.25);
        }

        .btn {
            border-radius: 25px;
            font-weight: 600;
            padding: 10px 25px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #6c5ce7 0%, #a29bfe 100%);
            border: none;
        }

        .user-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6c5ce7 0%, #a29bfe 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3em;
            color: white;
            margin: 0 auto 20px;
            font-weight: bold;
        }

        .user-info {
            text-align: center;
            margin-bottom: 30px;
        }

        .user-info h3 {
            margin: 0 0 5px 0;
            color: #2c3e50;
        }

        .user-info p {
            margin: 0;
            color: #7f8c8d;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .info-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            border-left: 4px solid #6c5ce7;
        }

        .info-item .label {
            font-size: 0.85em;
            color: #7f8c8d;
            font-weight: 500;
        }

        .info-item .value {
            font-weight: 600;
            color: #2c3e50;
            margin-top: 2px;
        }

        .preferences-grid {
            display: grid;
            gap: 15px;
        }

        .preference-item {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 15px;
            display: flex;
            justify-content: between;
            align-items: center;
        }

        .preference-info {
            flex: 1;
        }

        .preference-info h6 {
            margin: 0 0 5px 0;
            color: #2c3e50;
            font-weight: 600;
        }

        .preference-info p {
            margin: 0;
            color: #7f8c8d;
            font-size: 0.9em;
        }

        .form-check-input {
            margin-left: auto;
        }

        .activity-list {
            list-style: none;
            padding: 0;
        }

        .activity-item {
            background: #f8f9fa;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #6c5ce7;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .activity-content {
            flex: 1;
        }

        .activity-content .action {
            font-weight: 600;
            color: #2c3e50;
        }

        .activity-content .time {
            font-size: 0.85em;
            color: #7f8c8d;
        }

        @media (max-width: 768px) {
            .profile-card {
                margin: 0 15px;
            }

            .row {
                margin: 0;
            }

            .col-md-3, .col-md-9 {
                padding: 0;
            }

            .profile-sidebar {
                border-radius: 0;
            }

            .profile-nav {
                display: flex;
                overflow-x: auto;
                padding: 10px 0;
            }

            .profile-nav .nav-link {
                white-space: nowrap;
                padding: 10px 20px;
                margin: 0 5px;
                border-radius: 25px;
                border: none;
            }

            .profile-content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-user-cog me-3"></i>Account Settings</h1>
            <p class="mb-0">Manage your profile, preferences, and account security</p>
        </div>

        <!-- Display Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Profile Card -->
        <div class="profile-card">
            <div class="row g-0">
                <!-- Sidebar Navigation -->
                <div class="col-md-3">
                    <div class="profile-sidebar">
                        <ul class="profile-nav">
                            <li class="nav-item">
                                <a class="nav-link <?php echo $active_tab === 'profile' ? 'active' : ''; ?>" 
                                   href="/vehicare_db/client/profile.php?tab=profile">
                                    <i class="fas fa-user"></i>
                                    Profile Info
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $active_tab === 'security' ? 'active' : ''; ?>" 
                                   href="/vehicare_db/client/profile.php?tab=security">
                                    <i class="fas fa-shield-alt"></i>
                                    Security
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $active_tab === 'preferences' ? 'active' : ''; ?>" 
                                   href="/vehicare_db/client/profile.php?tab=preferences">
                                    <i class="fas fa-cog"></i>
                                    Preferences
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $active_tab === 'activity' ? 'active' : ''; ?>" 
                                   href="/vehicare_db/client/profile.php?tab=activity">
                                    <i class="fas fa-history"></i>
                                    Activity Log
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="col-md-9">
                    <div class="profile-content">
                        
                        <!-- Profile Tab -->
                        <?php if ($active_tab === 'profile'): ?>
                        <div class="content-section active">
                            <h2 class="section-title">Profile Information</h2>
                            
                            <!-- User Avatar and Basic Info -->
                            <div class="user-info">
                                <div class="user-avatar">
                                    <?php 
                                    $initials = substr($user['full_name'], 0, 1);
                                    if (strpos($user['full_name'], ' ') !== false) {
                                        $names = explode(' ', $user['full_name']);
                                        $initials = substr($names[0], 0, 1) . substr(end($names), 0, 1);
                                    }
                                    echo strtoupper($initials);
                                    ?>
                                </div>
                                <h3><?php echo htmlspecialchars($user['full_name']); ?></h3>
                                <p><?php echo htmlspecialchars($user['email']); ?></p>
                            </div>

                            <!-- Account Overview -->
                            <div class="info-grid">
                                <div class="info-item">
                                    <div class="label">Member Since</div>
                                    <div class="value"><?php echo date('M j, Y', strtotime($user['created_at'] ?? $user['created_date'] ?? date('Y-m-d'))); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="label">Last Login</div>
                                    <div class="value">
                                        <?php echo $user['last_login'] ? date('M j, Y g:i A', strtotime($user['last_login'])) : 'Never'; ?>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="label">Account Status</div>
                                    <div class="value"><?php echo ucfirst($user['status']); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="label">Email Verified</div>
                                    <div class="value"><?php echo isset($user['email_verified']) ? ($user['email_verified'] ? 'Yes' : 'No') : 'N/A'; ?></div>
                                </div>
                            </div>

                            <!-- Edit Profile Form -->
                            <form method="POST">
                                <input type="hidden" name="action" value="update_profile">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="full_name" class="form-label">Full Name</label>
                                            <input type="text" 
                                                   name="full_name" 
                                                   id="full_name" 
                                                   class="form-control" 
                                                   value="<?php echo htmlspecialchars($user['full_name']); ?>" 
                                                   required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email" class="form-label">Email Address</label>
                                            <input type="email" 
                                                   class="form-control" 
                                                   value="<?php echo htmlspecialchars($user['email']); ?>" 
                                                   disabled>
                                            <small class="text-muted">Contact support to change email</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" 
                                           name="phone" 
                                           id="phone" 
                                           class="form-control" 
                                           value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" 
                                           placeholder="+63 123 456 7890">
                                </div>

                                <div class="form-group">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea name="address" 
                                              id="address" 
                                              class="form-control" 
                                              rows="3" 
                                              placeholder="Your complete address"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Update Profile
                                </button>
                            </form>
                        </div>
                        <?php endif; ?>

                        <!-- Security Tab -->
                        <?php if ($active_tab === 'security'): ?>
                        <div class="content-section active">
                            <h2 class="section-title">Security Settings</h2>
                            
                            <!-- Change Password Form -->
                            <form method="POST">
                                <input type="hidden" name="action" value="change_password">
                                
                                <div class="form-group">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input type="password" 
                                           name="current_password" 
                                           id="current_password" 
                                           class="form-control" 
                                           required>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="new_password" class="form-label">New Password</label>
                                            <input type="password" 
                                                   name="new_password" 
                                                   id="new_password" 
                                                   class="form-control" 
                                                   required
                                                   minlength="6">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                                            <input type="password" 
                                                   name="confirm_password" 
                                                   id="confirm_password" 
                                                   class="form-control" 
                                                   required
                                                   minlength="6">
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-key me-2"></i>Change Password
                                </button>
                            </form>

                            <!-- Security Info -->
                            <div class="mt-4 p-3" style="background: #f8f9fa; border-radius: 10px;">
                                <h6><i class="fas fa-shield-alt me-2"></i>Security Tips</h6>
                                <ul class="mb-0 text-muted">
                                    <li>Use a strong password with at least 8 characters</li>
                                    <li>Include uppercase, lowercase, numbers, and symbols</li>
                                    <li>Don't reuse passwords from other accounts</li>
                                    <li>Change your password regularly</li>
                                </ul>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Preferences Tab -->
                        <?php if ($active_tab === 'preferences'): ?>
                        <div class="content-section active">
                            <h2 class="section-title">Notification Preferences</h2>
                            
                            <form method="POST">
                                <input type="hidden" name="action" value="update_preferences">
                                
                                <div class="preferences-grid">
                                    <div class="preference-item">
                                        <div class="preference-info">
                                            <h6>Email Notifications</h6>
                                            <p>Receive appointment confirmations and updates via email</p>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   name="email_notifications" 
                                                   <?php echo ($current_preferences['email_notifications'] ?? 1) ? 'checked' : ''; ?>>
                                        </div>
                                    </div>

                                    <div class="preference-item">
                                        <div class="preference-info">
                                            <h6>SMS Notifications</h6>
                                            <p>Get text message alerts for urgent updates</p>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   name="sms_notifications" 
                                                   <?php echo ($current_preferences['sms_notifications'] ?? 0) ? 'checked' : ''; ?>>
                                        </div>
                                    </div>

                                    <div class="preference-item">
                                        <div class="preference-info">
                                            <h6>Appointment Reminders</h6>
                                            <p>Receive reminders about upcoming appointments</p>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   name="appointment_reminders" 
                                                   <?php echo ($current_preferences['appointment_reminders'] ?? 1) ? 'checked' : ''; ?>>
                                        </div>
                                    </div>

                                    <div class="preference-item">
                                        <div class="preference-info">
                                            <h6>Service Updates</h6>
                                            <p>Get notified about service progress and completion</p>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   name="service_updates" 
                                                   <?php echo ($current_preferences['service_updates'] ?? 1) ? 'checked' : ''; ?>>
                                        </div>
                                    </div>

                                    <div class="preference-item">
                                        <div class="preference-info">
                                            <h6>Marketing Emails</h6>
                                            <p>Receive promotional offers and service recommendations</p>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   name="marketing_emails" 
                                                   <?php echo ($current_preferences['marketing_emails'] ?? 0) ? 'checked' : ''; ?>>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary mt-3">
                                    <i class="fas fa-save me-2"></i>Save Preferences
                                </button>
                            </form>
                        </div>
                        <?php endif; ?>

                        <!-- Activity Tab -->
                        <?php if ($active_tab === 'activity'): ?>
                        <div class="content-section active">
                            <h2 class="section-title">Recent Activity</h2>
                            
                            <?php if (!empty($activity_logs)): ?>
                                <ul class="activity-list">
                                    <?php foreach ($activity_logs as $log): ?>
                                    <li class="activity-item">
                                        <div class="activity-icon">
                                            <i class="fas fa-circle"></i>
                                        </div>
                                        <div class="activity-content">
                                            <div class="action"><?php echo htmlspecialchars($log['description'] ?? $log['action']); ?></div>
                                            <div class="time"><?php echo date('M j, Y g:i A', strtotime($log['created_at'])); ?></div>
                                        </div>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No recent activity to display</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>

        <!-- Back to Dashboard -->
        <div class="text-center mt-4">
            <a href="/vehicare_db/client/dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <script>
        // Password confirmation validation
        document.getElementById('confirm_password')?.addEventListener('input', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            
            if (newPassword !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php include __DIR__ . '/../includes/footer.php'; ?>


    }

    .profile-picture {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 4px solid white;
        object-fit: cover;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .profile-picture-placeholder {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        border: 4px solid white;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .profile-picture-placeholder i {
        font-size: 3em;
        color: white;
    }

    .profile-info h1 {
        margin: 0 0 10px 0;
        font-size: 2.2em;
        font-weight: 700;
    }

    .profile-info p {
        margin: 5px 0;
        opacity: 0.95;
    }

    .alert {
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: none;
    }

    .alert.show {
        display: block;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert-error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .section {
        background: white;
        border-radius: 12px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .section h2 {
        margin: 0 0 25px 0;
        color: #1a3a52;
        font-size: 1.6em;
        font-weight: 700;
        padding-bottom: 15px;
        border-bottom: 2px solid #f0f0f0;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    label {
        display: block;
        margin-bottom: 8px;
        color: #333;
        font-weight: 600;
        font-size: 0.95em;
    }

    input[type="text"],
    input[type="email"],
    input[type="password"],
    input[type="tel"],
    textarea,
    select {
        width: 100%;
        padding: 12px;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        font-size: 0.95em;
        font-family: 'Poppins', sans-serif;
        transition: all 0.3s ease;
    }

    input[type="text"]:focus,
    input[type="email"]:focus,
    input[type="password"]:focus,
    input[type="tel"]:focus,
    textarea:focus,
    select:focus {
        border-color: #2d5a7b;
        box-shadow: 0 0 0 3px rgba(45, 90, 123, 0.1);
        outline: none;
    }

    textarea {
        resize: vertical;
        min-height: 100px;
    }

    .btn {
        padding: 12px 30px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.95em;
    }

    .btn-primary {
        background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
        color: white;
        box-shadow: 0 4px 10px rgba(255, 152, 0, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(255, 152, 0, 0.4);
    }

    .btn-secondary {
        background: #f0f0f0;
        color: #333;
        border: 1px solid #e0e0e0;
    }

    .btn-secondary:hover {
        background: #e0e0e0;
    }

    .upload-section {
        border: 2px dashed #2d5a7b;
        border-radius: 8px;
        padding: 30px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: #f9f9f9;
    }

    .upload-section:hover {
        background: #f0f8ff;
        border-color: #ff9800;
    }

    .upload-section input[type="file"] {
        display: none;
    }

    .upload-section i {
        font-size: 2.5em;
        color: #2d5a7b;
        margin-bottom: 10px;
        display: block;
    }

    .upload-section p {
        margin: 0;
        color: #666;
    }

    .password-strength {
        height: 5px;
        background: #e0e0e0;
        border-radius: 3px;
        margin-top: 5px;
        overflow: hidden;
    }

    .password-strength-bar {
        height: 100%;
        width: 0%;
        background: #e74c3c;
        border-radius: 3px;
        transition: all 0.3s ease;
    }

    .password-strength-bar.weak {
        width: 33%;
        background: #e74c3c;
    }

    .password-strength-bar.fair {
        width: 66%;
        background: #f39c12;
    }

    .password-strength-bar.strong {
        width: 100%;
        background: #27ae60;
    }

    .button-group {
        display: flex;
        gap: 10px;
        margin-top: 25px;
    }

    @media (max-width: 768px) {
        .profile-header {
            flex-direction: column;
            text-align: center;
        }

        .profile-info h1 {
            font-size: 1.6em;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .section {
            padding: 20px;
        }
    }
</style>

<div class="profile-container">
    <!-- Profile Header -->
    <div class="profile-header">
        <div class="profile-picture-wrapper">
            <?php if (isset($user['profile_picture']) && $user['profile_picture'] && file_exists(__DIR__ . '/../uploads/profile_pictures/' . $user['profile_picture'])): ?>
                <img src="/vehicare_db/uploads/profile_pictures/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" class="profile-picture">
            <?php else: ?>
                <div class="profile-picture-placeholder">
                    <i class="fas fa-user"></i>
                </div>
            <?php endif; ?>
        </div>
        <div class="profile-info">
            <h1><?php echo htmlspecialchars($user['full_name']); ?></h1>
            <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($user['phone'] ?: 'Not provided'); ?></p>
            <p><i class="fas fa-calendar"></i> Member since <?php echo date('F j, Y', strtotime($user['created_at'] ?? $user['created_date'] ?? date('Y-m-d'))); ?></p>
        </div>
    </div>

    <!-- Messages -->
    <div id="message" class="alert"></div>

    <!-- Profile Update Section -->
    <div class="section">
        <h2>Profile Picture</h2>
        <form id="profilePictureForm" enctype="multipart/form-data">
            <div class="upload-section" onclick="document.getElementById('fileInput').click();">
                <input type="file" id="fileInput" name="profile_picture" accept="image/*">
                <i class="fas fa-cloud-upload-alt"></i>
                <p><strong>Click to upload</strong> or drag and drop</p>
                <p style="font-size: 0.85em; color: #999;">PNG, JPG, GIF, WebP (Max 5MB)</p>
            </div>
            <div style="margin-top: 20px; text-align: center;">
                <p style="color: #666; font-size: 0.9em;" id="uploadStatus"></p>
                <div id="uploadProgress" style="display: none; margin-top: 10px;">
                    <div style="width: 100%; background: #e0e0e0; border-radius: 5px; height: 8px;">
                        <div id="progressBar" style="width: 0%; height: 100%; background: #27ae60; border-radius: 5px; transition: width 0.3s;"></div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Personal Information Section -->
    <div class="section">
        <h2>Personal Information</h2>
        <form method="POST">
            <input type="hidden" name="action" value="update_profile">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?: ''); ?>">
                </div>
                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="state">State/Province</label>
                    <input type="text" id="state" name="state" value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="zip_code">Zip Code</label>
                    <input type="text" id="zip_code" name="zip_code" value="<?php echo htmlspecialchars($user['zip_code'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
            </div>

            <div class="button-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Changes
                </button>
                <button type="reset" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Reset
                </button>
            </div>
        </form>
    </div>


            </div>

            <div class="form-group">
                <label for="new_password">New Password *</label>
                <input type="password" id="new_password" name="new_password" required onkeyup="checkPasswordStrength(this.value)">
                <div class="password-strength">
                    <div id="passwordStrengthBar" class="password-strength-bar"></div>
                </div>
                <small style="color: #666; display: block; margin-top: 5px;">
                    Password must be at least 6 characters long
                </small>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm New Password *</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>


</div>

<script>
    // Show message
    <?php if ($message): ?>
    document.addEventListener('DOMContentLoaded', function() {
        const messageEl = document.getElementById('message');
        messageEl.textContent = '<?php echo $message; ?>';
        messageEl.className = 'alert show alert-<?php echo $message_type; ?>';
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            messageEl.className = 'alert';
        }, 5000);
    });
    <?php endif; ?>

    // Password strength checker
    function checkPasswordStrength(password) {
        const bar = document.getElementById('passwordStrengthBar');
        let strength = 'weak';
        
        if (password.length >= 8 && /[A-Z]/.test(password) && /[0-9]/.test(password) && /[!@#$%^&*]/.test(password)) {
            strength = 'strong';
        } else if (password.length >= 6 && (/[A-Z]/.test(password) || /[0-9]/.test(password))) {
            strength = 'fair';
        }
        
        bar.className = 'password-strength-bar ' + strength;
    }

    // File upload handler
    const fileInput = document.getElementById('fileInput');
    const uploadSection = document.querySelector('.upload-section');
    const uploadStatus = document.getElementById('uploadStatus');

    // Click to upload
    uploadSection.addEventListener('click', () => fileInput.click());

    // Drag and drop
    uploadSection.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadSection.style.background = '#f0f8ff';
        uploadSection.style.borderColor = '#ff9800';
    });

    uploadSection.addEventListener('dragleave', () => {
        uploadSection.style.background = '#f9f9f9';
        uploadSection.style.borderColor = '#2d5a7b';
    });

    uploadSection.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadSection.style.background = '#f9f9f9';
        uploadSection.style.borderColor = '#2d5a7b';
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            uploadFile(files[0]);
        }
    });

    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            uploadFile(e.target.files[0]);
        }
    });

    function uploadFile(file) {
        const formData = new FormData();
        formData.append('profile_picture', file);

        const messageEl = document.getElementById('message');
        
        // Disable file input
        fileInput.disabled = true;
        uploadSection.style.opacity = '0.6';
        uploadStatus.textContent = 'Uploading...';

        const xhr = new XMLHttpRequest();

        xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
                const percentComplete = (e.loaded / e.total) * 100;
                document.getElementById('progressBar').style.width = percentComplete + '%';
                document.getElementById('uploadProgress').style.display = 'block';
            }
        });

        xhr.addEventListener('load', () => {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    
                    fileInput.disabled = false;
                    uploadSection.style.opacity = '1';

                    if (response.success) {
                        uploadStatus.textContent = response.message;
                        uploadStatus.style.color = '#155724';
                        messageEl.textContent = response.message;
                        messageEl.className = 'alert show alert-success';
                        
                        // Reload profile picture
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        uploadStatus.textContent = response.message;
                        uploadStatus.style.color = '#721c24';
                        messageEl.textContent = response.message;
                        messageEl.className = 'alert show alert-error';
                    }

                    // Auto-hide after 5 seconds
                    setTimeout(() => {
                        messageEl.className = 'alert';
                        uploadStatus.textContent = '';
                        document.getElementById('uploadProgress').style.display = 'none';
                        document.getElementById('progressBar').style.width = '0%';
                    }, 5000);
                } catch (e) {
                    fileInput.disabled = false;
                    uploadSection.style.opacity = '1';
                    uploadStatus.textContent = 'Error uploading file';
                    uploadStatus.style.color = '#721c24';
                    messageEl.textContent = 'Error uploading file';
                    messageEl.className = 'alert show alert-error';
                }
            }


