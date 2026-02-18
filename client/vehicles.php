<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

// Check if client is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: /vehicare_db/login.php");
    exit;
}

$client_id = $_SESSION['user_id'];

// Handle vehicle deletion
if (isset($_POST['delete_vehicle'])) {
    $vehicle_id = $_POST['vehicle_id'];
    
    // Check if vehicle has appointments
    $check_appointments = $conn->prepare("SELECT COUNT(*) as count FROM appointments WHERE vehicle_id = ? AND status IN ('pending', 'confirmed', 'in-progress')");
    $check_appointments->bind_param("i", $vehicle_id);
    $check_appointments->execute();
    $active_appointments = $check_appointments->get_result()->fetch_assoc()['count'];
    
    if ($active_appointments > 0) {
        $_SESSION['error'] = "Cannot delete vehicle with active appointments. Please cancel or complete them first.";
    } else {
        // Soft delete - mark as archived
        $stmt = $conn->prepare("UPDATE vehicles SET status = 'archived', updated_date = CURRENT_TIMESTAMP WHERE vehicle_id = ? AND client_id = ?");
        $stmt->bind_param("ii", $vehicle_id, $client_id);
        
        if ($stmt->execute()) {
            log_event($client_id, "vehicle_deleted", "Archived vehicle #$vehicle_id");
            $_SESSION['success'] = "Vehicle removed successfully.";
        } else {
            $_SESSION['error'] = "Failed to remove vehicle.";
        }
    }
    
    header("Location: /vehicare_db/client/vehicles.php");
    exit;
}

