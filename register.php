<?php
session_start();
require_once __DIR__ . '/includes/config.php';

// Check if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: /vehicare_db/dashboard.php");
    exit;
}

$error = '';
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    $role = 'client'; // Only clients can register, staff and admin are created by admin

    // Custom validation
    if (empty($username)) {
        $errors[] = "Username is required.";
    } elseif (strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters.";
    } elseif (strlen($username) > 50) {
        $errors[] = "Username cannot exceed 50 characters.";
    } elseif (!preg_match('/^[a-zA-Z0-9_.-]+$/', $username)) {
        $errors[] = "Username can only contain letters, numbers, underscores, dots, and hyphens.";
    }

    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if (empty($full_name)) {
        $errors[] = "Full name is required.";
    } elseif (strlen($full_name) < 2) {
        $errors[] = "Full name must be at least 2 characters.";
    } elseif (strlen($full_name) > 100) {
        $errors[] = "Full name cannot exceed 100 characters.";
    } elseif (!preg_match('/^[a-zA-Z\s\'-]+$/', $full_name)) {
        $errors[] = "Full name can only contain letters, spaces, hyphens, and apostrophes.";
    }

    if (!empty($phone)) {
        if (!preg_match('/^[0-9\s\-\+\(\)]+$/', $phone)) {
            $errors[] = "Phone number format is invalid.";
        } elseif (strlen($phone) > 20) {
            $errors[] = "Phone number cannot exceed 20 characters.";
        }
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    } elseif (strlen($password) > 255) {
        $errors[] = "Password is too long.";
    } elseif (!preg_match('/^(?=.*[a-zA-Z])(?=.*[0-9])/', $password)) {
        $errors[] = "Password must contain both letters and numbers.";
    }

    if (empty($confirm_password)) {
        $errors[] = "Please confirm your password.";
    } elseif ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // Check if username or email already exists
    if (empty($errors)) {
        $username_escaped = $conn->real_escape_string($username);
        $email_escaped = $conn->real_escape_string($email);

        $checkUser = $conn->query("SELECT user_id FROM users WHERE username = '$username_escaped' OR email = '$email_escaped'");
        
        if ($checkUser && $checkUser->num_rows > 0) {
            $result = $checkUser->fetch_assoc();
            $errors[] = "Username or email already exists. Please use a different one.";
        }
    }

    // Register user if no errors
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $phone_escaped = $conn->real_escape_string($phone);
        $full_name_escaped = $conn->real_escape_string($full_name);

        $insertQuery = "INSERT INTO users (username, email, password, full_name, phone, role, status) 
                        VALUES ('$username_escaped', '$email_escaped', '$hashed_password', '$full_name_escaped', '$phone_escaped', '$role', 'active')";
        
        if ($conn->query($insertQuery)) {
            $success = "Registration successful! Please log in.";
            $username = '';
            $email = '';
            $full_name = '';
            $phone = '';
            $password = '';
            $confirm_password = '';
        } else {
            $errors[] = "Error: " . $conn->error;
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
    <title>VehiCare - Register</title>
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
            background: linear-gradient(135deg, #1a3a52 0%, #2d5a7b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .register-container {
            width: 100%;
            max-width: 600px;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(26, 58, 82, 0.3);
            overflow: hidden;
        }

        .register-header {
            background: linear-gradient(135deg, #1a3a52 0%, #2d5a7b 100%);
            padding: 40px 30px;
            text-align: center;
            color: #ffffff;
        }

        .register-header i {
            font-size: 50px;
            margin-bottom: 15px;
            display: inline-block;
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .register-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            color: #ffffff;
        }

        .register-header p {
            font-size: 13px;
            opacity: 0.9;
            margin: 0;
        }

        .register-body {
            padding: 40px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            font-weight: 600;
            color: #1a3a52;
            margin-bottom: 8px;
            display: block;
            font-size: 13px;
        }

        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 11px 14px;
            font-size: 14px;
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

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .form-row.full {
            grid-column: 1 / -1;
        }

        .btn-register {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 8px;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(255, 193, 7, 0.3);
            color: #ffffff;
            text-decoration: none;
        }

        .btn-register:active {
            transform: translateY(0);
        }

        .role-selector {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin: 15px 0;
        }

        .role-option {
            position: relative;
            display: none;
        }

        .role-option input[type="radio"] {
            display: none;
        }

        .role-label {
            display: block;
            padding: 15px 10px;
            text-align: center;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f8f9fa;
            font-weight: 600;
            font-size: 13px;
            color: #666;
        }

        .role-option input[type="radio"]:checked + .role-label {
            border-color: #ffc107;
            background: linear-gradient(135deg, rgba(255, 193, 7, 0.1) 0%, rgba(255, 152, 0, 0.1) 100%);
            color: #1a3a52;
        }

        .role-option.show {
            display: block;
        }

        .alert {
            border-radius: 8px;
            border: none;
            margin-bottom: 20px;
            padding: 14px;
            font-size: 13px;
        }

        .alert-danger {
            background: #fee;
            color: #c33;
            border-left: 4px solid #c33;
        }

        .alert-success {
            background: #efe;
            color: #3c3;
            border-left: 4px solid #3c3;
        }

        .text-danger {
            color: #c33;
            font-size: 12px;
            display: block;
            margin-top: 4px;
        }

        .back-home {
            text-align: center;
            margin-bottom: 15px;
        }

        .back-home a {
            color: #2d5a7b;
            text-decoration: none;
            font-size: 13px;
            transition: all 0.3s ease;
        }

        .back-home a:hover {
            color: #ffc107;
        }

        .register-footer {
            text-align: center;
            padding: 15px 40px;
            background: #f8f9fa;
            border-top: 1px solid #e0e0e0;
            font-size: 13px;
            color: #666;
        }

        .register-footer a {
            color: #2d5a7b;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .register-footer a:hover {
            color: #ffc107;
        }

        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .role-selector {
                grid-template-columns: 1fr;
            }

            .register-header h1 {
                font-size: 24px;
            }

            .register-body {
                padding: 30px 20px;
            }
        }
    </style>
</head>

<body>
    <div class="register-container">
        <div class="register-header">
            <i class="fas fa-car"></i>
            <h1>VehiCare</h1>
            <p>Create Your Account</p>
        </div>

        <div class="register-body">
            <div class="back-home">
                <a href="/vehicare_db/index.php"><i class="fas fa-arrow-left"></i> Back to Home</a>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> 
                    <div><?php echo $error; ?></div>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> 
                    <div><?php echo $success; ?> <a href="/vehicare_db/login.php">Go to Login</a></div>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="registerForm" novalidate>
                <div class="form-row">
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Choose a username"
                            value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                        <small class="text-danger" id="usernameError"></small>
                    </div>
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="your@email.com"
                            value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        <small class="text-danger" id="emailError"></small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" placeholder="Your full name"
                        value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                    <small class="text-danger" id="fullNameError"></small>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" class="form-control" id="phone" name="phone" placeholder="+1 (555) 000-0000"
                        value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    <small class="text-danger" id="phoneError"></small>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Min 6 characters">
                        <small class="text-danger" id="passwordError"></small>
                        <small style="color: #666; font-size: 11px; display: block; margin-top: 4px;">Must contain letters and numbers</small>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password *</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm password">
                        <small class="text-danger" id="confirmPasswordError"></small>
                    </div>
                </div>

                <button type="submit" class="btn-register">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </form>
        </div>

        <div class="register-footer">
            Already have an account? <a href="/vehicare_db/login.php">Sign In</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const registerForm = document.getElementById('registerForm');
        const usernameInput = document.getElementById('username');
        const emailInput = document.getElementById('email');
        const fullNameInput = document.getElementById('full_name');
        const phoneInput = document.getElementById('phone');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');

        const usernameError = document.getElementById('usernameError');
        const emailError = document.getElementById('emailError');
        const fullNameError = document.getElementById('fullNameError');
        const phoneError = document.getElementById('phoneError');
        const passwordError = document.getElementById('passwordError');
        const confirmPasswordError = document.getElementById('confirmPasswordError');

        // Real-time validation
        usernameInput.addEventListener('blur', validateUsername);
        emailInput.addEventListener('blur', validateEmail);
        fullNameInput.addEventListener('blur', validateFullName);
        phoneInput.addEventListener('blur', validatePhone);
        passwordInput.addEventListener('blur', validatePassword);
        confirmPasswordInput.addEventListener('blur', validateConfirmPassword);

        function validateUsername() {
            usernameError.textContent = '';
            const username = usernameInput.value.trim();

            if (username === '') {
                usernameError.textContent = 'Username is required.';
                return false;
            }

            if (username.length < 3) {
                usernameError.textContent = 'Username must be at least 3 characters.';
                return false;
            }

            if (username.length > 50) {
                usernameError.textContent = 'Username cannot exceed 50 characters.';
                return false;
            }

            if (!/^[a-zA-Z0-9_.-]+$/.test(username)) {
                usernameError.textContent = 'Username can only contain letters, numbers, underscores, dots, and hyphens.';
                return false;
            }

            return true;
        }

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

        function validateFullName() {
            fullNameError.textContent = '';
            const fullName = fullNameInput.value.trim();

            if (fullName === '') {
                fullNameError.textContent = 'Full name is required.';
                return false;
            }

            if (fullName.length < 2) {
                fullNameError.textContent = 'Full name must be at least 2 characters.';
                return false;
            }

            if (fullName.length > 100) {
                fullNameError.textContent = 'Full name cannot exceed 100 characters.';
                return false;
            }

            if (!/^[a-zA-Z\s\'-]+$/.test(fullName)) {
                fullNameError.textContent = 'Full name can only contain letters, spaces, hyphens, and apostrophes.';
                return false;
            }

            return true;
        }

        function validatePhone() {
            phoneError.textContent = '';
            const phone = phoneInput.value.trim();

            if (phone === '') {
                return true; // Phone is optional
            }

            if (!/^[0-9\s\-\+\(\)]+$/.test(phone)) {
                phoneError.textContent = 'Phone number format is invalid.';
                return false;
            }

            if (phone.length > 20) {
                phoneError.textContent = 'Phone number cannot exceed 20 characters.';
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

            if (!/^(?=.*[a-zA-Z])(?=.*[0-9])/.test(password)) {
                passwordError.textContent = 'Password must contain both letters and numbers.';
                return false;
            }

            return true;
        }

        function validateConfirmPassword() {
            confirmPasswordError.textContent = '';
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;

            if (confirmPassword === '') {
                confirmPasswordError.textContent = 'Please confirm your password.';
                return false;
            }

            if (password !== confirmPassword) {
                confirmPasswordError.textContent = 'Passwords do not match.';
                return false;
            }

            return true;
        }

        // Form submission validation
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const isUsernameValid = validateUsername();
            const isEmailValid = validateEmail();
            const isFullNameValid = validateFullName();
            const isPhoneValid = validatePhone();
            const isPasswordValid = validatePassword();
            const isConfirmPasswordValid = validateConfirmPassword();

            if (isUsernameValid && isEmailValid && isFullNameValid && isPhoneValid && isPasswordValid && isConfirmPasswordValid) {
                registerForm.submit();
            }
        });

        // Clear errors on input
        [usernameInput, emailInput, fullNameInput, phoneInput, passwordInput, confirmPasswordInput].forEach(input => {
            input.addEventListener('input', function() {
                const errorElement = document.getElementById(this.id + 'Error');
                if (errorElement && errorElement.textContent) {
                    errorElement.textContent = '';
                }
            });
        });
    </script>
</body>

</html>
