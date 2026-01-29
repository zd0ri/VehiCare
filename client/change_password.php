<?php
session_start();
require_once __DIR__ . '/../includes/config.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: /vehicare_db/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_password = isset($_POST['old_password']) ? $_POST['old_password'] : '';
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    $errors = [];

    if (empty($old_password)) $errors[] = "Current password is required";
    if (empty($new_password)) $errors[] = "New password is required";
    if (empty($confirm_password)) $errors[] = "Please confirm your password";
    if ($new_password !== $confirm_password) $errors[] = "Passwords do not match";
    if (strlen($new_password) < 6) $errors[] = "Password must be at least 6 characters";

    
    if (empty($errors)) {
        $query = "SELECT password FROM users WHERE user_id = $user_id";
        $result = $conn->query($query);
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (!password_verify($old_password, $user['password'])) {
                $errors[] = "Current password is incorrect";
            }
        }
    }

    if (empty($errors)) {
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $update_query = "UPDATE users SET password = '$hashed_password' WHERE user_id = $user_id";
        
        if ($conn->query($update_query)) {
            $message = "Password changed successfully!";
        } else {
            $error = "Failed to update password. Please try again.";
        }
    } else {
        $error = implode('<br>', $errors);
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div style="max-width: 600px; margin: 40px auto; padding: 0 20px;">
    <!-- Header -->
    <div style="background: linear-gradient(135deg, 
        <h1 style="margin: 0 0 10px 0;"><i class="fas fa-lock"></i> Change Password</h1>
        <p style="margin: 0; opacity: 0.9;">Update your account password</p>
    </div>

    <!-- Messages -->
    <?php if ($message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Form -->
    <div style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Current Password <span style="color: 
                <input type="password" class="form-control" name="old_password" required>
            </div>

            <div class="mb-3">
                <label class="form-label">New Password <span style="color: 
                <input type="password" class="form-control" name="new_password" required>
                <small style="color: 
            </div>

            <div class="mb-3">
                <label class="form-label">Confirm New Password <span style="color: 
                <input type="password" class="form-control" name="confirm_password" required>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary" style="background: 
                    <i class="fas fa-save"></i> Change Password
                </button>
                <a href="/vehicare_db/client/profile.php" class="btn btn-secondary" style="padding: 10px 30px;">
                    <i class="fas fa-arrow-left"></i> Back to Profile
                </a>
            </div>
        </form>
    </div>
</div>

<style>
    .form-control {
        border: 1px solid 
        border-radius: 8px;
        padding: 10px 15px;
    }
    .form-control:focus {
        border-color: 
        box-shadow: 0 0 0 0.2rem rgba(0, 82, 204, 0.25);
    }
    .form-label {
        color: 
        font-weight: 500;
        margin-bottom: 8px;
    }
    .btn {
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>

