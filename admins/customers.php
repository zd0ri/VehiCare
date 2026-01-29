<?php

session_start();
require_once __DIR__ . '/../includes/config.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /vehicare_db/login.php");
    exit;
}

$message = '';
$error = '';


$query = "SELECT u.*, cp.contact_number, cp.address, cp.city, cp.is_profile_complete,
                 (SELECT COUNT(*) FROM vehicles WHERE user_id = u.user_id) as vehicle_count,
                 (SELECT COUNT(*) FROM appointments WHERE user_id = u.user_id) as appointment_count
          FROM users u
          LEFT JOIN customer_profiles cp ON u.user_id = cp.user_id
          WHERE u.role = 'client'
          ORDER BY u.created_at DESC";

$result = $conn->query($query);
$customers = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
}


if (isset($_GET['action']) && $_GET['action'] === 'toggle_status' && isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
    $get_status = "SELECT status FROM users WHERE user_id = $user_id AND role = 'client'";
    $status_result = $conn->query($get_status);
    
    if ($status_result && $status_result->num_rows > 0) {
        $user_data = $status_result->fetch_assoc();
        $new_status = $user_data['status'] === 'active' ? 'inactive' : 'active';
        
        $update = "UPDATE users SET status = '$new_status' WHERE user_id = $user_id";
        if ($conn->query($update)) {
            $message = "Customer status updated successfully!";
            header("Refresh: 1; url=/vehicare_db/admins/customers.php");
        } else {
            $error = "Failed to update status.";
        }
    }
}

$page_title = 'Customer Management';
$page_icon = 'fas fa-users';
include __DIR__ . '/includes/admin_layout_header.php';
?>

<div style="flex: 1; overflow-y: auto; padding: 30px;">
    <!-- Header -->
    <div style="background: linear-gradient(135deg, 
        <h1 style="margin: 0 0 5px 0; font-size: 28px;"><i class="fas fa-users"></i> Customer Management</h1>
        <p style="margin: 0; opacity: 0.9;">View and manage all registered customers</p>
    </div>

    <!-- Messages -->
    <?php if ($message): ?>
    <div style="background: 
        <i class="fas fa-check-circle"></i> <?php echo $message; ?>
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div style="background: 
        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
    </div>
    <?php endif; ?>

    <!-- Stats -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <p style="margin: 0; color: 
            <h3 style="margin: 10px 0 0 0; color: 
        </div>
        <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <p style="margin: 0; color: 
            <h3 style="margin: 10px 0 0 0; color: 
                <?php echo count(array_filter($customers, fn($c) => $c['status'] === 'active')); ?>
            </h3>
        </div>
        <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <p style="margin: 0; color: 
            <h3 style="margin: 10px 0 0 0; color: 
                <?php echo count(array_filter($customers, fn($c) => $c['is_profile_complete'])); ?>
            </h3>
        </div>
    </div>

    <!-- Customers Table -->
    <div style="background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); overflow: hidden;">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: 
                        <th style="padding: 15px 20px; text-align: left; color: 
                        <th style="padding: 15px 20px; text-align: left; color: 
                        <th style="padding: 15px 20px; text-align: left; color: 
                        <th style="padding: 15px 20px; text-align: center; color: 
                        <th style="padding: 15px 20px; text-align: center; color: 
                        <th style="padding: 15px 20px; text-align: center; color: 
                        <th style="padding: 15px 20px; text-align: center; color: 
                        <th style="padding: 15px 20px; text-align: center; color: 
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                    <tr style="border-bottom: 1px solid 
                        <td style="padding: 15px 20px; color: 
                            <strong><?php echo htmlspecialchars($customer['full_name']); ?></strong>
                        </td>
                        <td style="padding: 15px 20px; color: 
                            <?php echo htmlspecialchars($customer['email']); ?>
                        </td>
                        <td style="padding: 15px 20px; color: 
                            <?php echo $customer['contact_number'] ? htmlspecialchars($customer['contact_number']) : 'N/A'; ?>
                        </td>
                        <td style="padding: 15px 20px; text-align: center; color: 
                            <?php echo $customer['vehicle_count']; ?>
                        </td>
                        <td style="padding: 15px 20px; text-align: center; color: 
                            <?php echo $customer['appointment_count']; ?>
                        </td>
                        <td style="padding: 15px 20px; text-align: center;">
                            <span style="background: <?php echo $customer['is_profile_complete'] ? '#28a745' : '#dc3545'; ?>; color: white; padding: 6px 12px; border-radius: 4px; font-size: 12px; font-weight: 600;">
                                <?php echo $customer['is_profile_complete'] ? 'COMPLETE' : 'INCOMPLETE'; ?>
                            </span>
                        </td>
                        <td style="padding: 15px 20px; text-align: center;">
                            <span style="background: <?php echo $customer['status'] === 'active' ? '#28a745' : '#dc3545'; ?>; color: white; padding: 6px 12px; border-radius: 4px; font-size: 12px; font-weight: 600;">
                                <?php echo strtoupper($customer['status']); ?>
                            </span>
                        </td>
                        <td style="padding: 15px 20px; text-align: center; font-size: 13px;">
                            <a href="/vehicare_db/admins/customer_detail.php?id=<?php echo $customer['user_id']; ?>" style="color: #2d5a7b; margin-right: 10px; text-decoration: none;">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <a href="/vehicare_db/admins/customers.php?action=toggle_status&id=<?php echo $customer['user_id']; ?>" style="color: <?php echo $customer['status'] === 'active' ? '#dc3545' : '#28a745'; ?>; text-decoration: none;">
                                <i class="fas fa-toggle-<?php echo $customer['status'] === 'active' ? 'on' : 'off'; ?>"></i> <?php echo $customer['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (count($customers) === 0): ?>
    <div style="background: white; padding: 60px 20px; border-radius: 8px; text-align: center;">
        <i class="fas fa-users" style="font-size: 48px; color: 
        <h4 style="color: 
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/admin_layout_footer.php'; ?>

