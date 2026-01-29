<?php
session_start();
require_once __DIR__ . '/../includes/config.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: /vehicare_db/login.php");
    exit;
}


$user_id = $_SESSION['user_id'];
$profile_data = null;
$profile_complete = false;

$query = "SELECT * FROM customer_profiles WHERE user_id = $user_id";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $profile_data = $result->fetch_assoc();
    $profile_complete = $profile_data['is_profile_complete'];
}


$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contact_number = isset($_POST['contact_number']) ? trim($_POST['contact_number']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $city = isset($_POST['city']) ? trim($_POST['city']) : '';
    $province = isset($_POST['province']) ? trim($_POST['province']) : '';
    $postal_code = isset($_POST['postal_code']) ? trim($_POST['postal_code']) : '';

    $errors = [];

    if (empty($full_name)) $errors[] = "Full name is required";
    if (empty($email)) $errors[] = "Email is required";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    if (empty($contact_number)) $errors[] = "Contact number is required";
    if (empty($address)) $errors[] = "Address is required";
    if (empty($city)) $errors[] = "City is required";
    if (empty($province)) $errors[] = "Province is required";

    if (empty($errors)) {
        
        if ($profile_data) {
            $update_query = "UPDATE customer_profiles SET 
                            contact_number = '$contact_number',
                            address = '$address',
                            city = '$city',
                            province = '$province',
                            postal_code = '$postal_code',
                            is_profile_complete = TRUE,
                            updated_at = NOW()
                            WHERE user_id = $user_id";
        } else {
            $insert_query = "INSERT INTO customer_profiles 
                            (user_id, contact_number, address, city, province, postal_code, is_profile_complete)
                            VALUES ($user_id, '$contact_number', '$address', '$city', '$province', '$postal_code', TRUE)";
            $conn->query($insert_query);
            $update_query = null;
        }

        if ($update_query) {
            $conn->query($update_query);
        }

        
        $update_user = "UPDATE users SET full_name = '$full_name', email = '$email' WHERE user_id = $user_id";
        $conn->query($update_user);

        $_SESSION['full_name'] = $full_name;
        $_SESSION['email'] = $email;

        $message = "Profile updated successfully!";
        $profile_complete = true;

        
        $query = "SELECT * FROM customer_profiles WHERE user_id = $user_id";
        $result = $conn->query($query);
        if ($result && $result->num_rows > 0) {
            $profile_data = $result->fetch_assoc();
        }
    } else {
        $error = implode('<br>', $errors);
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
    <!-- Header -->
    <div style="background: linear-gradient(135deg, 
        <h1 style="margin: 0 0 10px 0;"><i class="fas fa-user-circle"></i> My Profile</h1>
        <p style="margin: 0; opacity: 0.9;">Manage your personal information and account details</p>
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

    <!-- Profile Status -->
    <div style="background: white; padding: 20px; border-radius: 12px; margin-bottom: 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
        <div style="display: flex; align-items: center; gap: 15px;">
            <div style="background: <?php echo $profile_complete ? '#28a745' : '#ffc107'; ?>; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-<?php echo $profile_complete ? 'check' : 'exclamation'; ?>" style="color: white; font-size: 24px;"></i>
            </div>
            <div>
                <h5 style="margin: 0; color: #333;">Profile Status</h5>
                <p style="margin: 5px 0 0 0; color: #666;">
                    <?php echo $profile_complete ? 'Your profile is complete. You can now book appointments and manage your vehicles.' : 'Please complete your profile to use our services.'; ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Profile Form -->
    <div style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
        <h5 style="margin-bottom: 20px; color: 
        <form method="POST" action="">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Full Name <span style="color: 
                    <input type="text" class="form-control" name="full_name" 
                           value="<?php echo htmlspecialchars($_SESSION['full_name']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email Address <span style="color: 
                    <input type="email" class="form-control" name="email" 
                           value="<?php echo htmlspecialchars($_SESSION['email']); ?>" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Contact Number <span style="color: 
                    <input type="text" class="form-control" name="contact_number" 
                           value="<?php echo $profile_data['contact_number'] ?? ''; ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">City <span style="color: 
                    <input type="text" class="form-control" name="city" 
                           value="<?php echo $profile_data['city'] ?? ''; ?>" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Province <span style="color: 
                    <input type="text" class="form-control" name="province" 
                           value="<?php echo $profile_data['province'] ?? ''; ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Postal Code</label>
                    <input type="text" class="form-control" name="postal_code" 
                           value="<?php echo $profile_data['postal_code'] ?? ''; ?>">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Address <span style="color: 
                <textarea class="form-control" name="address" rows="3" required><?php echo $profile_data['address'] ?? ''; ?></textarea>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary" style="background: 
                    <i class="fas fa-save"></i> Save Changes
                </button>
                <a href="/vehicare_db/client/dashboard.php" class="btn btn-secondary" style="padding: 10px 30px;">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </form>
    </div>

    <!-- Password Change Section -->
    <div style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin-top: 30px;">
        <h5 style="margin-bottom: 20px; color: 
        <a href="/vehicare_db/client/change_password.php" class="btn btn-warning">
            <i class="fas fa-key"></i> Change Password
        </a>
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

