<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $email = $conn->real_escape_string($_POST['email']);
    $vehicle_model = $conn->real_escape_string($_POST['vehicle_model']);
    $service_id = intval($_POST['service_id']);
    $appointment_date = $conn->real_escape_string($_POST['appointment_date']);
    $appointment_time = $conn->real_escape_string($_POST['appointment_time']);
    $notes = isset($_POST['notes']) ? $conn->real_escape_string($_POST['notes']) : '';
    
    
    $clientCheck = $conn->query("SELECT client_id FROM clients WHERE email = '$email' LIMIT 1");
    
    if ($clientCheck && $clientCheck->num_rows > 0) {
        $client = $clientCheck->fetch_assoc();
        $client_id = $client['client_id'];
        
        
        $conn->query("UPDATE clients SET full_name='$full_name', phone='$phone' WHERE client_id = $client_id");
    } else {
        
        $insertClient = $conn->query("INSERT INTO clients (full_name, phone, email) VALUES ('$full_name', '$phone', '$email')");
        if (!$insertClient) {
            $_SESSION['error'] = "Error creating client account";
            header("Location: /vehicare_db/index.php");
            exit;
        }
        $client_id = $conn->insert_id;
    }
    
    
    $insertAppointment = $conn->query("INSERT INTO appointments (client_id, service_id, appointment_date, appointment_time, status) 
                                      VALUES ($client_id, $service_id, '$appointment_date', '$appointment_time', 'Pending')");
    
    if ($insertAppointment) {
        $_SESSION['success'] = "Appointment booked successfully! We'll contact you soon to confirm.";
    } else {
        $_SESSION['error'] = "Error booking appointment: " . $conn->error;
    }
    
    header("Location: /vehicare_db/index.php");
    exit;
}
?>

