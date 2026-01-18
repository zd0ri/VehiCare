<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /vehicare_db/index.php");
    exit;
}

$type = isset($_GET['type']) ? $_GET['type'] : '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$type || !$id) {
    $_SESSION['error'] = "Invalid delete request";
    header("Location: /vehicare_db/admins/dashboard.php");
    exit;
}

$success = false;

switch ($type) {
    case 'client':
        $success = $conn->query("DELETE FROM clients WHERE client_id = $id");
        $redirect = "clients.php";
        break;
    case 'vehicle':
        $success = $conn->query("DELETE FROM vehicles WHERE vehicle_id = $id");
        $redirect = "vehicles.php";
        break;
    case 'appointment':
        $success = $conn->query("DELETE FROM appointments WHERE appointment_id = $id");
        $redirect = "appointments.php";
        break;
    case 'service':
        $success = $conn->query("DELETE FROM services WHERE service_id = $id");
        $redirect = "services.php";
        break;
    case 'staff':
        $success = $conn->query("DELETE FROM staff WHERE staff_id = $id");
        $redirect = "staff.php";
        break;
    default:
        $_SESSION['error'] = "Invalid type";
        header("Location: /vehicare_db/admins/dashboard.php");
        exit;
}

if ($success) {
    $_SESSION['success'] = ucfirst($type) . " deleted successfully!";
} else {
    $_SESSION['error'] = "Error deleting " . $type . ": " . $conn->error;
}

header("Location: /vehicare_db/admins/$redirect");
exit;
?>
