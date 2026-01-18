<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /vehicare_db/index.php");
    exit;
}

include __DIR__ . '/../includes/adminHeader.php';

// Handle form submission for adding/editing clients
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $email = $conn->real_escape_string($_POST['email']);
    $address = $conn->real_escape_string($_POST['address']);
    
    if (isset($_POST['client_id']) && !empty($_POST['client_id'])) {
        // Update
        $client_id = intval($_POST['client_id']);
        $query = "UPDATE clients SET full_name='$full_name', phone='$phone', email='$email', address='$address' WHERE client_id=$client_id";
        if ($conn->query($query)) {
            $_SESSION['success'] = "Client updated successfully!";
        } else {
            $_SESSION['error'] = "Error updating client: " . $conn->error;
        }
    } else {
        // Insert
        $query = "INSERT INTO clients (full_name, phone, email, address) VALUES ('$full_name', '$phone', '$email', '$address')";
        if ($conn->query($query)) {
            $_SESSION['success'] = "Client added successfully!";
        } else {
            $_SESSION['error'] = "Error adding client: " . $conn->error;
        }
    }
    header("Location: /vehicare_db/admins/clients.php");
    exit;
}

// Fetch clients
$search = '';
if (isset($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $clientsQuery = $conn->query("SELECT * FROM clients WHERE full_name LIKE '%$search%' OR email LIKE '%$search%' OR phone LIKE '%$search%'");
} else {
    $clientsQuery = $conn->query("SELECT * FROM clients");
}
?>

<div class="admin-sidebar-shared">
  <div class="list-group">
    <a href="/vehicare_db/admins/dashboard.php" class="list-group-item">
      <i class="fas fa-chart-line"></i> Dashboard
    </a>
    <a href="/vehicare_db/admins/clients.php" class="list-group-item active">
      <i class="fas fa-users"></i> Clients
    </a>
    <a href="/vehicare_db/admins/vehicles.php" class="list-group-item">
      <i class="fas fa-car"></i> Vehicles
    </a>
    <a href="/vehicare_db/admins/appointments.php" class="list-group-item">
      <i class="fas fa-calendar"></i> Appointments
    </a>
    <a href="/vehicare_db/admins/services.php" class="list-group-item">
      <i class="fas fa-cogs"></i> Services
    </a>
    <a href="/vehicare_db/admins/parts.php" class="list-group-item">
      <i class="fas fa-box"></i> Parts & Inventory
    </a>
    <a href="/vehicare_db/admins/staff.php" class="list-group-item">
      <i class="fas fa-people-group"></i> Staff
    </a>
    <a href="/vehicare_db/admins/payments.php" class="list-group-item">
      <i class="fas fa-money-bill"></i> Payments
    </a>
  </div>
</div>

<div class="admin-main-content">
  <h1 style="color: #1a3a52; margin-bottom: 20px;">Manage Clients</h1>
  
  <?php include __DIR__ . '/../includes/alert.php'; ?>
  
  <div style="display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap; align-items: center;">
    <button class="btn btn-primary" onclick="openAddModal()"><i class="fas fa-plus"></i> Add New Client</button>
    <form style="display: flex; gap: 10px; flex: 1; min-width: 250px;">
      <input type="text" name="search" placeholder="Search by name, email, or phone..." class="form-control" value="<?php echo htmlspecialchars($search); ?>" style="flex: 1;">
      <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
    </form>
  </div>

  <div class="table-container">
    <div style="overflow-x: auto;">
      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Phone</th>
            <th>Email</th>
            <th>Address</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($clientsQuery && $clientsQuery->num_rows > 0) {
            while ($client = $clientsQuery->fetch_assoc()) {
              echo "<tr>
                <td>#{$client['client_id']}</td>
                <td>{$client['full_name']}</td>
                <td>{$client['phone']}</td>
                <td>{$client['email']}</td>
                <td>" . substr($client['address'], 0, 40) . "...</td>
                <td>
                  <div class='action-buttons'>
                    <button class='btn btn-primary btn-sm' onclick=\"editClient({$client['client_id']}, '{$client['full_name']}', '{$client['phone']}', '{$client['email']}', '{$client['address']}')\">Edit</button>
                    <a href='/vehicare_db/admins/delete.php?type=client&id={$client['client_id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure?\")'>Delete</a>
                  </div>
                </td>
              </tr>";
            }
          } else {
            echo "<tr><td colspan='6' style='text-align: center; padding: 20px;'>No clients found</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- Modal for adding/editing client -->
<div class="modal fade" id="clientModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Add New Client</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <div class="modal-body">
          <input type="hidden" name="client_id" id="client_id" value="">
          <div class="form-group">
            <label for="full_name">Full Name *</label>
            <input type="text" id="full_name" name="full_name" class="form-control" required>
          </div>
          <div class="form-group">
            <label for="phone">Phone *</label>
            <input type="tel" id="phone" name="phone" class="form-control" required>
          </div>
          <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" id="email" name="email" class="form-control" required>
          </div>
          <div class="form-group">
            <label for="address">Address</label>
            <textarea id="address" name="address" class="form-control" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Client</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openAddModal() {
  document.getElementById('client_id').value = '';
  document.getElementById('full_name').value = '';
  document.getElementById('phone').value = '';
  document.getElementById('email').value = '';
  document.getElementById('address').value = '';
  document.getElementById('modalTitle').textContent = 'Add New Client';
  new bootstrap.Modal(document.getElementById('clientModal')).show();
}

function editClient(id, name, phone, email, address) {
  document.getElementById('client_id').value = id;
  document.getElementById('full_name').value = name;
  document.getElementById('phone').value = phone;
  document.getElementById('email').value = email;
  document.getElementById('address').value = address;
  document.getElementById('modalTitle').textContent = 'Edit Client';
  new bootstrap.Modal(document.getElementById('clientModal')).show();
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
