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


$types = ['Sedan', 'SUV', 'Truck', 'Van', 'Motorcycle', 'Other'];


$edit_mode = false;
$vehicle_data = null;

if (isset($_GET['id'])) {
    $vehicle_id = intval($_GET['id']);
    $query = "SELECT * FROM vehicles WHERE vehicle_id = $vehicle_id AND user_id = $user_id";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $vehicle_data = $result->fetch_assoc();
        $edit_mode = true;
    } else {
        $error = "Vehicle not found.";
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vehicle_type = isset($_POST['vehicle_type']) ? trim($_POST['vehicle_type']) : '';
    $make = isset($_POST['make']) ? trim($_POST['make']) : '';
    $model = isset($_POST['model']) ? trim($_POST['model']) : '';
    $year = isset($_POST['year']) ? intval($_POST['year']) : 0;
    $plate_number = isset($_POST['plate_number']) ? trim($_POST['plate_number']) : '';
    $color = isset($_POST['color']) ? trim($_POST['color']) : '';
    $mileage = isset($_POST['mileage']) ? intval($_POST['mileage']) : 0;
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $status = isset($_POST['status']) ? trim($_POST['status']) : 'active';

    $errors = [];

    if (empty($vehicle_type)) $errors[] = "Vehicle type is required";
    if (empty($make)) $errors[] = "Make is required";
    if (empty($model)) $errors[] = "Model is required";
    if (empty($year)) $errors[] = "Year is required";
    if (empty($plate_number)) $errors[] = "Plate number is required";
    if (empty($color)) $errors[] = "Color is required";

    
    $plate_check = "SELECT vehicle_id FROM vehicles WHERE plate_number = '$plate_number' AND user_id = $user_id";
    if ($edit_mode) {
        $plate_check .= " AND vehicle_id != " . $vehicle_data['vehicle_id'];
    }
    $plate_result = $conn->query($plate_check);
    if ($plate_result && $plate_result->num_rows > 0) {
        $errors[] = "This plate number is already registered.";
    }

    if (empty($errors)) {
        if ($edit_mode) {
            
            $update_query = "UPDATE vehicles SET 
                            vehicle_type = '$vehicle_type',
                            make = '$make',
                            model = '$model',
                            year = $year,
                            plate_number = '$plate_number',
                            color = '$color',
                            mileage = $mileage,
                            description = '$description',
                            status = '$status',
                            updated_at = NOW()
                            WHERE vehicle_id = " . $vehicle_data['vehicle_id'];
            
            if ($conn->query($update_query)) {
                $message = "Vehicle updated successfully!";
                header("Refresh: 2; url=/vehicare_db/client/vehicles.php");
            } else {
                $error = "Failed to update vehicle.";
            }
        } else {
            
            $insert_query = "INSERT INTO vehicles 
                            (user_id, vehicle_type, make, model, year, plate_number, color, mileage, description, status)
                            VALUES ($user_id, '$vehicle_type', '$make', '$model', $year, '$plate_number', '$color', $mileage, '$description', '$status')";
            
            if ($conn->query($insert_query)) {
                $message = "Vehicle added successfully!";
                header("Refresh: 2; url=/vehicare_db/client/vehicles.php");
            } else {
                $error = "Failed to add vehicle.";
            }
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
        <h1 style="margin: 0 0 10px 0;"><i class="fas fa-car"></i> <?php echo $edit_mode ? 'Edit Vehicle' : 'Add New Vehicle'; ?></h1>
        <p style="margin: 0; opacity: 0.9;">Enter your vehicle information</p>
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
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Vehicle Type <span style="color: 
                    <select class="form-control" name="vehicle_type" required>
                        <option value="">Select Type</option>
                        <?php foreach ($types as $type): ?>
                        <option value="<?php echo $type; ?>" <?php echo ($vehicle_data && $vehicle_data['vehicle_type'] === $type) ? 'selected' : ''; ?>>
                            <?php echo $type; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Make/Brand <span style="color: 
                    <input type="text" class="form-control" name="make" 
                           value="<?php echo $vehicle_data ? htmlspecialchars($vehicle_data['make']) : ''; ?>" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Model <span style="color: 
                    <input type="text" class="form-control" name="model" 
                           value="<?php echo $vehicle_data ? htmlspecialchars($vehicle_data['model']) : ''; ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Year <span style="color: 
                    <input type="number" class="form-control" name="year" min="1900" max="<?php echo date('Y'); ?>"
                           value="<?php echo $vehicle_data ? $vehicle_data['year'] : ''; ?>" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Plate Number <span style="color: 
                    <input type="text" class="form-control" name="plate_number" 
                           value="<?php echo $vehicle_data ? htmlspecialchars($vehicle_data['plate_number']) : ''; ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Color <span style="color: 
                    <input type="text" class="form-control" name="color" 
                           value="<?php echo $vehicle_data ? htmlspecialchars($vehicle_data['color']) : ''; ?>" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Mileage (km)</label>
                    <input type="number" class="form-control" name="mileage" min="0"
                           value="<?php echo $vehicle_data ? $vehicle_data['mileage'] : '0'; ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-control" name="status">
                        <option value="active" <?php echo (!$vehicle_data || $vehicle_data['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($vehicle_data && $vehicle_data['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                        <option value="maintenance" <?php echo ($vehicle_data && $vehicle_data['status'] === 'maintenance') ? 'selected' : ''; ?>>In Maintenance</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Description/Notes</label>
                <textarea class="form-control" name="description" rows="3" placeholder="Any additional notes about your vehicle..."><?php echo $vehicle_data ? htmlspecialchars($vehicle_data['description']) : ''; ?></textarea>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary" style="background: 
                    <i class="fas fa-save"></i> <?php echo $edit_mode ? 'Update Vehicle' : 'Add Vehicle'; ?>
                </button>
                <a href="/vehicare_db/client/vehicles.php" class="btn btn-secondary" style="padding: 10px 30px;">
                    <i class="fas fa-arrow-left"></i> Back to Vehicles
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
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>

