<?php
session_start();
require_once __DIR__ . '/includes/config.php';

// Check if technician is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$appointment_id = isset($_POST['appointment_id']) ? (int)$_POST['appointment_id'] : 0;
$status = isset($_POST['status']) ? $_POST['status'] : '';

if ($appointment_id <= 0 || empty($status)) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

// Validate status
$valid_statuses = ['pending', 'in-progress', 'completed', 'cancelled'];
if (!in_array($status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

// Check if appointment belongs to this technician
$user_id = $_SESSION['user_id'];
$checkQuery = "SELECT a.appointment_id FROM appointments a 
               JOIN assignments ass ON a.appointment_id = ass.appointment_id 
               WHERE a.appointment_id = $appointment_id AND ass.staff_id = $user_id";

$result = $conn->query($checkQuery);
if (!$result || $result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Appointment not found or not assigned to you']);
    exit;
}

// Update appointment status
$update_query = "UPDATE appointments SET status = '$status' WHERE appointment_id = $appointment_id";

if ($conn->query($update_query)) {
    echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update status']);
}
?>
