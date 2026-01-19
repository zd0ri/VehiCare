<?php
session_start();
require_once __DIR__ . '/includes/config.php';

// Check if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: /vehicare_db/admins/dashboard.php");
    } else {
        header("Location: /vehicare_db/dashboard.php");
    }
    exit;
}

$error = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Custom validation
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }

    if (empty($errors)) {
        // Query users table for credentials
        $email_escaped = $conn->real_escape_string($email);
        $query = "SELECT * FROM users WHERE email = '$email_escaped' LIMIT 1";
        $result = $conn->query($query);

        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Check if user is active
            if ($user['status'] !== 'active') {
                $errors[] = "Your account is inactive. Please contact administration.";
            } elseif (password_verify($password, $user['password'])) {
                // Update last login timestamp
                $update_login = "UPDATE users SET last_login = NOW() WHERE user_id = " . $user['user_id'];
                $conn->query($update_login);

                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];

                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header("Location: /vehicare_db/admins/dashboard.php");
                } elseif ($user['role'] === 'staff') {
                    header("Location: /vehicare_db/staff/dashboard.php");
                } else {
                    header("Location: /vehicare_db/client/dashboard.php");
                }
                exit;
            } else {
                $errors[] = "Invalid email or password.";
            }
        } else {
            $errors[] = "Invalid email or password.";
        }
    }

    if (!empty($errors)) {
        $error = implode('<br>', $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VehiCare Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
        integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5a623 0%, #f5a623 50%, #ff6b6b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 500px;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(26, 58, 82, 0.3);
            overflow: hidden;
        }

        .login-header {
            background: linear-gradient(135deg, #1a3a52 0%, #2d5a7b 100%);
            padding: 50px 30px;
            text-align: center;
            color: #ffffff;
        }

        .login-header i {
            font-size: 60px;
            margin-bottom: 20px;
            display: inline-block;
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .login-header h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #ffffff;
        }

        .login-header p {
            font-size: 14px;
            opacity: 0.9;
            margin: 0;
        }

        .login-body {
            padding: 45px 40px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            font-weight: 600;
            color: #1a3a52;
            margin-bottom: 10px;
            display: block;
            font-size: 14px;
        }

        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-control:focus {
            border-color: #ffc107;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.1);
            outline: none;
        }

        .form-control::placeholder {
            color: #999;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(255, 193, 7, 0.3);
            color: #ffffff;
            text-decoration: none;
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            font-size: 14px;
        }

        .remember-forgot a {
            color: #2d5a7b;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .remember-forgot a:hover {
            color: #ffc107;
        }

        .form-check {
            margin-bottom: 0;
        }

        .form-check-input {
            width: 18px;
            height: 18px;
            border: 2px solid #e0e0e0;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 3px;
        }

        .form-check-input:checked {
            background-color: #ffc107;
            border-color: #ffc107;
        }

        .form-check-label {
            margin-left: 8px;
            cursor: pointer;
            user-select: none;
            color: #555;
            font-weight: 500;
        }

        .alert {
            border-radius: 8px;
            border: none;
            margin-bottom: 24px;
            padding: 16px;
            font-size: 14px;
        }

        .alert-danger {
            background: #fee;
            color: #c33;
            border-left: 4px solid #c33;
        }

        .alert-info {
            background: #e7f3ff;
            color: #0066cc;
            border-left: 4px solid #0066cc;
        }

        .login-footer {
            text-align: center;
            padding: 20px 40px;
            background: #f8f9fa;
            border-top: 1px solid #e0e0e0;
            font-size: 14px;
            color: #666;
        }

        .login-footer a {
            color: #2d5a7b;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-footer a:hover {
            color: #ffc107;
        }

        .back-home {
            display: inline-block;
            margin-bottom: 20px;
            text-align: center;
        }

        .back-home a {
            color: #2d5a7b;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .back-home a:hover {
            color: #ffc107;
        }

        @media (max-width: 480px) {
            .login-container {
                border-radius: 12px;
            }

            .login-header {
                padding: 40px 25px;
            }

            .login-header h1 {
                font-size: 26px;
            }

            .login-body {
                padding: 30px 25px;
            }

            .remember-forgot {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-header">
            <i class="fas fa-car"></i>
            <h1>VehiCare</h1>
            <p>Admin Dashboard Login</p>
        </div>

        <div class="login-body">
            <div class="back-home">
                <a href="/vehicare_db/index.php"><i class="fas fa-arrow-left"></i> Back to Home</a>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> 
                    <div id="errorMessages"><?php echo $error; ?></div>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="loginForm" novalidate>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email"
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    <small class="text-danger" id="emailError"></small>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password"
                        placeholder="Enter your password">
                    <small class="text-danger" id="passwordError"></small>
                </div>

                <div class="remember-forgot">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                        <label class="form-check-label" for="remember">
                            Remember me
                        </label>
                    </div>
                    <a href="#forgot">Forgot password?</a>
                </div>

                <button type="submit" class="btn-login" id="loginBtn">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>
        </div>

        <div class="login-footer">
            Don't have access? Contact your <a href="mailto:admin@vehicare.com">administrator</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        const loginForm = document.getElementById('loginForm');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const emailError = document.getElementById('emailError');
        const passwordError = document.getElementById('passwordError');
        const loginBtn = document.getElementById('loginBtn');

        // Real-time validation
        emailInput.addEventListener('blur', validateEmail);
        passwordInput.addEventListener('blur', validatePassword);

        function validateEmail() {
            emailError.textContent = '';
            const email = emailInput.value.trim();

            if (email === '') {
                emailError.textContent = 'Email is required.';
                return false;
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                emailError.textContent = 'Please enter a valid email address.';
                return false;
            }

            return true;
        }

        function validatePassword() {
            passwordError.textContent = '';
            const password = passwordInput.value;

            if (password === '') {
                passwordError.textContent = 'Password is required.';
                return false;
            }

            if (password.length < 6) {
                passwordError.textContent = 'Password must be at least 6 characters.';
                return false;
            }

            return true;
        }

        // Form submission validation
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();

            emailError.textContent = '';
            passwordError.textContent = '';

            const isEmailValid = validateEmail();
            const isPasswordValid = validatePassword();

            if (isEmailValid && isPasswordValid) {
                loginForm.submit();
            }
        });

        // Clear error on input
        emailInput.addEventListener('input', function() {
            if (emailError.textContent) {
                emailError.textContent = '';
            }
        });

        passwordInput.addEventListener('input', function() {
            if (passwordError.textContent) {
                passwordError.textContent = '';
            }
        });
    </script>
