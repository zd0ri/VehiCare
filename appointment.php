<?php 
session_start();
require_once __DIR__ . '/includes/config.php';
include __DIR__ . '/includes/header.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $vehicle_model = $conn->real_escape_string($_POST['vehicle_model']);
    $service_id = intval($_POST['service_id']);
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $notes = $conn->real_escape_string($_POST['notes'] ?? '');

    $sql = "INSERT INTO appointments (full_name, email, phone, vehicle_model, service_id, appointment_date, appointment_time, notes, status, created_at) 
            VALUES ('$full_name', '$email', '$phone', '$vehicle_model', $service_id, '$appointment_date', '$appointment_time', '$notes', 'pending', NOW())";

    if ($conn->query($sql) === TRUE) {
        $message = 'Appointment booked successfully! We will contact you soon to confirm.';
        $message_type = 'success';
    } else {
        $message = 'Error booking appointment: ' . $conn->error;
        $message_type = 'error';
    }
}
?>

<style>
    * {
        font-family: 'Poppins', sans-serif;
    }

    .appointment-page {
        max-width: 900px;
        margin: 0 auto;
        padding: 60px 20px;
    }

    .page-header {
        text-align: center;
        margin-bottom: 50px;
    }

    .page-header h1 {
        font-size: 2.8em;
        color: #1a3a52;
        margin: 0 0 20px 0;
        font-weight: 700;
    }

    .page-header p {
        font-size: 1.1em;
        color: #666;
    }

    .form-container {
        background: white;
        padding: 50px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .alert {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 30px;
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

    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group label {
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
        font-size: 0.95em;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 0.95em;
        font-family: 'Poppins', sans-serif;
        transition: all 0.3s ease;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #0ea5a4;
        box-shadow: 0 0 0 3px rgba(14, 165, 164, 0.1);
    }

    .form-group textarea {
        resize: vertical;
        min-height: 120px;
    }

    .required::after {
        content: ' *';
        color: #e74c3c;
    }

    .button-group {
        display: flex;
        gap: 15px;
        margin-top: 30px;
    }

    .btn {
        padding: 12px 30px;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.95em;
        font-family: 'Poppins', sans-serif;
    }

    .btn-primary {
        background: linear-gradient(135deg, #1a3a52 0%, #2d5a7b 100%);
        color: white;
        flex: 1;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(26, 58, 82, 0.3);
    }

    .btn-secondary {
        background: #f0f0f0;
        color: #333;
        border: 1px solid #ddd;
    }

    .btn-secondary:hover {
        background: #e0e0e0;
    }

    @media (max-width: 768px) {
        .appointment-page {
            padding: 40px 20px;
        }

        .form-container {
            padding: 30px;
        }

        .page-header h1 {
            font-size: 2em;
        }

        .button-group {
            flex-direction: column;
        }
    }
</style>

<div class="appointment-page">
    <!-- Page Header -->
    <div class="page-header">
        <h1>Book Your Service</h1>
        <p>Schedule an appointment for your vehicle maintenance or repair</p>
    </div>

    <!-- Appointment Form -->
    <div class="form-container">
        <div id="message" class="alert"></div>

        <form method="POST">
            <!-- Name and Email Row -->
            <div class="form-row">
                <div class="form-group">
                    <label for="full_name" class="required">Full Name</label>
                    <input type="text" id="full_name" name="full_name" required>
                </div>
                <div class="form-group">
                    <label for="email" class="required">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>
            </div>

            <!-- Phone and Vehicle Row -->
            <div class="form-row">
                <div class="form-group">
                    <label for="phone" class="required">Phone Number</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>
                <div class="form-group">
                    <label for="vehicle_model" class="required">Vehicle Model</label>
                    <input type="text" id="vehicle_model" name="vehicle_model" placeholder="e.g., Toyota Camry 2020" required>
                </div>
            </div>

            <!-- Service and Date Row -->
            <div class="form-row">
                <div class="form-group">
                    <label for="service_id" class="required">Select Service</label>
                    <select id="service_id" name="service_id" required>
                        <option value="">Choose a service</option>
                        <?php
                        $serviceResult = $conn->query("SELECT service_id, service_name, price FROM services");
                        if ($serviceResult) {
                            while ($service = $serviceResult->fetch_assoc()) {
                                echo "<option value='{$service['service_id']}'>{$service['service_name']} - \${$service['price']}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="appointment_date" class="required">Preferred Date</label>
                    <input type="date" id="appointment_date" name="appointment_date" required>
                </div>
            </div>

            <!-- Time -->
            <div class="form-row">
                <div class="form-group">
                    <label for="appointment_time" class="required">Preferred Time</label>
                    <input type="time" id="appointment_time" name="appointment_time" required>
                </div>
            </div>

            <!-- Notes -->
            <div class="form-group">
                <label for="notes">Additional Notes</label>
                <textarea id="notes" name="notes" placeholder="Tell us about your vehicle's issues or special requests..."></textarea>
            </div>

            <!-- Buttons -->
            <div class="button-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check"></i> Book Appointment
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
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
