<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

// Check if client is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: /vehicare_db/login.php");
    exit;
}

$client_id = $_SESSION['user_id'];

// Handle form submission
if ($_POST) {
    $vehicle_id = $_POST['vehicle_id'];
    $service_id = $_POST['service_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $customer_notes = $_POST['customer_notes'] ?? '';
    
    try {
        // Insert appointment
        $stmt = $conn->prepare("
            INSERT INTO appointments (client_id, vehicle_id, service_id, appointment_date, appointment_time, customer_notes, status)
            VALUES (?, ?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->bind_param("iiisss", $client_id, $vehicle_id, $service_id, $appointment_date, $appointment_time, $customer_notes);
        
        if ($stmt->execute()) {
            $appointment_id = $conn->insert_id;
            
            // Log the activity
            log_event($client_id, "appointment_booked", "Booked new appointment #$appointment_id");
            
            // Create notification (if table exists)
            try {
                $notification_title = "Appointment Booked Successfully";
                $notification_message = "Your appointment has been booked for " . date('M j, Y', strtotime($appointment_date)) . " at " . date('g:i A', strtotime($appointment_time));
                
                $notif_stmt = $conn->prepare("
                    INSERT INTO notifications (user_id, type, title, message) 
                    VALUES (?, 'appointment', ?, ?)
                ");
                $notif_stmt->bind_param("iss", $client_id, $notification_title, $notification_message);
                $notif_stmt->execute();
            } catch (Exception $e) {
                // Notifications table might not exist
            }
            
            $_SESSION['success'] = "Appointment booked successfully! We'll contact you soon to confirm.";
            header("Location: /vehicare_db/client/appointments.php");
            exit;
        } else {
            throw new Exception("Failed to book appointment");
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error booking appointment: " . $e->getMessage();
    }
}

// Get client's vehicles with error handling
try {
    $vehicles = $conn->query("
        SELECT * FROM vehicles 
        WHERE client_id = $client_id AND status = 'active'
        ORDER BY created_at DESC
    ");
} catch (Exception $e) {
    // If status or created_at columns don't exist, try simpler queries
    try {
        $vehicles = $conn->query("
            SELECT * FROM vehicles 
            WHERE client_id = $client_id
            ORDER BY vehicle_id DESC
        ");
    } catch (Exception $e2) {
        $vehicles = $conn->query("
            SELECT * FROM vehicles 
            WHERE client_id = $client_id
        ");
    }
}

// Get available services with error handling
try {
    $services = $conn->query("
        SELECT * FROM services 
        WHERE is_active = 1 
        ORDER BY service_name
    ");
} catch (Exception $e) {
    // If is_active column doesn't exist, try without the WHERE clause or use status
    try {
        $services = $conn->query("
            SELECT * FROM services 
            WHERE status = 'active'
            ORDER BY service_name
        ");
    } catch (Exception $e2) {
        // If no status column either, just get all services
        $services = $conn->query("
            SELECT * FROM services 
            ORDER BY service_name
        ");
    }
}

include __DIR__ . '/../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - VehiCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #f8f9fa;
            padding: 20px 0;
        }

        .appointment-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .appointment-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-header h1 {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .page-header p {
            color: #7f8c8d;
            font-size: 1.1em;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 1em;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .btn-primary {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            border: none;
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
        }

        .btn-secondary {
            background: #95a5a6;
            border: none;
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 600;
        }

        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 40px;
        }

        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin: 0 10px;
            position: relative;
        }

        .step.active {
            background: #3498db;
            color: white;
        }

        .step.completed {
            background: #27ae60;
            color: white;
        }

        .step::after {
            content: '';
            position: absolute;
            right: -25px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 2px;
            background: #e9ecef;
        }

        .step:last-child::after {
            display: none;
        }

        .vehicle-selection {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }

        .vehicle-card {
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .vehicle-card:hover {
            border-color: #3498db;
            transform: translateY(-2px);
        }

        .vehicle-card.selected {
            border-color: #3498db;
            background: #ebf3fd;
        }

        .vehicle-card input[type="radio"] {
            position: absolute;
            opacity: 0;
        }

        .time-slots {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 10px;
            margin-top: 15px;
        }

        .time-slot {
            padding: 10px 15px;
            border: 2px solid #e9ecef;
            border-radius: 25px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .time-slot:hover {
            border-color: #3498db;
            background: #ebf3fd;
        }

        .time-slot.selected {
            border-color: #3498db;
            background: #3498db;
            color: white;
        }

        .no-vehicles-message {
            text-align: center;
            padding: 40px;
            background: #f8f9fa;
            border-radius: 15px;
            margin: 20px 0;
        }

        .no-vehicles-message i {
            font-size: 3em;
            color: #bdc3c7;
            margin-bottom: 20px;
        }

        .alert {
            border-radius: 10px;
            border: none;
            padding: 15px 20px;
        }

        @media (max-width: 768px) {
            .appointment-card {
                padding: 25px;
            }
            
            .time-slots {
                grid-template-columns: repeat(3, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="appointment-container">
        <div class="page-header">
            <h1><i class="fas fa-calendar-plus me-3"></i>Book New Service</h1>
            <p>Schedule your vehicle maintenance appointment</p>
        </div>

        <!-- Display Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Check if user has vehicles -->
        <?php if (!$vehicles || $vehicles->num_rows === 0): ?>
            <div class="appointment-card">
                <div class="no-vehicles-message">
                    <i class="fas fa-car"></i>
                    <h3>No Vehicles Found</h3>
                    <p>You need to add at least one vehicle before booking an appointment.</p>
                    <a href="/vehicare_db/client/add-vehicle.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add Your First Vehicle
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- Appointment Form -->
            <form method="POST" class="appointment-form">
                <div class="appointment-card">
                    <!-- Step Indicator -->
                    <div class="step-indicator">
                        <div class="step active">1</div>
                        <div class="step">2</div>
                        <div class="step">3</div>
                        <div class="step">4</div>
                    </div>

                    <!-- Vehicle Selection -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-car me-2"></i>Select Vehicle
                        </label>
                        <div class="vehicle-selection">
                            <?php while ($vehicle = $vehicles->fetch_assoc()): ?>
                                <div class="vehicle-card" onclick="selectVehicle(<?php echo $vehicle['vehicle_id']; ?>)">
                                    <input type="radio" name="vehicle_id" value="<?php echo $vehicle['vehicle_id']; ?>" required>
                                    <h5><?php echo htmlspecialchars($vehicle['plate_number']); ?></h5>
                                    <p class="mb-1">
                                        <strong><?php echo htmlspecialchars($vehicle['car_brand'] . ' ' . $vehicle['car_model']); ?></strong>
                                    </p>
                                    <p class="mb-0 text-muted">
                                        <?php echo htmlspecialchars($vehicle['year_model'] . ' • ' . $vehicle['color']); ?>
                                    </p>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>

                    <!-- Service Selection -->
                    <div class="form-group">
                        <label for="service_id" class="form-label">
                            <i class="fas fa-wrench me-2"></i>Select Service
                        </label>
                        <select name="service_id" id="service_id" class="form-select" required>
                            <option value="">Choose a service...</option>
                            <?php while ($service = $services->fetch_assoc()): ?>
                                <option value="<?php echo $service['service_id']; ?>" 
                                        data-price="<?php echo $service['price'] ?? $service['base_price'] ?? 0; ?>"
                                        data-duration="<?php echo $service['estimated_duration'] ?? 60; ?>">
                                    <?php echo htmlspecialchars($service['service_name']); ?>
                                    - ₱<?php echo number_format($service['price'] ?? $service['base_price'] ?? 0, 2); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- Date Selection -->
                    <div class="form-group">
                        <label for="appointment_date" class="form-label">
                            <i class="fas fa-calendar me-2"></i>Preferred Date
                        </label>
                        <input type="date" 
                               name="appointment_date" 
                               id="appointment_date" 
                               class="form-control" 
                               min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                               max="<?php echo date('Y-m-d', strtotime('+3 months')); ?>"
                               required>
                    </div>

                    <!-- Time Selection -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-clock me-2"></i>Preferred Time
                        </label>
                        <input type="hidden" name="appointment_time" id="selected_time" required>
                        <div class="time-slots">
                            <div class="time-slot" onclick="selectTime('08:00')">8:00 AM</div>
                            <div class="time-slot" onclick="selectTime('09:00')">9:00 AM</div>
                            <div class="time-slot" onclick="selectTime('10:00')">10:00 AM</div>
                            <div class="time-slot" onclick="selectTime('11:00')">11:00 AM</div>
                            <div class="time-slot" onclick="selectTime('13:00')">1:00 PM</div>
                            <div class="time-slot" onclick="selectTime('14:00')">2:00 PM</div>
                            <div class="time-slot" onclick="selectTime('15:00')">3:00 PM</div>
                            <div class="time-slot" onclick="selectTime('16:00')">4:00 PM</div>
                        </div>
                    </div>

                    <!-- Additional Notes -->
                    <div class="form-group">
                        <label for="customer_notes" class="form-label">
                            <i class="fas fa-sticky-note me-2"></i>Additional Notes (Optional)
                        </label>
                        <textarea name="customer_notes" 
                                  id="customer_notes" 
                                  class="form-control" 
                                  rows="4" 
                                  placeholder="Any specific concerns or requests..."></textarea>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="text-center">
                        <a href="/vehicare_db/client/dashboard.php" class="btn btn-secondary me-3">
                            <i class="fas fa-arrow-left me-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check me-2"></i>Book Appointment
                        </button>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script>
        function selectVehicle(vehicleId) {
            // Remove previous selection
            document.querySelectorAll('.vehicle-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Select current vehicle
            event.currentTarget.classList.add('selected');
            event.currentTarget.querySelector('input[type="radio"]').checked = true;
        }

        function selectTime(time) {
            // Remove previous selection
            document.querySelectorAll('.time-slot').forEach(slot => {
                slot.classList.remove('selected');
            });
            
            // Select current time
            event.currentTarget.classList.add('selected');
            document.getElementById('selected_time').value = time;
        }

        // Set minimum date to tomorrow
        document.addEventListener('DOMContentLoaded', function() {
            const dateInput = document.getElementById('appointment_date');
            const today = new Date();
            const tomorrow = new Date(today);
            tomorrow.setDate(tomorrow.getDate() + 1);
            
            dateInput.min = tomorrow.toISOString().split('T')[0];
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>