// Get client's vehicles
$vehicles = $conn->query("
    SELECT v.*, 
           COUNT(a.appointment_id) as total_appointments,
           COUNT(CASE WHEN a.status = 'completed' THEN 1 END) as completed_appointments,
           MAX(a.appointment_date) as last_service_date
    FROM vehicles v
    LEFT JOIN appointments a ON v.vehicle_id = a.vehicle_id
    WHERE v.client_id = $client_id AND v.status IN ('active', 'inactive')
    GROUP BY v.vehicle_id
    ORDER BY v.created_at DESC
");

include __DIR__ . '/../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Vehicles - VehiCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #f8f9fa;
        }

        .vehicles-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .page-header {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
            color: white;
            padding: 30px 40px;
            border-radius: 20px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-header h1 {
            margin: 0;
            font-weight: 700;
        }

        .vehicles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
        }

        .vehicle-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .vehicle-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }

        .vehicle-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #27ae60, #2ed573);
        }

        .vehicle-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .vehicle-plate {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 8px 15px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 1.1em;
            letter-spacing: 1px;
        }

        .vehicle-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-active { 
            background: #d4edda; 
            color: #155724; 
        }

        .status-inactive { 
            background: #fff3cd; 
            color: #856404; 
        }

        .vehicle-info {
            margin-bottom: 20px;
        }

        .vehicle-title {
            font-size: 1.4em;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .vehicle-details {
            color: #7f8c8d;
            font-size: 0.95em;
        }

        .vehicle-specs {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin: 20px 0;
        }

        .spec-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .spec-icon {
            width: 35px;
            height: 35px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9em;
            color: white;
        }

        .spec-content {
            flex: 1;
        }

        .spec-content .label {
            font-size: 0.8em;
            color: #7f8c8d;
            font-weight: 500;
        }

        .spec-content .value {
            font-weight: 600;
            color: #2c3e50;
        }

        .vehicle-stats {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .stat {
            text-align: center;
        }

        .stat-number {
            font-size: 1.5em;
            font-weight: 700;
            color: #27ae60;
        }

        .stat-label {
            font-size: 0.8em;
            color: #7f8c8d;
            margin-top: 2px;
        }

        .vehicle-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            border-radius: 25px;
            font-weight: 600;
            padding: 8px 16px;
            font-size: 0.85em;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            border: none;
        }

        .btn-success {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
            border: none;
        }

        .btn-outline-secondary {
            border-color: #95a5a6;
            color: #95a5a6;
        }

        .btn-outline-danger {
            border-color: #e74c3c;
            color: #e74c3c;
        }

        .no-vehicles {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .no-vehicles i {
            font-size: 5em;
            color: #bdc3c7;
            margin-bottom: 25px;
        }

        .no-vehicles h3 {
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .no-vehicles p {
            color: #7f8c8d;
            font-size: 1.1em;
            margin-bottom: 25px;
        }

        @media (max-width: 768px) {
            .page-header {
                padding: 20px;
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .vehicles-grid {
                grid-template-columns: 1fr;
            }

            .vehicle-specs {
                grid-template-columns: 1fr;
            }

            .vehicle-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="vehicles-container">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1><i class="fas fa-car me-3"></i>My Vehicles</h1>
                <p class="mb-0">Manage your registered vehicles and service history</p>
            </div>
            <a href="/vehicare_db/client/add-vehicle.php" class="btn btn-light">
                <i class="fas fa-plus me-2"></i>Add New Vehicle
            </a>
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

        <!-- Vehicles Grid -->
        <?php if ($vehicles && $vehicles->num_rows > 0): ?>
            <div class="vehicles-grid">
                <?php while ($vehicle = $vehicles->fetch_assoc()): ?>
                    <div class="vehicle-card">
                        <div class="vehicle-header">
                            <div class="vehicle-plate"><?php echo htmlspecialchars($vehicle['plate_number']); ?></div>
                            <span class="vehicle-status status-<?php echo $vehicle['status']; ?>">
                                <?php echo ucfirst($vehicle['status']); ?>
                            </span>
                        </div>

                        <div class="vehicle-info">
                            <div class="vehicle-title">
                                <?php echo htmlspecialchars($vehicle['car_brand'] . ' ' . $vehicle['car_model']); ?>
                            </div>
                            <div class="vehicle-details">
                                <?php echo htmlspecialchars($vehicle['year_model'] . ' • ' . $vehicle['color']); ?>
                                <?php if ($vehicle['engine_type']): ?>
                                    • <?php echo htmlspecialchars($vehicle['engine_type']); ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="vehicle-specs">
                            <?php if ($vehicle['transmission_type']): ?>
                            <div class="spec-item">
                                <div class="spec-icon" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);">
                                    <i class="fas fa-cogs"></i>
                                </div>
                                <div class="spec-content">
                                    <div class="label">Transmission</div>
                                    <div class="value"><?php echo ucfirst($vehicle['transmission_type']); ?></div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if ($vehicle['mileage']): ?>
                            <div class="spec-item">
                                <div class="spec-icon" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                                    <i class="fas fa-tachometer-alt"></i>
                                </div>
                                <div class="spec-content">
                                    <div class="label">Mileage</div>
                                    <div class="value"><?php echo number_format($vehicle['mileage']); ?> km</div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="spec-item">
                                <div class="spec-icon" style="background: linear-gradient(135deg, #f39c12 0%, #d68910 100%);">
                                    <i class="fas fa-calendar-plus"></i>
                                </div>
                                <div class="spec-content">
                                    <div class="label">Added</div>
                                    <div class="value"><?php echo date('M j, Y', strtotime($vehicle['created_at'] ?? $vehicle['created_date'] ?? date('Y-m-d'))); ?></div>
                                </div>
                            </div>

                            <?php if ($vehicle['last_service_date']): ?>
                            <div class="spec-item">
                                <div class="spec-icon" style="background: linear-gradient(135deg, #27ae60 0%, #229954 100%);">
                                    <i class="fas fa-wrench"></i>
                                </div>
                                <div class="spec-content">
                                    <div class="label">Last Service</div>
                                    <div class="value"><?php echo date('M j, Y', strtotime($vehicle['last_service_date'])); ?></div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="vehicle-stats">
                            <div class="stat">
                                <div class="stat-number"><?php echo $vehicle['total_appointments']; ?></div>
                                <div class="stat-label">Total Services</div>
                            </div>
                            <div class="stat">
                                <div class="stat-number"><?php echo $vehicle['completed_appointments']; ?></div>
                                <div class="stat-label">Completed</div>
                            </div>
                        </div>

                        <div class="vehicle-actions">
                            <a href="/vehicare_db/client/book-appointment.php?vehicle=<?php echo $vehicle['vehicle_id']; ?>" 
                               class="btn btn-primary flex-fill">
                                <i class="fas fa-calendar-plus me-1"></i>Book Service
                            </a>
                            
                            <a href="/vehicare_db/client/edit-vehicle.php?id=<?php echo $vehicle['vehicle_id']; ?>" 
                               class="btn btn-outline-secondary">
                                <i class="fas fa-edit"></i>
                            </a>
                            
                            <button type="button" 
                                    class="btn btn-outline-danger" 
                                    onclick="confirmDelete(<?php echo $vehicle['vehicle_id']; ?>, '<?php echo htmlspecialchars($vehicle['plate_number']); ?>')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <!-- No Vehicles -->
            <div class="no-vehicles">
                <i class="fas fa-car"></i>
                <h3>No Vehicles Registered</h3>
                <p>Add your first vehicle to start booking maintenance appointments and tracking service history.</p>
                <a href="/vehicare_db/client/add-vehicle.php" class="btn btn-success btn-lg">
                    <i class="fas fa-plus me-2"></i>Add Your First Vehicle
                </a>
            </div>
        <?php endif; ?>

        <!-- Back to Dashboard -->
        <div class="text-center mt-4">
            <a href="/vehicare_db/client/dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Confirm Vehicle Removal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to remove vehicle <strong id="vehiclePlate"></strong>?</p>
                    <p class="text-muted">This action will archive the vehicle. You can still view its service history.</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="vehicle_id" id="deleteVehicleId">
                        <button type="submit" name="delete_vehicle" class="btn btn-danger">Remove Vehicle</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete(vehicleId, plateNumber) {
            document.getElementById('deleteVehicleId').value = vehicleId;
            document.getElementById('vehiclePlate').textContent = plateNumber;
            
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>