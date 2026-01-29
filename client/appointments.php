<?php
session_start();
require_once __DIR__ . '/../includes/config.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: /vehicare_db/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];


$query = "SELECT a.*, v.vehicle_type, v.make, v.model, v.plate_number, s.service_name,
                 CASE 
                    WHEN a.status = 'pending' THEN '
                    WHEN a.status = 'confirmed' THEN '
                    WHEN a.status = 'in-progress' THEN '
                    WHEN a.status = 'completed' THEN '
                    WHEN a.status = 'cancelled' THEN '
                 END as status_color
          FROM appointments a
          LEFT JOIN vehicles v ON a.vehicle_id = v.vehicle_id
          LEFT JOIN services s ON a.service_id = s.service_id
          WHERE a.user_id = $user_id
          ORDER BY a.appointment_date DESC, a.appointment_time DESC";

$result = $conn->query($query);
$appointments = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
    <!-- Header -->
    <div style="background: linear-gradient(135deg, 
        <div>
            <h1 style="margin: 0 0 10px 0;"><i class="fas fa-calendar"></i> Appointments & Bookings</h1>
            <p style="margin: 0; opacity: 0.9;">Manage your service appointments</p>
        </div>
        <a href="/vehicare_db/client/book_appointment.php" class="btn btn-success" style="background: 
            <i class="fas fa-plus"></i> Book Appointment
        </a>
    </div>

    <!-- Stats -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-left: 4px solid 
            <p style="margin: 0; color: 
            <h3 style="margin: 10px 0 0 0; color: 
                <?php echo count(array_filter($appointments, fn($a) => $a['status'] === 'pending')); ?>
            </h3>
        </div>
        <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-left: 4px solid 
            <p style="margin: 0; color: 
            <h3 style="margin: 10px 0 0 0; color: 
                <?php echo count(array_filter($appointments, fn($a) => $a['status'] === 'confirmed')); ?>
            </h3>
        </div>
        <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-left: 4px solid 
            <p style="margin: 0; color: 
            <h3 style="margin: 10px 0 0 0; color: 
                <?php echo count(array_filter($appointments, fn($a) => $a['status'] === 'in-progress')); ?>
            </h3>
        </div>
        <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-left: 4px solid 
            <p style="margin: 0; color: 
            <h3 style="margin: 10px 0 0 0; color: 
                <?php echo count(array_filter($appointments, fn($a) => $a['status'] === 'completed')); ?>
            </h3>
        </div>
    </div>

    <!-- Appointments List -->
    <?php if (count($appointments) > 0): ?>
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
                        <th style="padding: 15px 20px; text-align: left; color: 
                        <th style="padding: 15px 20px; text-align: center; color: 
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $apt): ?>
                    <tr style="border-bottom: 1px solid 
                        <td style="padding: 15px 20px; color: 
                            <strong><?php echo date('M d, Y', strtotime($apt['appointment_date'])); ?></strong><br>
                            <small style="color: 
                        </td>
                        <td style="padding: 15px 20px; color: 
                            <?php echo htmlspecialchars($apt['vehicle_type']) . ' ' . htmlspecialchars($apt['make']); ?><br>
                            <small style="color: 
                        </td>
                        <td style="padding: 15px 20px; color: #333;">
                            <?php echo $apt['service_name'] ? htmlspecialchars($apt['service_name']) : 'General Service'; ?>
                        </td>
                        <td style="padding: 15px 20px; color: #333;">
                            <span style="background: <?php echo $apt['appointment_type'] === 'appointment' ? '#e74c3c' : '#3498db'; ?>; color: white; padding: 6px 12px; border-radius: 4px; font-size: 12px; font-weight: 600;">
                                <?php echo ucfirst($apt['appointment_type']); ?>
                            </span>
                        </td>
                        <td style="padding: 15px 20px; color: #333;">
                            <span style="background: <?php echo isset($apt['status_color']) ? $apt['status_color'] : '#95a5a6'; ?>; color: white; padding: 6px 12px; border-radius: 4px; font-size: 12px; font-weight: 600;">
                                <?php echo ucfirst($apt['status']); ?>
                            </span>
                        </td>
                        <td style="padding: 15px 20px; text-align: center;">
                            <a href="/vehicare_db/client/appointment_detail.php?id=<?php echo $apt['appointment_id']; ?>" style="color: #2d5a7b; text-decoration: none;">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <?php if ($apt['status'] === 'pending' || $apt['status'] === 'confirmed'): ?>
                            <a href="/vehicare_db/client/edit_appointment.php?id=<?php echo $apt['appointment_id']; ?>" style="color: 
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="/vehicare_db/client/cancel_appointment.php?id=<?php echo $apt['appointment_id']; ?>" style="color: 
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
    <div style="background: white; padding: 60px 20px; border-radius: 12px; text-align: center; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
        <i class="fas fa-calendar" style="font-size: 60px; color: 
        <h4 style="color: 
        <p style="color: 
        <a href="/vehicare_db/client/book_appointment.php" class="btn btn-primary" style="background: 
            <i class="fas fa-plus"></i> Book Appointment Now
        </a>
    </div>
    <?php endif; ?>
</div>

<style>
    tr:hover {
        background: 
    }
    .btn {
        transition: all 0.3s ease;
    }
    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>

