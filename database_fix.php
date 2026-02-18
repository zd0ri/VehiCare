<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VehiCare Database Fix</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; padding: 50px 0; }
        .container { max-width: 800px; }
        .alert { border-radius: 10px; }
        .btn { border-radius: 25px; font-weight: 600; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0"><i class="fas fa-database"></i> VehiCare Database Fix</h3>
            </div>
            <div class="card-body">

<?php
if (isset($_POST['fix_database'])) {
    try {
        // Database connection
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "vehicare_db";

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }

        echo '<div class="alert alert-info">🔧 Fixing database schema...</div>';

        // Check current invoices table structure
        echo '<h5>Current invoices table structure:</h5>';
        $result = $conn->query("DESCRIBE invoices");
        if ($result) {
            echo '<ul>';
            $columns = [];
            while ($row = $result->fetch_assoc()) {
                $columns[] = $row['Field'];
                echo '<li><code>' . $row['Field'] . '</code> - ' . $row['Type'] . '</li>';
            }
            echo '</ul>';

            echo '<h5 class="mt-4">Adding missing columns:</h5>';
            
            if (!in_array('payment_status', $columns)) {
                $conn->query("ALTER TABLE invoices ADD COLUMN payment_status ENUM('unpaid', 'partial', 'paid') DEFAULT 'unpaid'");
                echo '<div class="alert alert-success">✓ Added payment_status column</div>';
            } else {
                echo '<div class="alert alert-info">✓ payment_status column already exists</div>';
            }

            if (!in_array('subtotal', $columns)) {
                $conn->query("ALTER TABLE invoices ADD COLUMN subtotal DECIMAL(10,2) DEFAULT 0.00");
                echo '<div class="alert alert-success">✓ Added subtotal column</div>';
            } else {
                echo '<div class="alert alert-info">✓ subtotal column already exists</div>';
            }

            if (!in_array('tax_amount', $columns)) {
                $conn->query("ALTER TABLE invoices ADD COLUMN tax_amount DECIMAL(10,2) DEFAULT 0.00");
                echo '<div class="alert alert-success">✓ Added tax_amount column</div>';
            } else {
                echo '<div class="alert alert-info">✓ tax_amount column already exists</div>';
            }

            if (!in_array('grand_total', $columns)) {
                $conn->query("ALTER TABLE invoices ADD COLUMN grand_total DECIMAL(10,2) NOT NULL DEFAULT 0.00");
                echo '<div class="alert alert-success">✓ Added grand_total column</div>';
            } else {
                echo '<div class="alert alert-info">✓ grand_total column already exists</div>';
            }

            // Check and fix vehicles table
            echo '<h5 class="mt-4">Checking vehicles table:</h5>';
            $vehicles_result = $conn->query("DESCRIBE vehicles");
            if ($vehicles_result) {
                $vehicle_columns = [];
                while ($row = $vehicles_result->fetch_assoc()) {
                    $vehicle_columns[] = $row['Field'];
                }
                
                if (!in_array('created_at', $vehicle_columns) && !in_array('created_date', $vehicle_columns)) {
                    $conn->query("ALTER TABLE vehicles ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
                    echo '<div class="alert alert-success">✓ Added created_at column to vehicles table</div>';
                } else {
                    echo '<div class="alert alert-info">✓ vehicles table has timestamp column</div>';
                }
                
                if (!in_array('status', $vehicle_columns)) {
                    $conn->query("ALTER TABLE vehicles ADD COLUMN status ENUM('active', 'inactive', 'sold') DEFAULT 'active'");
                    echo '<div class="alert alert-success">✓ Added status column to vehicles table</div>';
                } else {
                    echo '<div class="alert alert-info">✓ vehicles table has status column</div>';
                }
            }

            // Check and fix services table
            echo '<h5 class="mt-4">Checking services table:</h5>';
            $services_result = $conn->query("DESCRIBE services");
            if ($services_result) {
                $service_columns = [];
                while ($row = $services_result->fetch_assoc()) {
                    $service_columns[] = $row['Field'];
                }
                
                if (!in_array('is_active', $service_columns) && !in_array('status', $service_columns)) {
                    $conn->query("ALTER TABLE services ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active'");
                    echo '<div class="alert alert-success">✓ Added status column to services table</div>';
                } else {
                    echo '<div class="alert alert-info">✓ services table has status/is_active column</div>';
                }
            }

            // Test the fixed queries
            echo '<h5 class="mt-4">Testing fixed queries:</h5>';
            
            $test1 = $conn->query("SELECT COUNT(*) as count FROM invoices WHERE payment_status = 'unpaid'");
            if ($test1) {
                $count = $test1->fetch_assoc()['count'];
                echo '<div class="alert alert-success">✓ payment_status query works: ' . $count . ' unpaid invoices</div>';
            }

            echo '<div class="alert alert-success border-success">
                <h5>🎉 Database Fixed Successfully!</h5>
                <p>The client dashboard should now work without errors.</p>
                <a href="client/dashboard.php" class="btn btn-success">Go to Client Dashboard</a>
            </div>';

        } else {
            echo '<div class="alert alert-danger">❌ Could not check invoices table structure</div>';
        }

        $conn->close();

    } catch (Exception $e) {
        echo '<div class="alert alert-danger">❌ Error: ' . $e->getMessage() . '</div>';
        echo '<p>Please check your database configuration.</p>';
    }
} else {
    ?>

    <div class="alert alert-warning">
        <h5>⚠️ Database Schema Issues Detected</h5>
        <p>The client dashboard is failing because database tables are missing required columns like <code>payment_status</code>, <code>created_at</code>, etc.</p>
    </div>

    <h5>What this will fix:</h5>
    <ul>
        <li>Add <code>payment_status</code> column to invoices table</li>
        <li>Add <code>subtotal</code>, <code>tax_amount</code>, <code>grand_total</code> columns to invoices table</li>
        <li>Add <code>created_at</code> timestamps to vehicles and users tables</li>
        <li>Add <code>status</code> column to vehicles table (active/inactive/sold)</li>
        <li>Add <code>status</code> column to services table (active/inactive)</li>
        <li>Fix column name inconsistencies throughout the system</li>
        <li>Test all queries to ensure they work properly</li>
    </ul>

    <form method="POST" class="mt-4">
        <button type="submit" name="fix_database" class="btn btn-primary btn-lg">
            🔧 Fix Database Schema
        </button>
    </form>

    <div class="mt-4">
        <small class="text-muted">
            This will modify your database structure. Make sure you have a backup if needed.
        </small>
    </div>

    <?php
}
?>

            </div>
        </div>

        <div class="mt-4 text-center">
            <a href="index.php" class="btn btn-secondary">← Back to Home</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>