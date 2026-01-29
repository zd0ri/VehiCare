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


$query = "SELECT * FROM vehicles WHERE user_id = $user_id ORDER BY created_at DESC";
$result = $conn->query($query);
$vehicles = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $vehicles[] = $row;
    }
}


$types = ['Sedan', 'SUV', 'Truck', 'Van', 'Motorcycle', 'Other'];


if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $vehicle_id = intval($_GET['id']);
    
    
    $check = "SELECT user_id FROM vehicles WHERE vehicle_id = $vehicle_id";
    $check_result = $conn->query($check);
    
    if ($check_result && $check_result->num_rows > 0) {
        $check_data = $check_result->fetch_assoc();
        if ($check_data['user_id'] == $user_id) {
            $delete = "DELETE FROM vehicles WHERE vehicle_id = $vehicle_id";
            if ($conn->query($delete)) {
                $message = "Vehicle deleted successfully!";
                header("Refresh: 2; url=/vehicare_db/client/vehicles.php");
            } else {
                $error = "Failed to delete vehicle.";
            }
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
    <!-- Header -->
    <div style="background: linear-gradient(135deg, 
        <div>
            <h1 style="margin: 0 0 10px 0;"><i class="fas fa-car"></i> My Vehicles</h1>
            <p style="margin: 0; opacity: 0.9;">Manage your vehicle information</p>
        </div>
        <a href="/vehicare_db/client/add_vehicle.php" class="btn btn-success" style="background: 
            <i class="fas fa-plus"></i> Add New Vehicle
        </a>
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

    <!-- Vehicles Grid -->
    <?php if (count($vehicles) > 0): ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px;">
        <?php foreach ($vehicles as $vehicle): ?>
        <div style="background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); overflow: hidden;">
            <div style="background: linear-gradient(135deg, 
                <h5 style="margin: 0; display: flex; justify-content: space-between; align-items: center;">
                    <span><?php echo htmlspecialchars($vehicle['vehicle_type']); ?></span>
                    <small style="font-size: 12px; background: rgba(255,255,255,0.3); padding: 4px 8px; border-radius: 4px;">
                        <?php echo htmlspecialchars($vehicle['make']); ?>
                    </small>
                </h5>
            </div>
            
            <div style="padding: 20px;">
                <p style="margin: 0 0 10px 0; color: 
                <p style="margin: 0 0 10px 0; color: 
                <p style="margin: 0 0 10px 0; color: 
                <p style="margin: 0 0 10px 0; color: 
                <p style="margin: 0 0 10px 0; color: #666;">Model Year: <?php echo $vehicle['model_year']; ?></p>
                <p style="margin: 0 0 15px 0; color: #666;">
                    Status: 
                    <span style="background: <?php echo $vehicle['status'] === 'active' ? '#28a745' : '#dc3545'; ?>; color: white; padding: 4px 10px; border-radius: 4px; font-size: 12px;">
                        <?php echo ucfirst($vehicle['status']); ?>
                    </span>
                </p>

                <div style="display: flex; gap: 10px;">
                    <a href="/vehicare_db/client/edit_vehicle.php?id=<?php echo $vehicle['vehicle_id']; ?>" class="btn btn-primary" style="flex: 1; background: linear-gradient(135deg, #2d5a7b 0%, #1a3a52 100%); color: white; padding: 10px 15px; border-radius: 6px; text-decoration: none; text-align: center;">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="/vehicare_db/client/vehicles.php?action=delete&id=<?php echo $vehicle['vehicle_id']; ?>" class="btn btn-danger" style="flex: 1; background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; padding: 10px 15px; border-radius: 6px; text-decoration: none; text-align: center;">
                        <i class="fas fa-trash"></i> Delete
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div style="background: white; padding: 60px 20px; border-radius: 12px; text-align: center; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
        <i class="fas fa-car" style="font-size: 60px; color: 
        <h4 style="color: 
        <p style="color: 
        <a href="/vehicare_db/client/add_vehicle.php" class="btn btn-primary" style="background: 
            <i class="fas fa-plus"></i> Add Vehicle Now
        </a>
    </div>
    <?php endif; ?>
</div>

<style>
    .btn {
        transition: all 0.3s ease;
    }
    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>

