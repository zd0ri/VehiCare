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
    $plate_number = strtoupper(trim($_POST['plate_number']));
    $car_brand = trim($_POST['car_brand']);
    $car_model = trim($_POST['car_model']);
    $year_model = $_POST['year_model'];
    $color = trim($_POST['color']);
    $engine_type = trim($_POST['engine_type']);
    $transmission_type = $_POST['transmission_type'];
    $mileage = $_POST['mileage'] ? intval($_POST['mileage']) : null;
    $vin_number = trim($_POST['vin_number']);
    $insurance_info = trim($_POST['insurance_info']);
    $notes = trim($_POST['notes']);
    
    try {
        // Check if plate number already exists
        $check_plate = $conn->prepare("SELECT vehicle_id FROM vehicles WHERE plate_number = ? AND status != 'archived'");
        $check_plate->bind_param("s", $plate_number);
        $check_plate->execute();
        
        if ($check_plate->get_result()->num_rows > 0) {
            throw new Exception("A vehicle with this plate number is already registered.");
        }
        
        // Insert new vehicle
        $stmt = $conn->prepare("
            INSERT INTO vehicles (
                client_id, plate_number, car_brand, car_model, year_model, color, 
                engine_type, transmission_type, mileage, vin_number, insurance_info, notes
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->bind_param(
            "isssisssssss", 
            $client_id, $plate_number, $car_brand, $car_model, $year_model, $color,
            $engine_type, $transmission_type, $mileage, $vin_number, $insurance_info, $notes
        );
        
        if ($stmt->execute()) {
            $vehicle_id = $conn->insert_id;
            
            // Log the activity
            log_event($client_id, "vehicle_added", "Added new vehicle: $plate_number");
            
            // Create notification
            try {
                $notification_title = "Vehicle Added Successfully";
                $notification_message = "Your vehicle $plate_number has been added to your account.";
                
                $notif_stmt = $conn->prepare("
                    INSERT INTO notifications (user_id, type, title, message) 
                    VALUES (?, 'system', ?, ?)
                ");
                $notif_stmt->bind_param("iss", $client_id, $notification_title, $notification_message);
                $notif_stmt->execute();
            } catch (Exception $e) {
                // Notifications table might not exist
            }
            
            $_SESSION['success'] = "Vehicle added successfully! You can now book services for $plate_number.";
            header("Location: /vehicare_db/client/vehicles.php");
            exit;
        } else {
            throw new Exception("Failed to add vehicle");
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error adding vehicle: " . $e->getMessage();
    }
}

include __DIR__ . '/../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Vehicle - VehiCare</title>
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

        .vehicle-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .vehicle-card {
            background: white;
            border-radius: 25px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-header .icon-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2em;
            color: white;
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

        .section-title {
            font-size: 1.3em;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ecf0f1;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-section {
            margin-bottom: 35px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-label .required {
            color: #e74c3c;
        }

        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 12px 18px;
            font-size: 1em;
            transition: all 0.3s ease;
            background: #fff;
        }

        .form-control:focus, .form-select:focus {
            border-color: #27ae60;
            box-shadow: 0 0 0 0.2rem rgba(39, 174, 96, 0.25);
        }

        .input-group {
            position: relative;
        }

        .input-group-text {
            border: 2px solid #e9ecef;
            border-right: none;
            background: #f8f9fa;
            color: #6c757d;
        }

        .input-group .form-control {
            border-left: none;
        }

        .btn {
            border-radius: 50px;
            font-weight: 600;
            padding: 12px 30px;
            transition: all 0.3s ease;
        }

        .btn-success {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
            border: none;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(39, 174, 96, 0.3);
        }

        .btn-secondary {
            background: #95a5a6;
            border: none;
        }

        .form-text {
            color: #7f8c8d;
            font-size: 0.9em;
            margin-top: 5px;
        }

        .row > .col-md-6 {
            margin-bottom: 15px;
        }

        .alert {
            border-radius: 12px;
            border: none;
            padding: 15px 20px;
        }

        .progress-steps {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }

        .step {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0 20px;
            position: relative;
        }

        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .step.active .step-circle {
            background: #27ae60;
            color: white;
        }

        .step.completed .step-circle {
            background: #27ae60;
            color: white;
        }

        .step::after {
            content: '';
            position: absolute;
            right: -35px;
            top: 50%;
            transform: translateY(-50%);
            width: 30px;
            height: 2px;
            background: #e9ecef;
        }

        .step:last-child::after {
            display: none;
        }

        @media (max-width: 768px) {
            .vehicle-card {
                padding: 25px;
                margin: 0 15px 30px;
            }

            .progress-steps {
                display: none;
            }

            .btn {
                width: 100%;
                margin-bottom: 10px;
            }

            .row .col-md-6:nth-child(odd) {
                padding-right: 15px;
            }

            .row .col-md-6:nth-child(even) {
                padding-left: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="vehicle-container">
        <div class="vehicle-card">
            <!-- Page Header -->
            <div class="page-header">
                <div class="icon-circle">
                    <i class="fas fa-car-side"></i>
                </div>
                <h1>Add New Vehicle</h1>
                <p>Register your vehicle to start booking maintenance services</p>
            </div>

            <!-- Progress Steps -->
            <div class="progress-steps">
                <div class="step active">
                    <div class="step-circle">1</div>
                    <span>Basic Info</span>
                </div>
                <div class="step">
                    <div class="step-circle">2</div>
                    <span>Specifications</span>
                </div>
                <div class="step">
                    <div class="step-circle">3</div>
                    <span>Additional Info</span>
                </div>
            </div>

            <!-- Display Messages -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <!-- Vehicle Form -->
            <form method="POST" id="vehicleForm">
                <!-- Basic Information Section -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-info-circle"></i>
                        Basic Information
                    </h3>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="plate_number" class="form-label">
                                    <i class="fas fa-hashtag"></i>
                                    Plate Number <span class="required">*</span>
                                </label>
                                <input type="text" 
                                       name="plate_number" 
                                       id="plate_number" 
                                       class="form-control" 
                                       placeholder="ABC-1234" 
                                       required 
                                       style="text-transform: uppercase;"
                                       maxlength="15">
                                <div class="form-text">Enter the license plate number</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="car_brand" class="form-label">
                                    <i class="fas fa-industry"></i>
                                    Vehicle Brand <span class="required">*</span>
                                </label>
                                <input type="text" 
                                       name="car_brand" 
                                       id="car_brand" 
                                       class="form-control" 
                                       placeholder="Toyota, Honda, Ford..." 
                                       required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="car_model" class="form-label">
                                    <i class="fas fa-car"></i>
                                    Model <span class="required">*</span>
                                </label>
                                <input type="text" 
                                       name="car_model" 
                                       id="car_model" 
                                       class="form-control" 
                                       placeholder="Camry, Civic, Focus..." 
                                       required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="year_model" class="form-label">
                                    <i class="fas fa-calendar"></i>
                                    Year <span class="required">*</span>
                                </label>
                                <select name="year_model" id="year_model" class="form-select" required>
                                    <option value="">Select Year</option>
                                    <?php for ($year = date('Y') + 1; $year >= 1990; $year--): ?>
                                        <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="color" class="form-label">
                            <i class="fas fa-palette"></i>
                            Color <span class="required">*</span>
                        </label>
                        <input type="text" 
                               name="color" 
                               id="color" 
                               class="form-control" 
                               placeholder="White, Black, Red..." 
                               required>
                    </div>
                </div>

                <!-- Specifications Section -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-cogs"></i>
                        Vehicle Specifications
                    </h3>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="engine_type" class="form-label">
                                    <i class="fas fa-engine"></i>
                                    Engine Type
                                </label>
                                <input type="text" 
                                       name="engine_type" 
                                       id="engine_type" 
                                       class="form-control" 
                                       placeholder="1.6L, 2.0L Turbo, V6...">
                                <div class="form-text">Optional: Engine size or type</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="transmission_type" class="form-label">
                                    <i class="fas fa-cogs"></i>
                                    Transmission
                                </label>
                                <select name="transmission_type" id="transmission_type" class="form-select">
                                    <option value="">Select Transmission</option>
                                    <option value="manual">Manual</option>
                                    <option value="automatic">Automatic</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="mileage" class="form-label">
                            <i class="fas fa-tachometer-alt"></i>
                            Current Mileage (km)
                        </label>
                        <div class="input-group">
                            <input type="number" 
                                   name="mileage" 
                                   id="mileage" 
                                   class="form-control" 
                                   placeholder="50000" 
                                   min="0" 
                                   max="999999">
                            <span class="input-group-text">km</span>
                        </div>
                        <div class="form-text">Current odometer reading</div>
                    </div>
                </div>

                <!-- Additional Information Section -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-file-alt"></i>
                        Additional Information
                    </h3>

                    <div class="form-group">
                        <label for="vin_number" class="form-label">
                            <i class="fas fa-barcode"></i>
                            VIN Number
                        </label>
                        <input type="text" 
                               name="vin_number" 
                               id="vin_number" 
                               class="form-control" 
                               placeholder="1HGCM82633A004352"
                               maxlength="17"
                               style="text-transform: uppercase;">
                        <div class="form-text">17-character Vehicle Identification Number (optional)</div>
                    </div>

                    <div class="form-group">
                        <label for="insurance_info" class="form-label">
                            <i class="fas fa-shield-alt"></i>
                            Insurance Information
                        </label>
                        <textarea name="insurance_info" 
                                  id="insurance_info" 
                                  class="form-control" 
                                  rows="3" 
                                  placeholder="Insurance company, policy number, etc."></textarea>
                        <div class="form-text">Optional: Insurance details for reference</div>
                    </div>

                    <div class="form-group">
                        <label for="notes" class="form-label">
                            <i class="fas fa-sticky-note"></i>
                            Notes
                        </label>
                        <textarea name="notes" 
                                  id="notes" 
                                  class="form-control" 
                                  rows="3" 
                                  placeholder="Any additional notes about your vehicle..."></textarea>
                        <div class="form-text">Optional: Special notes or modifications</div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="text-center">
                    <a href="/vehicare_db/client/vehicles.php" class="btn btn-secondary me-3">
                        <i class="fas fa-arrow-left me-2"></i>Cancel
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-plus me-2"></i>Add Vehicle
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Auto-uppercase plate number and VIN
        document.getElementById('plate_number').addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });

        document.getElementById('vin_number').addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });

        // Form validation feedback
        document.getElementById('vehicleForm').addEventListener('submit', function(e) {
            const plateNumber = document.getElementById('plate_number').value.trim();
            
            if (plateNumber.length < 2) {
                e.preventDefault();
                alert('Please enter a valid plate number.');
                document.getElementById('plate_number').focus();
                return false;
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>