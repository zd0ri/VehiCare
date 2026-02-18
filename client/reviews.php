<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

// Check if client is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: /vehicare_db/login.php");
    exit;
}

$client_id = $_SESSION['user_id'];

// Handle review submission
$success_message = '';
$error_message = '';

if ($_POST) {
    $appointment_id = $_POST['appointment_id'] ?? 0;
    $rating = intval($_POST['rating'] ?? 0);
    $review_text = trim($_POST['review_text'] ?? '');
    $service_quality = intval($_POST['service_quality'] ?? 0);
    $staff_friendliness = intval($_POST['staff_friendliness'] ?? 0);
    $timeliness = intval($_POST['timeliness'] ?? 0);
    $value_for_money = intval($_POST['value_for_money'] ?? 0);
    $recommend = $_POST['recommend'] ?? 'no';

    // Validation
    if ($rating < 1 || $rating > 5) {
        $error_message = "Please provide a valid rating between 1 and 5 stars.";
    } elseif (empty($review_text) || strlen($review_text) < 10) {
        $error_message = "Please write a review with at least 10 characters.";
    } elseif ($appointment_id <= 0) {
        $error_message = "Please select a valid appointment.";
    } else {
        // Check if review already exists
        $check_review = "SELECT review_id FROM reviews WHERE appointment_id = ? AND client_id = ?";
        $stmt = $conn->prepare($check_review);
        $stmt->bind_param("ii", $appointment_id, $client_id);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            $error_message = "You have already submitted a review for this appointment.";
        } else {
            // Insert new review
            $insert_review = "
                INSERT INTO reviews 
                (client_id, appointment_id, rating, review_text, service_quality, 
                 staff_friendliness, timeliness, value_for_money, recommend, review_date, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'active')
            ";
            
            $stmt = $conn->prepare($insert_review);
            $stmt->bind_param("iiisiiiss", 
                $client_id, $appointment_id, $rating, $review_text, 
                $service_quality, $staff_friendliness, $timeliness, $value_for_money, $recommend
            );
            
            if ($stmt->execute()) {
                // Add to client activity log
                $activity_description = "Submitted review for appointment #" . str_pad($appointment_id, 6, '0', STR_PAD_LEFT);
                $log_activity = "INSERT INTO client_activity_logs (client_id, activity_type, activity_description) VALUES (?, 'review', ?)";
                $stmt2 = $conn->prepare($log_activity);
                $stmt2->bind_param("is", $client_id, $activity_description);
                $stmt2->execute();
                
                $success_message = "Thank you for your review! It has been submitted successfully.";
            } else {
                $error_message = "Failed to submit review. Please try again.";
            }
        }
    }
}

// Get completed appointments that can be reviewed
$appointments_query = "
    SELECT a.appointment_id, a.appointment_date, a.appointment_time, a.status,
           s.service_name, v.plate_number, v.car_brand, v.car_model,
           r.review_id
    FROM appointments a
    LEFT JOIN services s ON a.service_id = s.service_id
    LEFT JOIN vehicles v ON a.vehicle_id = v.vehicle_id
    LEFT JOIN reviews r ON a.appointment_id = r.appointment_id AND r.client_id = a.client_id
    WHERE a.client_id = ? AND a.status = 'completed'
    ORDER BY a.appointment_date DESC
    LIMIT 10
";

$stmt = $conn->prepare($appointments_query);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$appointments = $stmt->get_result();

// Get client's reviews with pagination
$page = $_GET['page'] ?? 1;
$per_page = 5;
$offset = ($page - 1) * $per_page;

$reviews_query = "
    SELECT r.*, a.appointment_date, s.service_name, v.plate_number, v.car_brand, v.car_model
    FROM reviews r
    LEFT JOIN appointments a ON r.appointment_id = a.appointment_id
    LEFT JOIN services s ON a.service_id = s.service_id
    LEFT JOIN vehicles v ON a.vehicle_id = v.vehicle_id
    WHERE r.client_id = ?
    ORDER BY r.review_date DESC
    LIMIT $per_page OFFSET $offset
