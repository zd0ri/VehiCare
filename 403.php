<?php 
/**
 * 403 Forbidden Page
 * Shown when user tries to access unauthorized content
 */

// Set HTTP status code
http_response_code(403);

// Include authentication helpers
require_once __DIR__ . '/app/helpers/auth_helpers.php';

// Get user info if authenticated
$current_user = getCurrentUser();
$user_role = getUserRole();

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Forbidden - VehiCare</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- Styles -->
    <link rel="stylesheet" href="/vehicare_db/assets/css/style.css">
    
    <style>
        .error-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Poppins', sans-serif;
        }
        
        .error-container {
            text-align: center;
            background: white;
            padding: 60px 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 90%;
        }
        
        .error-icon {
            font-size: 5rem;
            color: #dc143c;
            margin-bottom: 20px;
            animation: pulse 2s infinite;
        }
        
        .error-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 15px;
        }
        
        .error-code {
            font-size: 6rem;
            font-weight: 800;
            color: #dc143c;
            line-height: 1;
            margin-bottom: 20px;
            opacity: 0.3;
        }
        
        .error-message {
            color: #7f8c8d;
            font-size: 1.1rem;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .error-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .error-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 25px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #dc143c, #ff1744);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(220, 20, 60, 0.3);
            color: white;
            text-decoration: none;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
            color: white;
            text-decoration: none;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        @media (max-width: 768px) {
            .error-container {
                padding: 40px 25px;
            }
            
            .error-title {
                font-size: 2rem;
            }
            
            .error-code {
                font-size: 4rem;
            }
            
            .error-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="error-page">
        <div class="error-container">
            <div class="error-icon">
                <i class="fas fa-ban"></i>
            </div>
            
            <div class="error-code">403</div>
            
            <h1 class="error-title">Access Forbidden</h1>
            
            <p class="error-message">
                Sorry, you don't have permission to access this resource. 
                Your current role may not have the necessary privileges.
            </p>
            
            <?php if ($current_user): ?>
            <div class="error-details">
                <strong>Current User:</strong> <?php echo htmlspecialchars($current_user['full_name']); ?><br>
                <strong>Role:</strong> <?php echo htmlspecialchars(ucfirst($user_role)); ?><br>
                <strong>Email:</strong> <?php echo htmlspecialchars($current_user['email']); ?>
            </div>
            <?php else: ?>
            <div class="error-details">
                You are not currently logged in. Please log in to access this resource.
            </div>
            <?php endif; ?>
            
            <div class="error-actions">
                <?php if ($current_user): ?>
                    <?php if ($user_role === 'admin'): ?>
                        <a href="/vehicare_db/admins/dashboard.php" class="btn btn-primary">
                            <i class="fas fa-tachometer-alt"></i>
                            Admin Dashboard
                        </a>
                    <?php elseif ($user_role === 'staff'): ?>
                        <a href="/vehicare_db/staff/dashboard.php" class="btn btn-primary">
                            <i class="fas fa-tasks"></i>
                            Staff Dashboard
                        </a>
                    <?php else: ?>
                        <a href="/vehicare_db/client/dashboard.php" class="btn btn-primary">
                            <i class="fas fa-user"></i>
                            My Dashboard
                        </a>
                    <?php endif; ?>
                    
                    <a href="/vehicare_db/logout.php" class="btn btn-secondary">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                <?php else: ?>
                    <a href="/vehicare_db/login.php" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i>
                        Login
                    </a>
                    
                    <a href="/vehicare_db/index.php" class="btn btn-secondary">
                        <i class="fas fa-home"></i>
                        Home
                    </a>
                <?php endif; ?>
            </div>
            
            <div style="margin-top: 30px; color: #adb5bd; font-size: 0.8rem;">
                If you believe this is an error, please contact your administrator.
            </div>
        </div>
    </div>
    
    <script>
        // Auto-redirect after 30 seconds if user is not logged in
        <?php if (!$current_user): ?>
        setTimeout(function() {
            window.location.href = '/vehicare_db/login.php';
        }, 30000);
        <?php endif; ?>
    </script>
</body>
</html>