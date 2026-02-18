<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /vehicare_db/login.php");
    exit;
}

// Retrieve all parts with inventory information
$inventory = $conn->query("SELECT p.`part_id`, p.`part_name`, p.`brand`, p.`price`, 
                          COALESCE(i.`quantity`, 0) as `quantity`, 
                          i.`last_updated`
                          FROM `parts` p
                          LEFT JOIN `inventory` i ON p.`part_id` = i.`part_id`
                          ORDER BY p.`part_name`");

// Get inventory statistics
$stats = $conn->query("SELECT 
  COUNT(p.`part_id`) as total_parts,
  SUM(i.`quantity`) as total_quantity,
  SUM(p.`price` * i.`quantity`) as total_value,
  COUNT(CASE WHEN i.`quantity` = 0 OR i.`quantity` IS NULL THEN 1 END) as out_of_stock,
  COUNT(CASE WHEN i.`quantity` <= 20 AND i.`quantity` > 0 THEN 1 END) as low_stock
FROM `parts` p
LEFT JOIN `inventory` i ON p.`part_id` = i.`part_id`")->fetch_assoc();

$page_title = "Inventory";
$page_icon = "fas fa-boxes";
include __DIR__ . '/includes/admin_layout_header.php';
?>

<!-- Page Content -->
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Inventory Management</h3>
        <button class="btn btn-primary" onclick="alert('Add Part feature coming soon')">
            <i class="fas fa-plus"></i> Add Part
        </button>
    </div>
    
    <div class="table-responsive">
        <table class="table table-striped data-table">
            <thead>
                <tr>
                    <th>Part Name</th>
                    <th>Brand</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total Value</th>
                    <th>Last Updated</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($inventory && $inventory->num_rows > 0): ?>
                    <?php while ($item = $inventory->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($item['part_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($item['brand'] ?? 'N/A'); ?></td>
                        <td>₱<?php echo number_format($item['price'] ?? 0, 2); ?></td>
                        <td><?php echo $item['quantity'] ?? 0; ?></td>
                        <td>₱<?php echo number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 0), 2); ?></td>
                        <td><?php echo $item['last_updated'] ? date('M d, Y', strtotime($item['last_updated'])) : 'Never'; ?></td>
                        <td>
                            <?php $qty = $item['quantity'] ?? 0; ?>
                            <span class="badge bg-<?php echo $qty > 20 ? 'success' : ($qty > 0 ? 'warning' : 'danger'); ?>">
                                <?php echo $qty > 20 ? 'In Stock' : ($qty > 0 ? 'Low Stock' : 'Out of Stock'); ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editPart(<?php echo $item['part_id']; ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deletePart(<?php echo $item['part_id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center">No inventory items found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function editPart(id) {
    alert('Edit part ' + id);
}

function deletePart(id) {
    if (confirm('Are you sure you want to delete this part?')) {
        alert('Delete part ' + id);
    }
}
</script>

<?php include __DIR__ . '/includes/admin_layout_footer.php'; ?>