";

$stmt = $conn->prepare($reviews_query);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$reviews = $stmt->get_result();

// Get total reviews count
$count_query = "SELECT COUNT(*) as total FROM reviews WHERE client_id = ?";
$stmt = $conn->prepare($count_query);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$total_reviews = $stmt->get_result()->fetch_assoc()['total'];

$total_pages = ceil($total_reviews / $per_page);

// Get review statistics
$stats = [];
$stats['total_reviews'] = $total_reviews;
$stats['avg_rating'] = $conn->query("SELECT AVG(rating) as avg FROM reviews WHERE client_id = $client_id")->fetch_assoc()['avg'] ?? 0;
$stats['last_review'] = $conn->query("SELECT MAX(review_date) as last_date FROM reviews WHERE client_id = $client_id")->fetch_assoc()['last_date'];
$stats['pending_reviews'] = $conn->query("SELECT COUNT(*) as count FROM appointments a LEFT JOIN reviews r ON a.appointment_id = r.appointment_id WHERE a.client_id = $client_id AND a.status = 'completed' AND r.review_id IS NULL")->fetch_assoc()['count'] ?? 0;

include __DIR__ . '/../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews & Feedback - VehiCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #f8f9fa;
        }

        .reviews-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .page-header {
            background: linear-gradient(135deg, #fd79a8 0%, #e84393 100%);
            color: white;
            padding: 30px 40px;
            border-radius: 20px;
            margin-bottom: 30px;
        }

        .page-header h1 {
            margin: 0 0 10px 0;
            font-weight: 700;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient);
        }

        .stat-card .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.5em;
            color: white;
            background: var(--gradient);
        }

        .stat-card .stat-value {
            font-size: 2em;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .stat-card .stat-label {
            color: #7f8c8d;
            font-weight: 500;
        }

        .stat-card:nth-child(1) {
            --gradient: linear-gradient(135deg, #fd79a8 0%, #e84393 100%);
        }

        .stat-card:nth-child(2) {
            --gradient: linear-gradient(135deg, #fdcb6e 0%, #e17055 100%);
        }

        .stat-card:nth-child(3) {
            --gradient: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
        }

        .stat-card:nth-child(4) {
            --gradient: linear-gradient(135deg, #00b894 0%, #00a085 100%);
        }

        .review-form-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .section-title {
            font-size: 1.4em;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .star-rating {
            display: flex;
            gap: 5px;
            margin-bottom: 20px;
        }

        .star {
            font-size: 2em;
            color: #ddd;
            cursor: pointer;
            transition: color 0.2s ease;
        }

        .star:hover,
        .star.active {
            color: #ffd700;
        }

        .rating-section {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
        }

        .rating-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .rating-item:last-child {
            margin-bottom: 0;
        }

        .rating-label {
            font-weight: 600;
            color: #2c3e50;
        }

        .rating-stars {
            display: flex;
            gap: 3px;
        }

        .rating-stars .star {
            font-size: 1.2em;
        }

        .review-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .review-rating {
            display: flex;
            gap: 3px;
        }

        .review-rating .star {
            font-size: 1.1em;
            color: #ffd700;
        }

        .review-date {
            color: #7f8c8d;
            font-size: 0.9em;
        }

        .review-text {
            color: #2c3e50;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .review-details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .detail-row:last-child {
            margin-bottom: 0;
        }

        .appointment-info {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }

        .appointment-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .appointment-content h6 {
            margin: 0;
            font-weight: 600;
            color: #2c3e50;
        }

        .appointment-content p {
            margin: 0;
            color: #7f8c8d;
            font-size: 0.9em;
        }

        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            font-weight: 500;
        }

        .form-control:focus {
            border-color: #fd79a8;
            box-shadow: 0 0 0 0.2rem rgba(253, 121, 168, 0.25);
        }

        .btn {
            border-radius: 25px;
            font-weight: 600;
            padding: 12px 30px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #fd79a8 0%, #e84393 100%);
            border: none;
        }

        .recommend-options {
            display: flex;
            gap: 15px;
            margin-top: 10px;
        }

        .recommend-option {
            flex: 1;
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .recommend-option input[type="radio"] {
            display: none;
        }

        .recommend-option:hover {
            border-color: #fd79a8;
        }

        .recommend-option.selected {
            background: linear-gradient(135deg, #fd79a8 0%, #e84393 100%);
            color: white;
            border-color: #fd79a8;
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .no-reviews {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .no-reviews i {
            font-size: 4em;
            color: #bdc3c7;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .page-header {
                padding: 20px;
                text-align: center;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .review-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .rating-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .recommend-options {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="reviews-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-star me-3"></i>Reviews & Feedback</h1>
            <p class="mb-0">Share your experience and help us improve our services</p>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-comment-dots"></i>
                </div>
                <div class="stat-value"><?php echo $stats['total_reviews']; ?></div>
                <div class="stat-label">Total Reviews</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['avg_rating'], 1); ?></div>
                <div class="stat-label">Average Rating</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-calendar"></i>
                </div>
                <div class="stat-value"><?php echo $stats['last_review'] ? date('M j', strtotime($stats['last_review'])) : 'N/A'; ?></div>
                <div class="stat-label">Last Review</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-value"><?php echo $stats['pending_reviews']; ?></div>
                <div class="stat-label">Pending Reviews</div>
            </div>
        </div>

        <!-- Error/Success Messages -->
        <?php if ($error_message): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="alert alert-success" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <!-- Write Review Form -->
        <?php if ($appointments && $appointments->num_rows > 0): ?>
            <div class="review-form-card">
                <h4 class="section-title">
                    <i class="fas fa-edit"></i>
                    Write a Review
                </h4>

                <form method="POST" id="reviewForm">
                    <!-- Select Appointment -->
                    <div class="mb-4">
                        <label for="appointment_id" class="form-label">Select Appointment to Review *</label>
                        <select name="appointment_id" id="appointment_id" class="form-select" required>
                            <option value="">Choose an appointment...</option>
                            <?php 
                            $appointments->data_seek(0);
                            while ($appointment = $appointments->fetch_assoc()): 
                                if (!$appointment['review_id']): // Only show appointments without reviews
                            ?>
                                <option value="<?php echo $appointment['appointment_id']; ?>">
                                    <?php echo date('M j, Y', strtotime($appointment['appointment_date'])); ?> - 
                                    <?php echo htmlspecialchars($appointment['service_name']); ?> 
                                    (<?php echo htmlspecialchars($appointment['plate_number']); ?>)
                                </option>
                            <?php 
                                endif;
                            endwhile; 
                            ?>
                        </select>
                    </div>

                    <!-- Overall Rating -->
                    <div class="mb-4">
                        <label class="form-label">Overall Rating *</label>
                        <div class="star-rating" id="overallRating">
                            <i class="fas fa-star star" data-rating="1"></i>
                            <i class="fas fa-star star" data-rating="2"></i>
                            <i class="fas fa-star star" data-rating="3"></i>
                            <i class="fas fa-star star" data-rating="4"></i>
                            <i class="fas fa-star star" data-rating="5"></i>
                        </div>
                        <input type="hidden" name="rating" id="rating" value="0" required>
                        <small class="text-muted">Click on the stars to rate your experience</small>
                    </div>

                    <!-- Detailed Ratings -->
                    <div class="rating-section">
                        <h6 class="mb-3">Rate Specific Aspects</h6>
                        
                        <div class="rating-item">
                            <span class="rating-label">Service Quality</span>
                            <div class="rating-stars" data-field="service_quality">
                                <i class="fas fa-star star" data-rating="1"></i>
                                <i class="fas fa-star star" data-rating="2"></i>
                                <i class="fas fa-star star" data-rating="3"></i>
                                <i class="fas fa-star star" data-rating="4"></i>
                                <i class="fas fa-star star" data-rating="5"></i>
                            </div>
                            <input type="hidden" name="service_quality" value="0">
                        </div>

                        <div class="rating-item">
                            <span class="rating-label">Staff Friendliness</span>
                            <div class="rating-stars" data-field="staff_friendliness">
                                <i class="fas fa-star star" data-rating="1"></i>
                                <i class="fas fa-star star" data-rating="2"></i>
                                <i class="fas fa-star star" data-rating="3"></i>
                                <i class="fas fa-star star" data-rating="4"></i>
                                <i class="fas fa-star star" data-rating="5"></i>
                            </div>
                            <input type="hidden" name="staff_friendliness" value="0">
                        </div>

                        <div class="rating-item">
                            <span class="rating-label">Timeliness</span>
                            <div class="rating-stars" data-field="timeliness">
                                <i class="fas fa-star star" data-rating="1"></i>
                                <i class="fas fa-star star" data-rating="2"></i>
                                <i class="fas fa-star star" data-rating="3"></i>
                                <i class="fas fa-star star" data-rating="4"></i>
                                <i class="fas fa-star star" data-rating="5"></i>
                            </div>
                            <input type="hidden" name="timeliness" value="0">
                        </div>

                        <div class="rating-item">
                            <span class="rating-label">Value for Money</span>
                            <div class="rating-stars" data-field="value_for_money">
                                <i class="fas fa-star star" data-rating="1"></i>
                                <i class="fas fa-star star" data-rating="2"></i>
                                <i class="fas fa-star star" data-rating="3"></i>
                                <i class="fas fa-star star" data-rating="4"></i>
                                <i class="fas fa-star star" data-rating="5"></i>
                            </div>
                            <input type="hidden" name="value_for_money" value="0">
                        </div>
                    </div>

                    <!-- Review Text -->
                    <div class="mb-4">
                        <label for="review_text" class="form-label">Your Review *</label>
                        <textarea class="form-control" 
                                  id="review_text" 
                                  name="review_text" 
                                  rows="5" 
                                  placeholder="Please share your experience with our service..."
                                  required
                                  minlength="10"></textarea>
                        <small class="text-muted">Minimum 10 characters required</small>
                    </div>

                    <!-- Recommendation -->
                    <div class="mb-4">
                        <label class="form-label">Would you recommend our service to others?</label>
                        <div class="recommend-options">
                            <label class="recommend-option" for="recommend_yes">
                                <input type="radio" name="recommend" value="yes" id="recommend_yes" required>
                                <i class="fas fa-thumbs-up mb-2"></i>
                                <div>Yes, I would recommend</div>
                            </label>
                            <label class="recommend-option" for="recommend_no">
                                <input type="radio" name="recommend" value="no" id="recommend_no" required>
                                <i class="fas fa-thumbs-down mb-2"></i>
                                <div>No, I wouldn't recommend</div>
                            </label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-2"></i>
                        Submit Review
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <!-- My Reviews -->
        <div class="review-form-card">
            <h4 class="section-title">
                <i class="fas fa-list"></i>
                My Reviews
            </h4>

            <?php if ($reviews && $reviews->num_rows > 0): ?>
                <?php while ($review = $reviews->fetch_assoc()): ?>
                    <div class="review-card">
                        <div class="review-header">
                            <div class="review-rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star star <?php echo $i <= $review['rating'] ? 'active' : ''; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <div class="review-date">
                                <?php echo date('M j, Y', strtotime($review['review_date'])); ?>
                            </div>
                        </div>

                        <div class="appointment-info">
                            <div class="appointment-icon">
                                <i class="fas fa-wrench"></i>
                            </div>
                            <div class="appointment-content">
                                <h6><?php echo htmlspecialchars($review['service_name']); ?></h6>
                                <p><?php echo htmlspecialchars($review['plate_number']); ?> - <?php echo date('M j, Y', strtotime($review['appointment_date'])); ?></p>
                            </div>
                        </div>

                        <div class="review-text">
                            <?php echo htmlspecialchars($review['review_text']); ?>
                        </div>

                        <div class="review-details">
                            <div class="detail-row">
                                <span>Service Quality:</span>
                                <div class="rating-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star star <?php echo $i <= $review['service_quality'] ? 'active' : ''; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="detail-row">
                                <span>Staff Friendliness:</span>
                                <div class="rating-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star star <?php echo $i <= $review['staff_friendliness'] ? 'active' : ''; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="detail-row">
                                <span>Timeliness:</span>
                                <div class="rating-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star star <?php echo $i <= $review['timeliness'] ? 'active' : ''; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="detail-row">
                                <span>Value for Money:</span>
                                <div class="rating-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star star <?php echo $i <= $review['value_for_money'] ? 'active' : ''; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <span class="badge <?php echo $review['recommend'] === 'yes' ? 'bg-success' : 'bg-warning'; ?>">
                                <i class="fas fa-thumbs-<?php echo $review['recommend'] === 'yes' ? 'up' : 'down'; ?> me-1"></i>
                                <?php echo $review['recommend'] === 'yes' ? 'Recommended' : 'Not Recommended'; ?>
                            </span>
                        </div>
                    </div>
                <?php endwhile; ?>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav>
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page-1; ?>">Previous</a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i === (int)$page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page+1; ?>">Next</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php else: ?>
                <!-- No Reviews -->
                <div class="no-reviews">
                    <i class="fas fa-comment-slash"></i>
                    <h3>No Reviews Yet</h3>
                    <p>You haven't written any reviews yet. Complete a service appointment to leave a review.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Back to Dashboard -->
        <div class="text-center mt-4">
            <a href="/vehicare_db/client/dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Star rating functionality
        function initializeStarRating(container, hiddenField) {
            const stars = container.querySelectorAll('.star');
            let currentRating = 0;

            stars.forEach((star, index) => {
                star.addEventListener('click', () => {
                    currentRating = index + 1;
                    updateStars();
                    if (hiddenField) {
                        document.querySelector(`input[name="${hiddenField}"]`).value = currentRating;
                    } else {
                        document.getElementById('rating').value = currentRating;
                    }
                });

                star.addEventListener('mouseover', () => {
                    highlightStars(index + 1);
                });
            });

            container.addEventListener('mouseleave', () => {
                highlightStars(currentRating);
            });

            function updateStars() {
                highlightStars(currentRating);
            }

            function highlightStars(rating) {
                stars.forEach((star, index) => {
                    if (index < rating) {
                        star.classList.add('active');
                    } else {
                        star.classList.remove('active');
                    }
                });
            }
        }

        // Initialize all rating systems
        document.addEventListener('DOMContentLoaded', () => {
            // Overall rating
            initializeStarRating(document.getElementById('overallRating'));

            // Detailed ratings
            document.querySelectorAll('.rating-stars').forEach(container => {
                const field = container.getAttribute('data-field');
                initializeStarRating(container, field);
            });

            // Recommendation options
            document.querySelectorAll('input[name="recommend"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    document.querySelectorAll('.recommend-option').forEach(option => {
                        option.classList.remove('selected');
                    });
                    this.closest('.recommend-option').classList.add('selected');
                });
            });

            // Form validation
            document.getElementById('reviewForm')?.addEventListener('submit', function(e) {
                const rating = parseInt(document.getElementById('rating').value);
                const reviewText = document.getElementById('review_text').value.trim();

                if (rating === 0) {
                    e.preventDefault();
                    alert('Please provide an overall rating.');
                    return;
                }

                if (reviewText.length < 10) {
                    e.preventDefault();
                    alert('Please write a review with at least 10 characters.');
                    return;
                }
            });
        });
    </script>
</body>
</html>