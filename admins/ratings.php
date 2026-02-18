<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /vehicare_db/login.php");
    exit;
}

$ratings = $conn->query("
    SELECT r.*, s.full_name as staff_name, u.full_name as client_name
    FROM ratings r
    JOIN staff s ON r.staff_id = s.staff_id
    JOIN users u ON r.client_id = u.user_id
    ORDER BY r.rating_date DESC
");

$page_title = "Ratings";
$page_icon = "fas fa-star";
include __DIR__ . '/includes/admin_layout_header.php';
?>

<!-- Page Content -->
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Customer Ratings & Reviews</h3>
    </div>
    
    <div class="table-responsive">
        <table class="table table-striped data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Client</th>
                    <th>Staff Member</th>
                    <th>Rating</th>
                    <th>Feedback</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($ratings && $ratings->num_rows > 0): ?>
                    <?php while($rating = $ratings->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $rating['rating_id']; ?></td>
                        <td><?php echo htmlspecialchars($rating['client_name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($rating['staff_name'] ?? 'N/A'); ?></td>
                        <td>
                            <div class="rating-stars">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star text-<?php echo $i <= ($rating['rating'] ?? 0) ? 'warning' : 'muted'; ?>"></i>
                                <?php endfor; ?>
                                (<?php echo $rating['rating'] ?? 0; ?>/5)
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars(substr($rating['feedback'] ?? 'No feedback', 0, 50)); ?>...</td>
                        <td><?php echo htmlspecialchars($rating['rating_date'] ?? 'N/A'); ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-info" onclick="viewRating(<?php echo $rating['rating_id']; ?>)">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteRating(<?php echo $rating['rating_id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No ratings found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function viewRating(id) {
    alert('View rating details for ID: ' + id);
}

function deleteRating(id) {
    if (confirm('Are you sure you want to delete this rating?')) {
        alert('Delete rating ' + id);
    }
}
</script>

<?php include __DIR__ . '/includes/admin_layout_footer.php'; ?>
