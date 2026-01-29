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


$profile_check = "SELECT is_profile_complete FROM customer_profiles WHERE user_id = $user_id";
$profile_result = $conn->query($profile_check);
if (!$profile_result || $profile_result->num_rows === 0) {
    header("Location: /vehicare_db/client/profile.php?incomplete=1");
    exit;
}
$profile_data = $profile_result->fetch_assoc();
if (!$profile_data['is_profile_complete']) {
    header("Location: /vehicare_db/client/profile.php?incomplete=1");
    exit;
}


$vehicles_query = "SELECT * FROM vehicles WHERE user_id = $user_id AND status = 'active' ORDER BY created_at DESC";
$vehicles_result = $conn->query($vehicles_query);
$vehicles = [];
if ($vehicles_result && $vehicles_result->num_rows > 0) {
    while ($row = $vehicles_result->fetch_assoc()) {
        $vehicles[] = $row;
    }
}


$services_query = "SELECT * FROM services WHERE status = 'active' ORDER BY service_name ASC";
$services_result = $conn->query($services_query);
$services = [];
if ($services_result && $services_result->num_rows > 0) {
    while ($row = $services_result->fetch_assoc()) {
        $services[] = $row;
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_type = isset($_POST['appointment_type']) ? trim($_POST['appointment_type']) : '';
    $vehicle_id = isset($_POST['vehicle_id']) ? intval($_POST['vehicle_id']) : 0;
    $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : null;
    $appointment_date = isset($_POST['appointment_date']) ? trim($_POST['appointment_date']) : '';
    $appointment_time = isset($_POST['appointment_time']) ? trim($_POST['appointment_time']) : '';
    $notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';

    $errors = [];

    if (empty($appointment_type)) $errors[] = "Appointment type is required";
    if ($vehicle_id === 0) $errors[] = "Please select a vehicle";
    if (empty($appointment_date)) $errors[] = "Date is required";
    if (empty($appointment_time)) $errors[] = "Time is required";
    if ($appointment_type === 'appointment' && empty($appointment_time)) $errors[] = "Time is required for scheduled appointments";

    
    if ($appointment_date && strtotime($appointment_date) < strtotime('today')) {
        $errors[] = "Appointment date cannot be in the past";
    }

    
    if ($vehicle_id > 0) {
        $vehicle_check = "SELECT user_id FROM vehicles WHERE vehicle_id = $vehicle_id";
        $vehicle_check_result = $conn->query($vehicle_check);
        if (!$vehicle_check_result || $vehicle_check_result->num_rows === 0 || $vehicle_check_result->fetch_assoc()['user_id'] != $user_id) {
            $errors[] = "Invalid vehicle selection";
        }
    }

    if (empty($errors)) {
        $service_id_val = $service_id > 0 ? $service_id : 'NULL';
        
        $insert_query = "INSERT INTO appointments 
                        (user_id, vehicle_id, service_id, appointment_type, appointment_date, appointment_time, notes, status)
                        VALUES ($user_id, $vehicle_id, $service_id_val, '$appointment_type', '$appointment_date', '$appointment_time', '$notes', 'pending')";

        if ($conn->query($insert_query)) {
            $message = "Appointment booked successfully! Your appointment is pending confirmation.";
            
            $_POST = [];
        } else {
            $error = "Failed to book appointment. Please try again.";
        }
    } else {
        $error = implode('<br>', $errors);
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div style="max-width: 800px; margin: 40px auto; padding: 0 20px;">
    <!-- Header -->
    <div style="background: linear-gradient(135deg, 
        <h1 style="margin: 0 0 10px 0;"><i class="fas fa-calendar-plus"></i> Book Appointment</h1>
        <p style="margin: 0; opacity: 0.9;">Schedule your vehicle service or walk in</p>
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
                <label class="form-label">Appointment Type <span style="color: 
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <label style="display: flex; align-items: center; padding: 15px; border: 2px solid 
                        <input type="radio" name="appointment_type" value="appointment" <?php echo (!isset($_POST['appointment_type']) || $_POST['appointment_type'] === 'appointment') ? 'checked' : ''; ?> style="margin-right: 10px;" required>
                        <span>
                            <strong>Scheduled Appointment</strong><br>
                            <small style="color: 
                        </span>
                    </label>
                    <label style="display: flex; align-items: center; padding: 15px; border: 2px solid 
                        <input type="radio" name="appointment_type" value="walk-in" <?php echo isset($_POST['appointment_type']) && $_POST['appointment_type'] === 'walk-in' ? 'checked' : ''; ?> style="margin-right: 10px;" required>
                        <span>
                            <strong>Walk-in Service</strong><br>
                            <small style="color: 
                        </span>
                    </label>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Select Vehicle <span style="color: 
                <select class="form-control" name="vehicle_id" required>
                    <option value="">-- Choose a vehicle --</option>
                    <?php foreach ($vehicles as $vehicle): ?>
                    <option value="<?php echo $vehicle['vehicle_id']; ?>" <?php echo (isset($_POST['vehicle_id']) && $_POST['vehicle_id'] == $vehicle['vehicle_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($vehicle['vehicle_type'] . ' - ' . $vehicle['make'] . ' ' . $vehicle['model'] . ' (' . $vehicle['plate_number'] . ')'); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <?php if (count($vehicles) === 0): ?>
                <small style="color: 
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label">Service Type</label>
                <select class="form-control" name="service_id">
                    <option value="">-- Choose a service (optional) --</option>
                    <?php foreach ($services as $service): ?>
                    <option value="<?php echo $service['service_id']; ?>" <?php echo (isset($_POST['service_id']) && $_POST['service_id'] == $service['service_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($service['service_name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <small style="color: 
            </div>

            <div id="appointment-fields">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Date <span style="color: 
                        <input type="date" class="form-control" name="appointment_date" 
                               value="<?php echo isset($_POST['appointment_date']) ? $_POST['appointment_date'] : ''; ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Time <span style="color: 
                        <input type="time" class="form-control" name="appointment_time" 
                               value="<?php echo isset($_POST['appointment_time']) ? $_POST['appointment_time'] : ''; ?>" required>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Notes / Special Requests</label>
                <textarea class="form-control" name="notes" rows="4" placeholder="Any additional information about your service needs..."><?php echo isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : ''; ?></textarea>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary" style="background: 
                    <i class="fas fa-check"></i> Book Appointment
                </button>
                <a href="/vehicare_db/client/appointments.php" class="btn btn-secondary" style="padding: 10px 30px;">
                    <i class="fas fa-arrow-left"></i> Cancel
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
    label input[type="radio"] {
        cursor: pointer;
    }
    label:has(input[type="radio"]:checked) {
        border-color: 
        background: 
    }
</style>

<script>
    document.querySelectorAll('input[name="appointment_type"]').forEach(input => {
        input.addEventListener('change', function() {
            const appointmentFields = document.getElementById('appointment-fields');
            if (this.value === 'walk-in') {
                appointmentFields.style.display = 'none';
                document.querySelector('input[name="appointment_date"]').removeAttribute('required');
                document.querySelector('input[name="appointment_time"]').removeAttribute('required');
            } else {
                appointmentFields.style.display = 'block';
                document.querySelector('input[name="appointment_date"]').setAttribute('required', 'required');
                document.querySelector('input[name="appointment_time"]').setAttribute('required', 'required');
            }
        });
    });
    
    document.querySelector('input[name="appointment_type"]:checked')?.dispatchEvent(new Event('change'));
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>

