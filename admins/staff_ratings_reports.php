<?php

session_start();
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /vehicare_db/login.php");
    exit;
}

$page_title = 'Staff Ratings & Reports';
$page_icon = 'fas fa-star';
include __DIR__ . '/includes/admin_layout_header.php';


$staff_stats = $conn->query("
    SELECT 
        u.user_id,
        u.full_name,
        COUNT(sr.rating_id) as total_ratings,
        ROUND(AVG(sr.rating), 2) as average_rating,
        MIN(sr.rating) as lowest_rating,
        MAX(sr.rating) as highest_rating
    FROM users u
    LEFT JOIN staff_ratings sr ON u.user_id = sr.staff_id
    WHERE u.role = 'staff' AND u.status = 'active'
    GROUP BY u.user_id, u.full_name
    ORDER BY average_rating DESC
");
?>

<style>
    .container { max-width: 1200px; margin: 0 auto; }
    .header-section { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .btn { padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; font-size: 13px; }
    .btn-primary { background: 
    .btn-primary:hover { background: 
    .btn-secondary { background: 
    .btn-secondary:hover { background: 
    .staff-card { background: white; padding: 20px; margin-bottom: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .rating-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
    .staff-name { font-size: 18px; font-weight: bold; color: 
    .rating-display { display: flex; align-items: center; gap: 10px; }
    .stars { font-size: 20px; color: 
    .rating-score { font-size: 24px; font-weight: bold; color: 
    .rating-count { color: 
    .stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 15px; }
    .stat-box { background: 
    .stat-label { font-size: 12px; color: 
    .stat-value { font-size: 20px; font-weight: bold; color: 
    .reviews-section { margin-top: 15px; border-top: 1px solid 
    .review-item { background: 
    .review-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
    .review-client { font-weight: bold; }
    .review-rating { color: 
    .review-text { color: 
    .review-date { font-size: 12px; color: 
    .no-reviews { color: 
    .chart-container { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px; }
    .tabs { display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 2px solid 
    .tab { padding: 10px 20px; cursor: pointer; border-bottom: 3px solid transparent; }
    .tab.active { border-bottom-color: 
</style>

<div class="container">
    <div class="header-section">
        <h2 style="margin: 0;"><i class="fas fa-star"></i> Staff Ratings & Reports</h2>
        <div>
            <button class="btn btn-secondary" onclick="exportReport()">
                <i class="fas fa-download"></i> Export Report
            </button>
        </div>
    </div>
    
    <!-- Tabs -->
    <div class="tabs">
        <div class="tab active" onclick="switchTab('overview')">Overview</div>
        <div class="tab" onclick="switchTab('detailed')">Detailed Ratings</div>
        <div class="tab" onclick="switchTab('performance')">Performance Analysis</div>
    </div>
    
    <!-- Overview Tab -->
    <div id="overview" class="tab-content">
        <?php 
        $staff_stats->data_seek(0); 
        while ($staff = $staff_stats->fetch_assoc()): 
        ?>
            <div class="staff-card">
                <div class="rating-header">
                    <div class="staff-name"><?php echo htmlspecialchars($staff['full_name']); ?></div>
                    <div class="rating-display">
                        <div class="stars">
                            <?php 
                            $rating = round($staff['average_rating'] ?? 0);
                            for ($i = 0; $i < 5; $i++) {
                                echo ($i < $rating) ? 'â˜…' : 'â˜†';
                            }
                            ?>
                        </div>
                        <div class="rating-score"><?php echo number_format($staff['average_rating'] ?? 0, 1); ?>/5</div>
                        <div class="rating-count"><?php echo $staff['total_ratings']; ?> ratings</div>
                    </div>
                </div>
                
                <div class="stats-row">
                    <div class="stat-box">
                        <div class="stat-label">Total Ratings</div>
                        <div class="stat-value"><?php echo $staff['total_ratings']; ?></div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-label">Average Rating</div>
                        <div class="stat-value"><?php echo number_format($staff['average_rating'] ?? 0, 1); ?></div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-label">Highest Rating</div>
                        <div class="stat-value"><?php echo $staff['highest_rating'] ?? '-'; ?></div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-label">Lowest Rating</div>
                        <div class="stat-value"><?php echo $staff['lowest_rating'] ?? '-'; ?></div>
                    </div>
                </div>
                
                <div class="reviews-section">
                    <strong>Recent Reviews:</strong>
                    <?php
                    $reviews = $conn->query("
                        SELECT sr.*, u.full_name as client_name
                        FROM staff_ratings sr
                        JOIN users u ON sr.client_id = u.user_id
                        WHERE sr.staff_id = {$staff['user_id']}
                        ORDER BY sr.rating_date DESC
                        LIMIT 5
                    ");
                    
                    if ($reviews->num_rows > 0) {
                        while ($review = $reviews->fetch_assoc()) {
                            echo '
                            <div class="review-item">
                                <div class="review-header">
                                    <span class="review-client">' . htmlspecialchars($review['client_name']) . '</span>
                                    <span class="review-rating">';
                            for ($i = 0; $i < 5; $i++) {
                                echo ($i < $review['rating']) ? 'â˜…' : 'â˜†';
                            }
                            echo '</span>
                                </div>';
                            if ($review['review_text']) {
                                echo '<div class="review-text">' . htmlspecialchars($review['review_text']) . '</div>';
                            }
                            echo '<div class="review-date">' . date('M d, Y H:i', strtotime($review['rating_date'])) . '</div>
                            </div>';
                        }
                    } else {
                        echo '<div class="no-reviews">No reviews yet</div>';
                    }
                    ?>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
    
    <!-- Detailed Ratings Tab -->
    <div id="detailed" class="tab-content" style="display: none;">
        <div class="chart-container">
            <h3>All Staff Ratings</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: 
                        <th style="padding: 12px; text-align: left; border-bottom: 1px solid 
                        <th style="padding: 12px; text-align: left; border-bottom: 1px solid 
                        <th style="padding: 12px; text-align: left; border-bottom: 1px solid 
                        <th style="padding: 12px; text-align: left; border-bottom: 1px solid 
                        <th style="padding: 12px; text-align: left; border-bottom: 1px solid 
                        <th style="padding: 12px; text-align: left; border-bottom: 1px solid 
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $staff_stats->data_seek(0);
                    while ($staff = $staff_stats->fetch_assoc()):
                    ?>
                        <tr style="border-bottom: 1px solid 
                            <td style="padding: 12px;"><?php echo htmlspecialchars($staff['full_name']); ?></td>
                            <td style="padding: 12px;"><?php echo $staff['total_ratings']; ?></td>
                            <td style="padding: 12px; font-weight: bold;">
                                <?php 
                                $rating = round($staff['average_rating'] ?? 0);
                                for ($i = 0; $i < 5; $i++) {
                                    echo ($i < $rating) ? 'â˜…' : 'â˜†';
                                }
                                echo ' ' . number_format($staff['average_rating'] ?? 0, 1);
                                ?>
                            </td>
                            <td style="padding: 12px;"><?php echo $staff['highest_rating'] ?? '-'; ?></td>
                            <td style="padding: 12px;"><?php echo $staff['lowest_rating'] ?? '-'; ?></td>
                            <td style="padding: 12px;">
                                <?php
                                
                                $dist = $conn->query("
                                    SELECT rating, COUNT(*) as count
                                    FROM staff_ratings
                                    WHERE staff_id = {$staff['user_id']}
                                    GROUP BY rating
                                ");
                                
                                $distribution = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
                                while ($d = $dist->fetch_assoc()) {
                                    $distribution[$d['rating']] = $d['count'];
                                }
                                
                                echo '5â˜…: ' . $distribution[5] . ', 4â˜…: ' . $distribution[4] . ', 3â˜…: ' . $distribution[3];
                                ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Performance Analysis Tab -->
    <div id="performance" class="tab-content" style="display: none;">
        <div class="chart-container">
            <h3>Performance Insights</h3>
            <div style="background: 
                <p><strong>Top Performers:</strong></p>
                <ul>
                    <?php
                    $staff_stats->data_seek(0);
                    $count = 0;
                    while ($staff = $staff_stats->fetch_assoc() && $count < 5):
                        $count++;
                    ?>
                        <li>
                            <?php echo htmlspecialchars($staff['full_name']); ?> 
                            - Average Rating: <strong><?php echo number_format($staff['average_rating'] ?? 0, 1); ?>/5</strong>
                            (<?php echo $staff['total_ratings']; ?> ratings)
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>
            
            <div style="background: 
                <p><strong>âš  Needs Improvement:</strong></p>
                <ul>
                    <?php
                    $low_rated = $conn->query("
                        SELECT 
                            u.user_id,
                            u.full_name,
                            COUNT(sr.rating_id) as total_ratings,
                            ROUND(AVG(sr.rating), 2) as average_rating
                        FROM users u
                        LEFT JOIN staff_ratings sr ON u.user_id = sr.staff_id
                        WHERE u.role = 'staff' AND u.status = 'active'
                        GROUP BY u.user_id, u.full_name
                        HAVING average_rating < 3.5
                        ORDER BY average_rating ASC
                    ");
                    
                    while ($staff = $low_rated->fetch_assoc()) {
                        echo '<li>' . htmlspecialchars($staff['full_name']) . ' - Average: ' . $staff['average_rating'] . '/5</li>';
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
function switchTab(tabName) {
    const tabs = document.querySelectorAll('.tab-content');
    tabs.forEach(tab => tab.style.display = 'none');
    document.getElementById(tabName).style.display = 'block';
    
    const tabButtons = document.querySelectorAll('.tab');
    tabButtons.forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
}

function exportReport() {
    alert('Implement export functionality - generate CSV/PDF report');
}
</script>

<?php include __DIR__ . '/includes/admin_layout_footer.php'; ?>

