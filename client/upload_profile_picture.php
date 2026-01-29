<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

// Check if client is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Content-Type: application/json");
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    
    // Handle file upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_picture'];
        
        // Validate file
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($file['type'], $allowed_types)) {
            header("Content-Type: application/json");
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.']);
            exit;
        }
        
        if ($file['size'] > $max_size) {
            header("Content-Type: application/json");
            echo json_encode(['success' => false, 'message' => 'File size exceeds 5MB limit.']);
            exit;
        }
        
        // Create uploads directory if it doesn't exist
        $upload_dir = __DIR__ . '/../uploads/profile_pictures/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Generate unique filename
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'profile_' . $user_id . '_' . time() . '.' . $file_extension;
        $file_path = $upload_dir . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            // Get old picture to delete
            $result = $conn->query("SELECT profile_picture FROM users WHERE user_id = $user_id");
            $row = $result->fetch_assoc();
            
            if ($row['profile_picture'] && file_exists($upload_dir . $row['profile_picture'])) {
                unlink($upload_dir . $row['profile_picture']);
            }
            
            // Update database
            $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE user_id = ?");
            $stmt->bind_param("si", $filename, $user_id);
            
            if ($stmt->execute()) {
                $_SESSION['profile_picture'] = $filename;
                header("Content-Type: application/json");
                echo json_encode([
                    'success' => true, 
                    'message' => 'Profile picture updated successfully',
                    'image_url' => '/vehicare_db/uploads/profile_pictures/' . $filename
                ]);
            } else {
                unlink($file_path);
                header("Content-Type: application/json");
                echo json_encode(['success' => false, 'message' => 'Failed to update database']);
            }
        } else {
            header("Content-Type: application/json");
            echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
        }
    } else {
        header("Content-Type: application/json");
        echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    }
} else {
    header("Content-Type: application/json");
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
