<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /vehicare_db/login.php");
    exit;
}

// Fetch parts/inventory
$partsQuery = $conn->query("SELECT * FROM parts ORDER BY part_id DESC");

$page_title = "Parts & Inventory";
$page_icon = "fas fa-box";
include __DIR__ . '/includes/admin_layout_header.php';
?>

<!-- Page Content -->
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Parts & Inventory Management</h3>
        <button class="btn btn-primary" onclick="alert('Add part feature coming soon')">
            <i class="fas fa-plus"></i> Add New Part
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-striped data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Part Name</th>
                    <th>Brand</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($partsQuery && $partsQuery->num_rows > 0): ?>
                    <?php while($part = $partsQuery->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $part['part_id']; ?></td>
                        <td><?php echo htmlspecialchars($part['part_name']); ?></td>
                        <td><?php echo htmlspecialchars($part['brand']); ?></td>
                        <td>₱<?php echo number_format($part['price'], 2); ?></td>
                        <td>
                            <span class="badge bg-<?php echo ($part['stock'] > 10) ? 'success' : 'warning'; ?>">
                                <?php echo $part['stock']; ?> units
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editPart(<?php echo $part['part_id']; ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deletePart(<?php echo $part['part_id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No parts found</td>
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
