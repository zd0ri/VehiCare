<?php

session_start();
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /vehicare_db/login.php");
    exit;
}

$page_title = 'Inventory Management';
$page_icon = 'fas fa-warehouse';
include __DIR__ . '/includes/admin_layout_header.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_part') {
        $part_name = $_POST['part_name'];
        $part_code = $_POST['part_code'];
        $category = $_POST['category'];
        $description = $_POST['description'];
        $quantity = intval($_POST['quantity']);
        $reorder_level = intval($_POST['reorder_level']);
        $unit_price = floatval($_POST['unit_price']);
        $supplier = $_POST['supplier'];
        
        $stmt = $conn->prepare("INSERT INTO inventory_parts (part_name, part_code, category, description, quantity, reorder_level, unit_price, supplier) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssidis', $part_name, $part_code, $category, $description, $quantity, $reorder_level, $unit_price, $supplier);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Part added successfully!";
        }
    }
    
    if ($_POST['action'] === 'update_part') {
        $part_id = intval($_POST['part_id']);
        $part_name = $_POST['part_name'];
        $quantity = intval($_POST['quantity']);
        $reorder_level = intval($_POST['reorder_level']);
        $unit_price = floatval($_POST['unit_price']);
        
        $stmt = $conn->prepare("UPDATE inventory_parts SET part_name = ?, quantity = ?, reorder_level = ?, unit_price = ? WHERE part_id = ?");
        $stmt->bind_param('sidii', $part_name, $quantity, $reorder_level, $unit_price, $part_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Part updated successfully!";
        }
    }
    
    if ($_POST['action'] === 'adjust_quantity') {
        $part_id = intval($_POST['part_id']);
        $quantity_change = intval($_POST['quantity_change']);
        $notes = $_POST['notes'];
        
        
        $part = $conn->query("SELECT quantity FROM inventory_parts WHERE part_id = $part_id")->fetch_assoc();
        $new_quantity = $part['quantity'] + $quantity_change;
        
        
        $conn->query("UPDATE inventory_parts SET quantity = $new_quantity WHERE part_id = $part_id");
        
        
        $transaction_type = $quantity_change > 0 ? 'purchase' : 'usage';
        $conn->query("INSERT INTO inventory_transactions (part_id, transaction_type, quantity, notes, created_by, created_at)
                     VALUES ($part_id, '$transaction_type', ABS($quantity_change), '$notes', {$_SESSION['user_id']}, NOW())");
        
        $_SESSION['success'] = "Inventory adjusted successfully!";
    }
    
    if ($_POST['action'] === 'delete_part') {
        $part_id = intval($_POST['part_id']);
        $conn->query("DELETE FROM inventory_parts WHERE part_id = $part_id");
        $_SESSION['success'] = "Part deleted successfully!";
    }
}


