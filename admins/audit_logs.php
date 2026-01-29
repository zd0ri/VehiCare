<?php

session_start();
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /vehicare_db/login.php");
    exit;
}

$page_title = 'Audit Logs & Activity';
$page_icon = 'fas fa-history';
include __DIR__ . '/includes/admin_layout_header.php';


$activity_stats = $conn->query("
    SELECT 
        COUNT(*) as total_logs,
        COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as today_logs,
        COUNT(CASE WHEN status = 'success' THEN 1 END) as successful_actions,
        COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_actions
    FROM audit_logs
")->fetch_assoc();


$logs = $conn->query("
    SELECT al.*, u.full_name
    FROM audit_logs al
    LEFT JOIN users u ON al.user_id = u.user_id
    ORDER BY al.created_at DESC
    LIMIT 500
");


$actions = $conn->query("
    SELECT action, COUNT(*) as count
    FROM audit_logs
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY action
    ORDER BY count DESC
");
?>

<style>
    .container { max-width: 1200px; margin: 0 auto; }
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .stat-card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-left: 4px solid 
    .stat-number { font-size: 32px; font-weight: bold; margin: 10px 0; }
    .stat-label { color: 
    .logs-table { width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .logs-table thead { background: 
    .logs-table th, .logs-table td { padding: 15px; text-align: left; border-bottom: 1px solid 
    .logs-table tbody tr:hover { background: 
    .status-badge { display: inline-block; padding: 6px 12px; border-radius: 5px; font-size: 12px; font-weight: bold; }
    .status-badge.success { background: 
    .status-badge.failed { background: 
    .action-badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; background: 
    .filter-bar { background: white; padding: 20px; margin-bottom: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .filter-group { display: flex; gap: 15px; flex-wrap: wrap; }
    .filter-group input, .filter-group select { padding: 8px; border: 1px solid 
    .chart-container { background: white; padding: 20px; margin-bottom: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .action-list { list-style: none; padding: 0; }
    .action-list li { padding: 10px; border-bottom: 1px solid 
    .action-list li:hover { background: 
    .action-name { font-weight: bold; }
    .action-count { color: 
    .tabs { display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 2px solid 
    .tab { padding: 10px 20px; cursor: pointer; border-bottom: 3px solid transparent; }
    .tab.active { border-bottom-color: 
    .btn { padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; font-size: 13px; }
    .btn-primary { background: 
    .btn-primary:hover { background: 
    .btn-secondary { background: 
    .btn-secondary:hover { background: 
    .btn-danger { background: 
    .btn-danger:hover { background: 
</style>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="margin: 0;"><i class="fas fa-history"></i> Audit Logs & System Activity</h2>
        <div>
            <button class="btn btn-primary" onclick="refreshLogs()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <button class="btn btn-secondary" onclick="exportLogs()">
                <i class="fas fa-download"></i> Export
            </button>
        </div>
    </div>
    
    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card" style="border-left-color: 
            <div class="stat-label">Total Logs</div>
            <div class="stat-number" style="color: 
        </div>
        
        <div class="stat-card" style="border-left-color: 
            <div class="stat-label">Today's Activity</div>
            <div class="stat-number" style="color: 
        </div>
        
        <div class="stat-card" style="border-left-color: 
            <div class="stat-label">Successful Actions</div>
            <div class="stat-number" style="color: 
        </div>
        
        <div class="stat-card" style="border-left-color: 
            <div class="stat-label">Failed Actions</div>
            <div class="stat-number" style="color: 
        </div>
    </div>
    
    <!-- Tabs -->
    <div class="tabs">
        <div class="tab active" onclick="switchTab('activity-log')">Activity Log</div>
        <div class="tab" onclick="switchTab('action-summary')">Action Summary</div>
        <div class="tab" onclick="switchTab('user-activity')">User Activity</div>
    </div>
    
    <!-- Activity Log Tab -->
    <div id="activity-log" class="tab-content">
        <!-- Filters -->
        <div class="filter-bar">
            <div class="filter-group">
                <select id="filterAction" onchange="filterLogs()">
                    <option value="">All Actions</option>
                    <option value="CREATE_">Create</option>
                    <option value="UPDATE_">Update</option>
                    <option value="DELETE_">Delete</option>
                    <option value="LOGIN">Login</option>
                    <option value="LOGOUT">Logout</option>
                </select>
                <select id="filterStatus" onchange="filterLogs()">
                    <option value="">All Status</option>
                    <option value="success">Success</option>
                    <option value="failed">Failed</option>
                </select>
                <select id="filterUser" onchange="filterLogs()">
                    <option value="">All Users</option>
                    <?php
                    $users = $conn->query("SELECT DISTINCT al.user_id, u.full_name FROM audit_logs al LEFT JOIN users u ON al.user_id = u.user_id WHERE al.user_id IS NOT NULL");
                    while ($user = $users->fetch_assoc()) {
                        echo '<option value="' . $user['user_id'] . '">' . htmlspecialchars($user['full_name'] ?? 'Unknown') . '</option>';
                    }
                    ?>
                </select>
                <input type="date" id="filterDate" onchange="filterLogs()">
            </div>
        </div>
        
        <!-- Logs Table -->
        <table class="logs-table">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Table</th>
                    <th>Record ID</th>
                    <th>Status</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody id="logsBody">
                <?php while ($log = $logs->fetch_assoc()): ?>
                    <tr class="log-row" 
                        data-action="<?php echo $log['action']; ?>"
                        data-status="<?php echo $log['status']; ?>"
                        data-user="<?php echo $log['user_id']; ?>"
                        data-date="<?php echo substr($log['created_at'], 0, 10); ?>">
                        <td>
                            <small><?php echo date('M d, Y H:i:s', strtotime($log['created_at'])); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($log['full_name'] ?? 'System'); ?></td>
                        <td>
                            <span class="action-badge"><?php echo htmlspecialchars($log['action']); ?></span>
                        </td>
                        <td><?php echo htmlspecialchars($log['table_name'] ?? '-'); ?></td>
                        <td><?php echo $log['record_id'] ?? '-'; ?></td>
                        <td>
                            <span class="status-badge <?php echo $log['status']; ?>">
                                <?php echo ucfirst($log['status']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($log['description']): ?>
                                <small><?php echo htmlspecialchars(substr($log['description'], 0, 50)); ?></small>
                            <?php else: ?>
                                <small style="color: 
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Action Summary Tab -->
    <div id="action-summary" class="tab-content" style="display: none;">
        <div class="chart-container">
            <h3>Action Summary (Last 30 Days)</h3>
            <ul class="action-list">
                <?php while ($action = $actions->fetch_assoc()): ?>
                    <li>
                        <span class="action-name"><?php echo htmlspecialchars($action['action']); ?></span>
                        <span class="action-count"><?php echo $action['count']; ?> times</span>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>
    </div>
    
    <!-- User Activity Tab -->
    <div id="user-activity" class="tab-content" style="display: none;">
        <div class="chart-container">
            <h3>User Activity Summary</h3>
            <table class="logs-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Total Actions</th>
                        <th>Last Activity</th>
                        <th>Successful</th>
                        <th>Failed</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $user_activity = $conn->query("
                        SELECT 
                            u.full_name,
                            COUNT(al.log_id) as total_actions,
                            MAX(al.created_at) as last_activity,
                            COUNT(CASE WHEN al.status = 'success' THEN 1 END) as successful,
                            COUNT(CASE WHEN al.status = 'failed' THEN 1 END) as failed
                        FROM audit_logs al
                        LEFT JOIN users u ON al.user_id = u.user_id
                        WHERE al.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                        GROUP BY al.user_id, u.full_name
                        ORDER BY total_actions DESC
                    ");
                    
                    while ($user = $user_activity->fetch_assoc()):
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['full_name'] ?? 'System'); ?></td>
                            <td><?php echo $user['total_actions']; ?></td>
                            <td><small><?php echo date('M d, Y H:i', strtotime($user['last_activity'])); ?></small></td>
                            <td><span style="color: 
                            <td><span style="color: 
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
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

function filterLogs() {
    const action = document.getElementById('filterAction').value;
    const status = document.getElementById('filterStatus').value;
    const user = document.getElementById('filterUser').value;
    const date = document.getElementById('filterDate').value;
    
    const rows = document.querySelectorAll('.log-row');
    rows.forEach(row => {
        let show = true;
        
        if (action && !row.dataset.action.startsWith(action)) show = false;
        if (status && row.dataset.status !== status) show = false;
        if (user && row.dataset.user !== user) show = false;
        if (date && !row.dataset.date.startsWith(date)) show = false;
        
        row.style.display = show ? '' : 'none';
    });
}

function refreshLogs() {
    location.reload();
}

function exportLogs() {
    alert('Implement export functionality - generate CSV log file');
}
</script>

<?php include __DIR__ . '/includes/admin_layout_footer.php'; ?>

