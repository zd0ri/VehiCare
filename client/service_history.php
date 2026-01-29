<?php
session_start();
require_once __DIR__ . '/../includes/config.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: /vehicare_db/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];


$query = "SELECT sh.*, v.vehicle_type, v.make, v.model, v.plate_number, 
                 s.service_name, u.full_name as staff_name
          FROM service_history sh
          LEFT JOIN vehicles v ON sh.vehicle_id = v.vehicle_id
          LEFT JOIN services s ON sh.service_id = s.service_id
          LEFT JOIN users u ON sh.staff_member = u.user_id
          WHERE sh.user_id = $user_id
          ORDER BY sh.service_date DESC";

$result = $conn->query($query);
$history = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $history[] = $row;
    }
}


$spending_query = "SELECT SUM(service_cost) as total FROM service_history WHERE user_id = $user_id";
$spending_result = $conn->query($spending_query);
$total_spent = 0;
if ($spending_result && $spending_result->num_rows > 0) {
    $spending_data = $spending_result->fetch_assoc();
    $total_spent = $spending_data['total'] ?? 0;
}

include __DIR__ . '/../includes/header.php';
?>

<div style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
    <!-- Header -->
    <div style="background: linear-gradient(135deg, 
        <h1 style="margin: 0 0 10px 0;"><i class="fas fa-history"></i> Service History</h1>
        <p style="margin: 0; opacity: 0.9;">View all your vehicle service records</p>
    </div>

    <!-- Stats -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-left: 4px solid 
            <p style="margin: 0; color: 
            <h3 style="margin: 10px 0 0 0; color: 
        </div>
        <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-left: 4px solid 
            <p style="margin: 0; color: 
            <h3 style="margin: 10px 0 0 0; color: 
        </div>
        <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-left: 4px solid 
            <p style="margin: 0; color: 
            <h3 style="margin: 10px 0 0 0; color: 
                â‚±<?php echo count($history) > 0 ? number_format($total_spent / count($history), 2) : '0.00'; ?>
            </h3>
        </div>
    </div>

    <!-- History List -->
    <?php if (count($history) > 0): ?>
    <div style="background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); overflow: hidden;">
        <div style="background: 
            <h5 style="margin: 0; color: 
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: 
                        <th style="padding: 15px 20px; text-align: left; color: 
                        <th style="padding: 15px 20px; text-align: left; color: 
                        <th style="padding: 15px 20px; text-align: left; color: 
                        <th style="padding: 15px 20px; text-align: left; color: 
                        <th style="padding: 15px 20px; text-align: right; color: 
                        <th style="padding: 15px 20px; text-align: center; color: 
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $record): ?>
                    <tr style="border-bottom: 1px solid 
                        <td style="padding: 15px 20px; color: 
                            <strong><?php echo date('M d, Y', strtotime($record['service_date'])); ?></strong><br>
                            <small style="color: 
                        </td>
                        <td style="padding: 15px 20px; color: 
                            <?php echo htmlspecialchars($record['vehicle_type']) . ' ' . htmlspecialchars($record['make']); ?><br>
                            <small style="color: 
                        </td>
                        <td style="padding: 15px 20px; color: 
                            <?php echo $record['service_name'] ? htmlspecialchars($record['service_name']) : 'General Service'; ?>
                        </td>
                        <td style="padding: 15px 20px; color: 
                            <?php echo $record['staff_name'] ? htmlspecialchars($record['staff_name']) : 'N/A'; ?>
                        </td>
                        <td style="padding: 15px 20px; color: 
                            â‚±<?php echo $record['service_cost'] ? number_format($record['service_cost'], 2) : '0.00'; ?>
                        </td>
                        <td style="padding: 15px 20px; text-align: center;">
                            <a href="/vehicare_db/client/service_detail.php?id=<?php echo $record['history_id']; ?>" style="color: 
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
    <div style="background: white; padding: 60px 20px; border-radius: 12px; text-align: center; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
        <i class="fas fa-history" style="font-size: 60px; color: 
        <h4 style="color: 
        <p style="color: 
        <a href="/vehicare_db/client/book_appointment.php" class="btn btn-primary" style="background: 
            <i class="fas fa-calendar-plus"></i> Book a Service Now
        </a>
    </div>
    <?php endif; ?>
</div>

<style>
    tr:hover {
        background: 
    }
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>

