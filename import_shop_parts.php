<?php
require_once __DIR__ . '/includes/config.php';

$parts = [
    ['name' => 'High Performance Air Filter', 'brand' => 'AutoParts Inc', 'price' => 45.99],
    ['name' => 'Ceramic Brake Pads', 'brand' => 'BrakeMaster', 'price' => 89.99],
    ['name' => 'Oil Filter Kit', 'brand' => 'AutoParts Inc', 'price' => 34.50],
    ['name' => 'Engine Spark Plugs (Set of 4)', 'brand' => 'EngineExperts', 'price' => 125.00],
    ['name' => 'Suspension Coil Springs', 'brand' => 'SuspensionTech', 'price' => 250.00],
    ['name' => 'Radiator Cooling Unit', 'brand' => 'CoolingSystems', 'price' => 185.99],
    ['name' => 'LED Headlight Assembly', 'brand' => 'LightSystems', 'price' => 299.99],
    ['name' => 'Automatic Transmission Fluid', 'brand' => 'FluidDynamics', 'price' => 42.50],
    ['name' => 'Premium Alloy Wheels (17")', 'brand' => 'WheelMasters', 'price' => 450.00],
    ['name' => 'Engine Gasket Set', 'brand' => 'EngineExperts', 'price' => 75.00],
    ['name' => 'Brake Rotors (Pair)', 'brand' => 'BrakeMaster', 'price' => 165.00],
    ['name' => 'Power Steering Pump', 'brand' => 'LightSystems', 'price' => 210.00]
];

$inserted = 0;
$skipped = 0;

foreach ($parts as $part) {
    // Check if part already exists
    $check = $conn->query("SELECT part_id FROM parts WHERE part_name = '" . $conn->real_escape_string($part['name']) . "'");
    
    if ($check && $check->num_rows > 0) {
        $skipped++;
        continue;
    }
    
    // Insert part with 50 stock
    $query = "INSERT INTO parts (part_name, brand, price, stock) 
              VALUES ('" . $conn->real_escape_string($part['name']) . "', 
                      '" . $conn->real_escape_string($part['brand']) . "', 
                      " . $part['price'] . ", 
                      50)";
    
    if ($conn->query($query)) {
        $inserted++;
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Shop Parts Import</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f7fa;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
        }
        .icon {
            font-size: 60px;
            color: #dc143c;
            margin-bottom: 20px;
        }
        h1 {
            color: #1a1a1a;
            margin-bottom: 20px;
            font-weight: 700;
        }
        .stat {
            font-size: 24px;
            font-weight: 700;
            margin: 20px 0;
        }
        .inserted {
            color: #27ae60;
        }
        .skipped {
            color: #f39c12;
        }
        .success-msg {
            color: #27ae60;
            font-weight: 600;
            margin-top: 20px;
        }
        .btn-group {
            margin-top: 30px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: center;
        }
        .btn {
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-primary {
            background: #dc143c;
            color: white;
        }
        .btn-primary:hover {
            background: #a01030;
            transform: translateY(-2px);
        }
        .btn-secondary {
            background: #f5f5f5;
            color: #1a1a1a;
            border: 1px solid #ddd;
        }
        .btn-secondary:hover {
            background: #e8e8e8;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h1>Shop Parts Imported Successfully!</h1>
        
        <p style="color: #666; font-size: 16px;">All shop parts have been added to the inventory system.</p>
        
        <div class="stat inserted">
            <i class="fas fa-plus-circle"></i> <?php echo $inserted; ?> Parts Inserted
        </div>
        
        <?php if ($skipped > 0): ?>
        <div class="stat skipped">
            <i class="fas fa-info-circle"></i> <?php echo $skipped; ?> Parts Skipped (Already Exist)
        </div>
        <?php endif; ?>
        
        <div class="success-msg">
            ✓ All 12 shop parts are now available in your inventory system
        </div>
        
        <div class="btn-group">
            <a href="/vehicare_db/admins/inventory.php" class="btn btn-primary">
                <i class="fas fa-boxes"></i> View Inventory
            </a>
            <a href="/vehicare_db/shop.php" class="btn btn-secondary">
                <i class="fas fa-shopping-bag"></i> View Shop
            </a>
        </div>
    </div>
</body>
</html>
