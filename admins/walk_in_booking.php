<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /vehicare_db/login.php");
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $name = $conn->real_escape_string($_POST['customer_name']);
            $phone = $conn->real_escape_string($_POST['phone']);
            $email = $conn->real_escape_string($_POST['email']);
            $vehicle = $conn->real_escape_string($_POST['vehicle_info']);
            $service_id = intval($_POST['service_id']);
            $date = $_POST['booking_date'];
            $time = $_POST['booking_time'];
            
            $conn->query("INSERT INTO walk_in_bookings (customer_name, phone, email, vehicle_info, service_id, booking_date, booking_time, status) 
                         VALUES ('$name', '$phone', '$email', '$vehicle', $service_id, '$date', '$time', 'pending')");
            
            $_SESSION['success'] = "Walk-in booking created!";
        } elseif ($_POST['action'] == 'update') {
            $id = intval($_POST['booking_id']);
            $status = $conn->real_escape_string($_POST['status']);
            $conn->query("UPDATE walk_in_bookings SET status = '$status' WHERE booking_id = $id");
            $_SESSION['success'] = "Walk-in booking updated!";
        } elseif ($_POST['action'] == 'delete') {
            $id = intval($_POST['booking_id']);
            $conn->query("DELETE FROM walk_in_bookings WHERE booking_id = $id");
            $_SESSION['success'] = "Walk-in booking deleted!";
        }
        header("Location: /vehicare_db/admins/walk_in_booking.php");
        exit;
    }
}

// Get all walk-in bookings with error handling
try {
    $bookings = $conn->query("
        SELECT wb.*, s.service_name 
        FROM walk_in_bookings wb
        LEFT JOIN services s ON wb.service_id = s.service_id
        ORDER BY wb.booking_date DESC, wb.booking_time DESC
    ");
} catch (Exception $e) {
    $bookings = false;
    $error_message = "Walk-in bookings table not found. Please run the database setup.";
}

try {
    $services = $conn->query("SELECT service_id, service_name, price FROM services");
} catch (Exception $e) {
    $services = false;
}

$page_title = "Walk-In Bookings";
$page_icon = "fas fa-door-open";
include __DIR__ . '/includes/admin_layout_header.php';
?>

<!-- Page Content -->
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Walk-In Booking Management</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bookingModal">
            <i class="fas fa-plus"></i> New Walk-In Booking
        </button>
    </div>
    
    <?php if (isset($error_message)): ?>
        <div class="alert alert-warning">
            <strong>Database Issue:</strong> <?php echo $error_message; ?>
            <br><a href="/vehicare_db/create_missing_tables.php" class="btn btn-sm btn-primary mt-2">Fix Database</a>
        </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-striped data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer Name</th>
                    <th>Phone</th>
                    <th>Vehicle</th>
                    <th>Service</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($bookings && $bookings->num_rows > 0): ?>
                    <?php while($booking = $bookings->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $booking['booking_id']; ?></td>
                        <td><?php echo htmlspecialchars($booking['client_name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($booking['phone'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($booking['vehicle_plate'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($booking['service_name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($booking['booking_date'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($booking['booking_time'] ?? 'N/A'); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $booking['status'] == 'completed' ? 'success' : ($booking['status'] == 'in_progress' ? 'warning' : 'secondary'); ?>">
                                <?php echo htmlspecialchars($booking['status'] ?? 'pending'); ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editBooking(<?php echo $booking['booking_id']; ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteBooking(<?php echo $booking['booking_id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center">No walk-in bookings found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function editBooking(id) {
    alert('Edit booking ' + id);
}

function deleteBooking(id) {
    if (confirm('Are you sure you want to delete this booking?')) {
        alert('Delete booking ' + id);
    }
}
</script>

<?php include __DIR__ . '/includes/admin_layout_footer.php'; ?>
