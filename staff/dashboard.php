<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

// Check if staff is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: /vehicare_db/login.php");
    exit;
}

include __DIR__ . '/../includes/header.php';
?>

<div style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
    <div style="background: linear-gradient(135deg, #1a3a52 0%, #2d5a7b 100%); color: white; padding: 40px; border-radius: 12px; margin-bottom: 30px;">
        <h1 style="margin: 0 0 10px 0;">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h1>
        <p style="margin: 0; opacity: 0.9;">Staff Dashboard</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-left: 4px solid #ffc107;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <p style="margin: 0; color: #666; font-size: 13px; text-transform: uppercase; font-weight: 600;">Today's Tasks</p>
                    <h3 style="margin: 10px 0 0 0; color: #1a3a52; font-size: 28px;">0</h3>
                </div>
                <i class="fas fa-tasks" style="font-size: 40px; color: #ffc107; opacity: 0.3;"></i>
            </div>
        </div>

        <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-left: 4px solid #27ae60;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <p style="margin: 0; color: #666; font-size: 13px; text-transform: uppercase; font-weight: 600;">Completed</p>
                    <h3 style="margin: 10px 0 0 0; color: #1a3a52; font-size: 28px;">0</h3>
                </div>
                <i class="fas fa-check-circle" style="font-size: 40px; color: #27ae60; opacity: 0.3;"></i>
            </div>
        </div>

        <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-left: 4px solid #3498db;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <p style="margin: 0; color: #666; font-size: 13px; text-transform: uppercase; font-weight: 600;">Rating</p>
                    <h3 style="margin: 10px 0 0 0; color: #1a3a52; font-size: 28px;">0/5</h3>
                </div>
                <i class="fas fa-star" style="font-size: 40px; color: #3498db; opacity: 0.3;"></i>
            </div>
        </div>
    </div>

    <div style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
        <h2 style="margin: 0 0 20px 0; color: #1a3a52;">Quick Actions</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <a href="#" class="btn" style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); color: white; padding: 15px; border-radius: 8px; text-decoration: none; font-weight: 600; text-align: center; transition: all 0.3s ease;">
                <i class="fas fa-list"></i> My Tasks
            </a>
            <a href="#" class="btn" style="background: #2d5a7b; color: white; padding: 15px; border-radius: 8px; text-decoration: none; font-weight: 600; text-align: center; transition: all 0.3s ease;">
                <i class="fas fa-history"></i> Work History
            </a>
            <a href="#" class="btn" style="background: #2d5a7b; color: white; padding: 15px; border-radius: 8px; text-decoration: none; font-weight: 600; text-align: center; transition: all 0.3s ease;">
                <i class="fas fa-user"></i> My Profile
            </a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