$inventory_stats = $conn->query("
    SELECT 
        COUNT(*) as total_parts,
        SUM(CASE WHEN quantity < reorder_level THEN 1 ELSE 0 END) as low_stock_count,
        SUM(quantity * unit_price) as total_inventory_value
    FROM inventory_parts
    WHERE status = 'active'
")->fetch_assoc();


$parts = $conn->query("
    SELECT * FROM inventory_parts 
    WHERE status = 'active'
    ORDER BY part_name ASC
");


$low_stock = $conn->query("
    SELECT * FROM inventory_parts 
    WHERE status = 'active' AND quantity < reorder_level
    ORDER BY quantity ASC
");
?>

<style>
    .container { max-width: 1200px; margin: 0 auto; }
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .stat-card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-left: 4px solid 
    .stat-number { font-size: 32px; font-weight: bold; margin: 10px 0; }
    .stat-label { color: 
    .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .btn { padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; font-size: 13px; }
    .btn-primary { background: 
    .btn-primary:hover { background: 
    .btn-danger { background: 
    .btn-small { padding: 5px 10px; font-size: 12px; }
    .inventory-table { width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .inventory-table thead { background: 
    .inventory-table th, .inventory-table td { padding: 15px; text-align: left; border-bottom: 1px solid 
    .inventory-table tbody tr:hover { background: 
    .stock-warning { color: 
    .stock-good { color: 
    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); }
    .modal-content { background: white; margin: 5% auto; padding: 30px; border: 1px solid 
    .modal-close { color: 
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
    .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 8px; border: 1px solid 
    .form-full { grid-column: 1 / -1; }
    .alert { padding: 15px; margin-bottom: 20px; border-radius: 5px; }
    .alert-success { background: 
    .alert-warning { background: 
    .tabs { display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 2px solid 
    .tab { padding: 10px 20px; cursor: pointer; border-bottom: 3px solid transparent; }
    .tab.active { border-bottom-color: 
</style>

<div class="container">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    
    <div class="header-actions">
        <h2 style="margin: 0;"><i class="fas fa-warehouse"></i> Inventory Management</h2>
        <button class="btn btn-primary" onclick="openAddPartModal()">
            <i class="fas fa-plus"></i> Add New Part
        </button>
    </div>
    
    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card" style="border-left-color: 
            <div class="stat-label">Total Parts</div>
            <div class="stat-number" style="color: 
        </div>
        
        <div class="stat-card" style="border-left-color: 
            <div class="stat-label">Low Stock Items</div>
            <div class="stat-number" style="color: 
        </div>
        
        <div class="stat-card" style="border-left-color: 
            <div class="stat-label">Total Inventory Value</div>
            <div class="stat-number" style="color: 
        </div>
    </div>
    
    <!-- Tabs -->
    <div class="tabs">
        <div class="tab active" onclick="switchTab('all-parts')">All Parts</div>
        <div class="tab" onclick="switchTab('low-stock')">Low Stock</div>
        <div class="tab" onclick="switchTab('history')">Transaction History</div>
    </div>
    
    <!-- All Parts Tab -->
    <div id="all-parts" class="tab-content">
        <table class="inventory-table">
            <thead>
                <tr>
                    <th>Part Code</th>
                    <th>Part Name</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Reorder Level</th>
                    <th>Unit Price</th>
                    <th>Supplier</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($part = $parts->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($part['part_code']); ?></strong></td>
                        <td><?php echo htmlspecialchars($part['part_name']); ?></td>
                        <td><?php echo htmlspecialchars($part['category']); ?></td>
                        <td class="<?php echo ($part['quantity'] < $part['reorder_level']) ? 'stock-warning' : 'stock-good'; ?>">
                            <?php echo $part['quantity']; ?>
                        </td>
                        <td><?php echo $part['reorder_level']; ?></td>
                        <td>â‚±<?php echo number_format($part['unit_price'], 2); ?></td>
                        <td><?php echo htmlspecialchars($part['supplier'] ?? '-'); ?></td>
                        <td>
                            <button class="btn btn-primary btn-small" onclick="openAdjustModal(<?php echo $part['part_id']; ?>, <?php echo $part['quantity']; ?>)">
                                Adjust
                            </button>
                            <button class="btn btn-small" style="background: 
                                Edit
                            </button>
                            <button class="btn btn-danger btn-small" onclick="deletePart(<?php echo $part['part_id']; ?>)">
                                Delete
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Low Stock Tab -->
    <div id="low-stock" class="tab-content" style="display: none;">
        <div class="alert alert-warning">
            <strong>âš  Warning:</strong> The following items are below reorder level and should be replenished.
        </div>
        <table class="inventory-table">
            <thead>
                <tr>
                    <th>Part Code</th>
                    <th>Part Name</th>
                    <th>Current Stock</th>
                    <th>Reorder Level</th>
                    <th>Difference</th>
                    <th>Supplier</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($part = $low_stock->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($part['part_code']); ?></strong></td>
                        <td><?php echo htmlspecialchars($part['part_name']); ?></td>
                        <td class="stock-warning"><?php echo $part['quantity']; ?></td>
                        <td><?php echo $part['reorder_level']; ?></td>
                        <td class="stock-warning"><?php echo $part['reorder_level'] - $part['quantity']; ?> units</td>
                        <td><?php echo htmlspecialchars($part['supplier'] ?? '-'); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Part Modal -->
<div id="addPartModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal('addPartModal')">&times;</span>
        <h3>Add New Part</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add_part">
            
            <div class="form-grid">
                <div class="form-group">
                    <label>Part Code:</label>
                    <input type="text" name="part_code" required>
                </div>
                
                <div class="form-group">
                    <label>Part Name:</label>
                    <input type="text" name="part_name" required>
                </div>
                
                <div class="form-group">
                    <label>Category:</label>
                    <select name="category" required>
                        <option value="">Select Category</option>
                        <option value="Engine">Engine</option>
                        <option value="Brakes">Brakes</option>
                        <option value="Transmission">Transmission</option>
                        <option value="Electrical">Electrical</option>
                        <option value="Body">Body</option>
                        <option value="Suspension">Suspension</option>
                        <option value="Accessories">Accessories</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Unit Price (â‚±):</label>
                    <input type="number" name="unit_price" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label>Quantity:</label>
                    <input type="number" name="quantity" value="0" required>
                </div>
                
                <div class="form-group">
                    <label>Reorder Level:</label>
                    <input type="number" name="reorder_level" value="10" required>
                </div>
                
                <div class="form-group">
                    <label>Supplier:</label>
                    <input type="text" name="supplier">
                </div>
                
                <div class="form-group form-full">
                    <label>Description:</label>
                    <textarea name="description" rows="3"></textarea>
                </div>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary">Add Part</button>
                <button type="button" class="btn" style="background: 
            </div>
        </form>
    </div>
</div>

<!-- Adjust Quantity Modal -->
<div id="adjustModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal('adjustModal')">&times;</span>
        <h3>Adjust Inventory</h3>
        <form method="POST">
            <input type="hidden" name="action" value="adjust_quantity">
            <input type="hidden" name="part_id" id="partIdAdjust">
            
            <div class="form-group">
                <label>Current Quantity:</label>
                <input type="text" id="currentQty" readonly style="background: 
            </div>
            
            <div class="form-group">
                <label>Quantity Change (+ or -):</label>
                <input type="number" name="quantity_change" required placeholder="e.g., +5 or -3">
            </div>
            
            <div class="form-group">
                <label>Notes:</label>
                <textarea name="notes" rows="3" placeholder="Reason for adjustment..."></textarea>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary">Adjust</button>
                <button type="button" class="btn" style="background: 
            </div>
        </form>
    </div>
</div>

<script>
function openAddPartModal() {
    document.getElementById('addPartModal').style.display = 'block';
}

function openAdjustModal(partId, currentQty) {
    document.getElementById('partIdAdjust').value = partId;
    document.getElementById('currentQty').value = currentQty;
    document.getElementById('adjustModal').style.display = 'block';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function editPart(partId) {
    alert('Implement edit functionality');
}

function deletePart(partId) {
    if (confirm('Are you sure you want to delete this part?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="action" value="delete_part"><input type="hidden" name="part_id" value="' + partId + '">';
        document.body.appendChild(form);
        form.submit();
    }
}

function switchTab(tabName) {
    const tabs = document.querySelectorAll('.tab-content');
    tabs.forEach(tab => tab.style.display = 'none');
    document.getElementById(tabName).style.display = 'block';
    
    const tabButtons = document.querySelectorAll('.tab');
    tabButtons.forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
}

window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}
</script>

<?php include __DIR__ . '/includes/admin_layout_footer.php'; ?>

