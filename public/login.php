<?php
/**
 * Login Page
 * VehiCare Service Management System
 */

require_once __DIR__ . '/app/middleware/SessionMiddleware.php';

// Redirect if already authenticated
if (is_authenticated()) {
    $user = current_user();
    $redirect_url = match($user['role']) {
        'admin' => '/vehicare_db/app/views/admin/dashboard.php',
        'staff' => '/vehicare_db/app/views/staff/dashboard.php',
        'client' => '/vehicare_db/app/views/client/dashboard.php',
        default => '/vehicare_db/index.php'
    };
    header("Location: $redirect_url");
    exit;
}

$error_message = '';
$success_message = '';

// Handle form submission
if ($_POST) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error_message = 'Security token mismatch. Please try again.';
    } else {
        $email = sanitize_input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember_me = isset($_POST['remember_me']);
        
        if (empty($email) || empty($password)) {
            $error_message = 'Please fill in all fields.';
        } else {
            $result = auth()->login($email, $password, $remember_me);
            
            if ($result['success']) {
                $user = $result['user'];
                $redirect_url = match($user['role']) {
                    'admin' => '/vehicare_db/app/views/admin/dashboard.php',
                    'staff' => '/vehicare_db/app/views/staff/dashboard.php',
                    'client' => '/vehicare_db/app/views/client/dashboard.php',
                    default => '/vehicare_db/index.php'
                };
                header("Location: $redirect_url");
                exit;
            } else {
                $error_message = $result['message'];
            }
        }
    }
}

// Check for flash messages
$flash_message = get_flash_message();
if ($flash_message) {
    if ($flash_message['type'] === 'success') {
        $success_message = $flash_message['text'];
    } else {
        $error_message = $flash_message['text'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/vehicare_db/assets/css/auth.css">
</head>
<body class="auth-body">
    <div class="auth-container">
        <div class="auth-card">
            <!-- Brand Header -->
            <div class="auth-header text-center mb-4">
                <div class="brand-logo">
                    <i class="fas fa-car text-primary"></i>
                </div>
                <h2 class="brand-name">VehiCare</h2>
                <p class="brand-tagline">Vehicle Service Management</p>
            </div>

            <!-- Login Form -->
            <div class="auth-form">
                <h3 class="form-title">Sign In</h3>
                <p class="form-subtitle">Welcome back! Please sign in to your account.</p>

                <?php if ($error_message): ?>
                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success_message): ?>
                    <div class="alert alert-success d-flex align-items-center" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    
                    <div class="form-group mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                                   placeholder="Enter your email" required>
                            <div class="invalid-feedback">
                                Please provide a valid email address.
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Enter your password" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                            <div class="invalid-feedback">
                                Please provide your password.
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me" 
                                       <?php echo isset($_POST['remember_me']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="remember_me">
                                    Remember me for 30 days
                                </label>
                            </div>
                            <a href="/vehicare_db/forgot-password.php" class="forgot-password-link">
                                Forgot Password?
                            </a>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Sign In
                    </button>
                </form>

                <!-- Demo Accounts -->
                <div class="demo-accounts mt-4">
                    <h6 class="demo-title">Demo Accounts:</h6>
                    <div class="demo-grid">
                        <div class="demo-account" onclick="fillDemoAccount('admin@vehicare.com', 'password')">
                            <i class="fas fa-user-shield text-danger"></i>
                            <span class="demo-role">Admin</span>
                            <small>Full Access</small>
                        </div>
                        <div class="demo-account" onclick="fillDemoAccount('john.mechanic@vehicare.com', 'password')">
                            <i class="fas fa-tools text-warning"></i>
                            <span class="demo-role">Staff</span>
                            <small>Technician</small>
                        </div>
                        <div class="demo-account" onclick="fillDemoAccount('client1@email.com', 'password')">
                            <i class="fas fa-user text-info"></i>
                            <span class="demo-role">Client</span>
                            <small>Customer</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="auth-footer text-center">
                <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
                <p class="version">Version <?php echo APP_VERSION; ?></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Bootstrap form validation
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();

        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordField = document.getElementById('password');
            const toggleIcon = this.querySelector('i');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        });

        // Fill demo account credentials
        function fillDemoAccount(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
            document.getElementById('email').focus();
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                if (alert.classList.contains('alert-success')) {
                    alert.style.transition = 'opacity 0.5s';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                }
            });
        }, 5000);
    </script>
</body>
</html>