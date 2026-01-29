<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

// Check if client is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: /vehicare_db/login.php");
    exit;
}

$message = '';
$message_type = '';
$user_id = $_SESSION['user_id'];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'update_profile') {
            // Update profile information
            $full_name = $conn->real_escape_string($_POST['full_name']);
            $email = $conn->real_escape_string($_POST['email']);
            $phone = $conn->real_escape_string($_POST['phone']);
            $address = $conn->real_escape_string($_POST['address']);
            $city = $conn->real_escape_string($_POST['city']);
            $state = $conn->real_escape_string($_POST['state']);
            $zip_code = $conn->real_escape_string($_POST['zip_code']);
            
            $sql = "UPDATE users SET 
                    full_name = '$full_name', 
                    email = '$email', 
                    phone = '$phone', 
                    address = '$address', 
                    city = '$city', 
                    state = '$state', 
                    zip_code = '$zip_code' 
                    WHERE user_id = $user_id";
            
            if ($conn->query($sql) === TRUE) {
                $_SESSION['full_name'] = $full_name;
                $message = 'Profile information updated successfully!';
                $message_type = 'success';
            } else {
                $message = 'Error updating profile: ' . $conn->error;
                $message_type = 'error';
            }
        } elseif ($_POST['action'] === 'change_password') {
            // Change password
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
            
            // Get current password from database
            $result = $conn->query("SELECT password FROM users WHERE user_id = $user_id");
            $user = $result->fetch_assoc();
            
            // Verify current password
            if (!password_verify($current_password, $user['password'])) {
                $message = 'Current password is incorrect!';
                $message_type = 'error';
            } elseif ($new_password !== $confirm_password) {
                $message = 'New passwords do not match!';
                $message_type = 'error';
            } elseif (strlen($new_password) < 6) {
                $message = 'Password must be at least 6 characters long!';
                $message_type = 'error';
            } else {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET password = '$hashed_password' WHERE user_id = $user_id";
                
                if ($conn->query($sql) === TRUE) {
                    $message = 'Password changed successfully!';
                    $message_type = 'success';
                } else {
                    $message = 'Error changing password: ' . $conn->error;
                    $message_type = 'error';
                }
            }
        }
    }
}

// Fetch user data
$result = $conn->query("SELECT * FROM users WHERE user_id = $user_id");
$user = $result->fetch_assoc();

include __DIR__ . '/../includes/header.php';
?>

<style>
    .profile-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
        background: #f5f7fa;
        min-height: 100vh;
    }

    .profile-header {
        background: linear-gradient(135deg, #1a3a52 0%, #2d5a7b 100%);
        color: white;
        padding: 40px;
        border-radius: 12px;
        margin-bottom: 30px;
        display: flex;
        align-items: center;
        gap: 30px;
    }

    .profile-picture-wrapper {
        position: relative;
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
            <p><i class="fas fa-calendar"></i> Member since <?php echo date('F j, Y', strtotime($user['created_date'])); ?></p>
        </div>
    </div>

    <!-- Messages -->
    <div id="message" class="alert"></div>

    <!-- Profile Update Section -->
    <div class="section">
        <h2>Profile Picture</h2>
        <div class="upload-section" onclick="document.getElementById('fileInput').click();">
            <input type="file" id="fileInput" accept="image/*">
            <i class="fas fa-cloud-upload-alt"></i>
            <p><strong>Click to upload</strong> or drag and drop</p>
            <p style="font-size: 0.85em; color: #999;">PNG, JPG, GIF, WebP (Max 5MB)</p>
        </div>
        <div style="margin-top: 20px; text-align: center;">
            <p style="color: #666; font-size: 0.9em;">Uploading...</p>
            <div id="uploadProgress" style="display: none; margin-top: 10px;">
                <div style="width: 100%; background: #e0e0e0; border-radius: 5px; height: 8px;">
                    <div id="progressBar" style="width: 0%; height: 100%; background: #27ae60; border-radius: 5px; transition: width 0.3s;"></div>
                </div>
            </div>
        </div>
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
                    <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($user['city'] ?: ''); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="state">State/Province</label>
                    <input type="text" id="state" name="state" value="<?php echo htmlspecialchars($user['state'] ?: ''); ?>">
                </div>
                <div class="form-group">
                    <label for="zip_code">Zip Code</label>
                    <input type="text" id="zip_code" name="zip_code" value="<?php echo htmlspecialchars($user['zip_code'] ?: ''); ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address"><?php echo htmlspecialchars($user['address'] ?: ''); ?></textarea>
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

    <!-- Change Password Section -->
    <div class="section">
        <h2>Change Password</h2>
        <form method="POST">
            <input type="hidden" name="action" value="change_password">
            
            <div class="form-group">
                <label for="current_password">Current Password *</label>
                <input type="password" id="current_password" name="current_password" required>
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

            <div class="button-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-lock"></i> Change Password
                </button>
                <button type="reset" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Reset
                </button>
            </div>
        </form>
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

        const xhr = new XMLHttpRequest();

        xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
                const percentComplete = (e.loaded / e.total) * 100;
                console.log(percentComplete + '% uploaded');
            }
        });

        xhr.addEventListener('load', () => {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    
                    fileInput.disabled = false;
                    uploadSection.style.opacity = '1';

                    if (response.success) {
                        messageEl.textContent = response.message;
                        messageEl.className = 'alert show alert-success';
                        
                        // Reload profile picture
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        messageEl.textContent = response.message;
                        messageEl.className = 'alert show alert-error';
                    }

                    // Auto-hide after 5 seconds
                    setTimeout(() => {
                        messageEl.className = 'alert';
                    }, 5000);
                } catch (e) {
                    fileInput.disabled = false;
                    uploadSection.style.opacity = '1';
                    messageEl.textContent = 'Error uploading file';
                    messageEl.className = 'alert show alert-error';
                }
            }
        });

        xhr.addEventListener('error', () => {
            fileInput.disabled = false;
            uploadSection.style.opacity = '1';
            messageEl.textContent = 'Upload failed';
            messageEl.className = 'alert show alert-error';
        });

        xhr.open('POST', '/vehicare_db/client/upload_profile_picture.php');
        xhr.send(formData);
    }
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
