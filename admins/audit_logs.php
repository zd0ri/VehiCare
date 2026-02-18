<?php
require_once '../includes/config.php';
require_once '../app/middleware/AuthMiddleware.php';
require_once '../app/middleware/AuditLogger.php';

// Check authentication and admin role
AuthMiddleware::requireAuth();
AuthMiddleware::requireRole(['admin']);

$audit_logger = new AuditLogger($pdo);

// Handle AJAX requests
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    
    try {
        if ($_GET['ajax'] === 'logs') {
            $filters = [
                'user_id' => $_GET['user_id'] ?? '',
                'action' => $_GET['action'] ?? '',
                'table_name' => $_GET['table_name'] ?? '',
                'date_from' => $_GET['date_from'] ?? '',
                'date_to' => $_GET['date_to'] ?? '',
                'search' => $_GET['search'] ?? ''
            ];
            
            $page = max(1, intval($_GET['page'] ?? 1));
            $per_page = 25;
            $offset = ($page - 1) * $per_page;
            
            $logs = $audit_logger->getLogs($filters, $per_page, $offset);
            $total = $audit_logger->getLogCount($filters);
            
            echo json_encode([
                'success' => true,
                'logs' => $logs,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $per_page,
                    'total_records' => $total,
                    'total_pages' => ceil($total / $per_page)
                ]
            ]);
            
        } elseif ($_GET['ajax'] === 'stats') {
            $days = intval($_GET['days'] ?? 30);
            $stats = $audit_logger->getStats($days);
            echo json_encode(['success' => true, 'stats' => $stats]);
            
        } elseif ($_GET['ajax'] === 'export') {
            // Export audit logs to CSV
            $filters = [
                'user_id' => $_GET['user_id'] ?? '',
                'action' => $_GET['action'] ?? '',
                'table_name' => $_GET['table_name'] ?? '',
                'date_from' => $_GET['date_from'] ?? '',
                'date_to' => $_GET['date_to'] ?? '',
                'search' => $_GET['search'] ?? ''
            ];
            
            $logs = $audit_logger->getLogs($filters, 10000, 0); // Large limit for export
            
            // Log the export action
            $audit_logger->logExport('audit_logs', $filters, count($logs));
            
            // Set CSV headers
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="audit_logs_' . date('Y-m-d_H-i-s') . '.csv"');
            
            $output = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($output, [
                'Timestamp', 'User', 'Action', 'Table', 'Record ID', 
                'Description', 'IP Address', 'User Agent'
            ]);
            
            foreach ($logs as $log) {
                fputcsv($output, [
                    $log['created_at'],
                    $log['user_name'] . ' (' . $log['user_email'] . ')',
                    $log['action'],
                    $log['table_name'],
                    $log['record_id'],
                    $log['description'],
                    $log['ip_address'],
                    substr($log['user_agent'], 0, 100) // Truncate user agent
                ]);
            }
            
            fclose($output);
            exit;
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Get filter options for dropdowns
$stmt = $pdo->query("SELECT DISTINCT u.user_id, u.full_name, u.email FROM users u INNER JOIN audit_logs al ON u.user_id = al.user_id ORDER BY u.full_name");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$actions = ['LOGIN', 'LOGOUT', 'CREATE', 'UPDATE', 'DELETE', 'VIEW', 'EXPORT', 'ACCESS_DENIED', 'SETTINGS_CHANGE'];

$stmt = $pdo->query("SELECT DISTINCT table_name FROM audit_logs WHERE table_name IS NOT NULL ORDER BY table_name");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Page title and breadcrumb
$page_title = "Audit Logs";
$breadcrumb = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php'],
    ['title' => 'Audit Logs', 'url' => '']
];

include 'includes/admin_header.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row">
            <div class="col-12">
                <div class="page-header">
                    <h2>Audit Logs</h2>
                    <p class="text-muted">Track all system activities and user actions</p>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4" id="statsCards">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-list-ul text-primary fa-2x"></i>
                            </div>
                            <div>
                                <h6 class="card-subtitle mb-1 text-muted">Total Logs (30d)</h6>
                                <h4 class="card-title mb-0" id="totalLogs">--</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-users text-success fa-2x"></i>
                            </div>
                            <div>
                                <h6 class="card-subtitle mb-1 text-muted">Active Users</h6>
                                <h4 class="card-title mb-0" id="activeUsers">--</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-edit text-warning fa-2x"></i>
                            </div>
                            <div>
                                <h6 class="card-subtitle mb-1 text-muted">Updates Today</h6>
                                <h4 class="card-title mb-0" id="updatesToday">--</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-shield-alt text-danger fa-2x"></i>
                            </div>
                            <div>
                                <h6 class="card-subtitle mb-1 text-muted">Security Events</h6>
                                <h4 class="card-title mb-0" id="securityEvents">--</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form id="filtersForm" class="row g-3">
                    <div class="col-md-2">
                        <label for="filterUser" class="form-label">User</label>
                        <select class="form-select" id="filterUser" name="user_id">
                            <option value="">All Users</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= $user['user_id'] ?>"><?= htmlspecialchars($user['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="filterAction" class="form-label">Action</label>
                        <select class="form-select" id="filterAction" name="action">
                            <option value="">All Actions</option>
                            <?php foreach ($actions as $action): ?>
                                <option value="<?= $action ?>"><?= $action ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="filterTable" class="form-label">Table</label>
                        <select class="form-select" id="filterTable" name="table_name">
                            <option value="">All Tables</option>
                            <?php foreach ($tables as $table): ?>
                                <option value="<?= $table ?>"><?= htmlspecialchars($table) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="filterDateFrom" class="form-label">From</label>
                        <input type="date" class="form-control" id="filterDateFrom" name="date_from">
                    </div>
                    <div class="col-md-2">
                        <label for="filterDateTo" class="form-label">To</label>
                        <input type="date" class="form-control" id="filterDateTo" name="date_to">
                    </div>
                    <div class="col-md-2">
                        <label for="filterSearch" class="form-label">Search</label>
                        <input type="text" class="form-control" id="filterSearch" name="search" placeholder="Search description...">
                    </div>
                </form>
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="button" class="btn btn-primary" onclick="loadLogs()">
                            <i class="fas fa-search"></i> Apply Filters
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="clearFilters()">
                            <i class="fas fa-times"></i> Clear
                        </button>
                        <button type="button" class="btn btn-success" onclick="exportLogs()">
                            <i class="fas fa-download"></i> Export CSV
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Logs Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Audit Log Entries</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="15%">Timestamp</th>
                                <th width="12%">User</th>
                                <th width="10%">Action</th>
                                <th width="10%">Table</th>
                                <th width="8%">Record</th>
                                <th width="35%">Description</th>
                                <th width="10%">IP Address</th>
                            </tr>
                        </thead>
                        <tbody id="logsTableBody">
                            <tr>
                                <td colspan="7" class="text-center">
                                    <div class="spinner-border" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <nav aria-label="Audit logs pagination" id="paginationNav" style="display: none;">
                    <ul class="pagination justify-content-center" id="pagination">
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Log Details Modal -->
<div class="modal fade" id="logDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Log Entry Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="logDetailsBody">
                <!-- Content loaded via JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentPage = 1;
let currentFilters = {};

$(document).ready(function() {
    loadStats();
    loadLogs();
    
    // Auto-refresh every 30 seconds
    setInterval(function() {
        loadStats();
        if (currentPage === 1) {
            loadLogs();
        }
    }, 30000);
});

function loadStats() {
    $.get('audit_logs.php?ajax=stats&days=30')
        .done(function(response) {
            if (response.success) {
                const stats = response.stats;
                
                $('#totalLogs').text(stats.total_logs.toLocaleString());
                $('#activeUsers').text(stats.active_users.length);
                
                // Count today's updates
                const today = new Date().toISOString().split('T')[0];
                const todayActivity = stats.daily_activity.find(d => d.date === today);
                $('#updatesToday').text(todayActivity ? todayActivity.count : 0);
                
                // Count security events (ACCESS_DENIED, failed logins)
                const securityEvents = stats.by_action
                    .filter(a => ['ACCESS_DENIED', 'LOGIN'].includes(a.action))
                    .reduce((sum, a) => sum + parseInt(a.count), 0);
                $('#securityEvents').text(securityEvents);
            }
        })
        .fail(function() {
            console.error('Failed to load audit stats');
        });
}

function loadLogs(page = 1) {
    currentPage = page;
    const formData = new FormData(document.getElementById('filtersForm'));
    const params = new URLSearchParams(formData);
    params.append('ajax', 'logs');
    params.append('page', page);
    
    currentFilters = Object.fromEntries(formData);
    
    $('#logsTableBody').html(`
        <tr>
            <td colspan="7" class="text-center">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </td>
        </tr>
    `);
    
    $.get('audit_logs.php?' + params.toString())
        .done(function(response) {
            if (response.success) {
                displayLogs(response.logs);
                displayPagination(response.pagination);
            } else {
                showError('Failed to load logs: ' + response.message);
            }
        })
        .fail(function() {
            showError('Failed to load audit logs');
        });
}

function displayLogs(logs) {
    if (logs.length === 0) {
        $('#logsTableBody').html(`
            <tr>
                <td colspan="7" class="text-center text-muted">No audit logs found</td>
            </tr>
        `);
        return;
    }
    
    const tbody = $('#logsTableBody');
    tbody.empty();
    
    logs.forEach(log => {
        const row = `
            <tr class="audit-log-row" style="cursor: pointer;" onclick="showLogDetails(${log.log_id})">
                <td>
                    <small>${formatDateTime(log.created_at)}</small>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="user-avatar me-2">
                            <i class="fas fa-user-circle text-muted"></i>
                        </div>
                        <div>
                            <small class="fw-bold">${escapeHtml(log.user_name || 'System')}</small>
                            <br>
                            <small class="text-muted">${escapeHtml(log.user_role || '')}</small>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="badge ${getActionBadgeClass(log.action)}">${log.action}</span>
                </td>
                <td>
                    <code class="small">${escapeHtml(log.table_name || '')}</code>
                </td>
                <td>
                    ${log.record_id ? `<small>#${log.record_id}</small>` : '<small class="text-muted">--</small>'}
                </td>
                <td>
                    <small>${escapeHtml(log.description || '')}</small>
                </td>
                <td>
                    <small class="text-muted">${escapeHtml(log.ip_address || '--')}</small>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
}

function displayPagination(pagination) {
    const paginationNav = $('#paginationNav');
    const paginationUl = $('#pagination');
    
    if (pagination.total_pages <= 1) {
        paginationNav.hide();
        return;
    }
    
    paginationUl.empty();
    
    // Previous button
    if (pagination.current_page > 1) {
        paginationUl.append(`
            <li class="page-item">
                <a class="page-link" href="#" onclick="loadLogs(${pagination.current_page - 1})">Previous</a>
            </li>
        `);
    }
    
    // Page numbers
    const startPage = Math.max(1, pagination.current_page - 2);
    const endPage = Math.min(pagination.total_pages, pagination.current_page + 2);
    
    for (let i = startPage; i <= endPage; i++) {
        const activeClass = i === pagination.current_page ? 'active' : '';
        paginationUl.append(`
            <li class="page-item ${activeClass}">
                <a class="page-link" href="#" onclick="loadLogs(${i})">${i}</a>
            </li>
        `);
    }
    
    // Next button
    if (pagination.current_page < pagination.total_pages) {
        paginationUl.append(`
            <li class="page-item">
                <a class="page-link" href="#" onclick="loadLogs(${pagination.current_page + 1})">Next</a>
            </li>
        `);
    }
    
    paginationNav.show();
}

function showLogDetails(logId) {
    // Find the log in current logs data
    const params = new URLSearchParams(currentFilters);
    params.append('ajax', 'logs');
    params.append('page', currentPage);
    
    $.get('audit_logs.php?' + params.toString())
        .done(function(response) {
            if (response.success) {
                const log = response.logs.find(l => l.log_id == logId);
                if (log) {
                    displayLogDetailsModal(log);
                }
            }
        });
}

function displayLogDetailsModal(log) {
    const modalBody = $('#logDetailsBody');
    
    let oldValues = '';
    let newValues = '';
    
    try {
        if (log.old_values) {
            const oldData = JSON.parse(log.old_values);
            oldValues = '<pre class="bg-light p-2 small">' + JSON.stringify(oldData, null, 2) + '</pre>';
        }
        
        if (log.new_values) {
            const newData = JSON.parse(log.new_values);
            newValues = '<pre class="bg-light p-2 small">' + JSON.stringify(newData, null, 2) + '</pre>';
        }
    } catch (e) {
        // If JSON parsing fails, show raw values
        if (log.old_values) oldValues = '<pre class="bg-light p-2 small">' + escapeHtml(log.old_values) + '</pre>';
        if (log.new_values) newValues = '<pre class="bg-light p-2 small">' + escapeHtml(log.new_values) + '</pre>';
    }
    
    modalBody.html(`
        <div class="row">
            <div class="col-md-6">
                <h6>Basic Information</h6>
                <table class="table table-borderless table-sm">
                    <tr><td><strong>Timestamp:</strong></td><td>${formatDateTime(log.created_at)}</td></tr>
                    <tr><td><strong>User:</strong></td><td>${escapeHtml(log.user_name)} (${escapeHtml(log.user_email)})</td></tr>
                    <tr><td><strong>Role:</strong></td><td><span class="badge bg-info">${escapeHtml(log.user_role)}</span></td></tr>
                    <tr><td><strong>Action:</strong></td><td><span class="badge ${getActionBadgeClass(log.action)}">${log.action}</span></td></tr>
                    <tr><td><strong>Table:</strong></td><td><code>${escapeHtml(log.table_name || '')}</code></td></tr>
                    <tr><td><strong>Record ID:</strong></td><td>${log.record_id || '--'}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>Technical Details</h6>
                <table class="table table-borderless table-sm">
                    <tr><td><strong>IP Address:</strong></td><td>${escapeHtml(log.ip_address)}</td></tr>
                    <tr><td><strong>User Agent:</strong></td><td><small>${escapeHtml(log.user_agent || '--')}</small></td></tr>
                </table>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-12">
                <h6>Description</h6>
                <p class="alert alert-info">${escapeHtml(log.description || 'No description available')}</p>
            </div>
        </div>
        
        ${oldValues || newValues ? '<div class="row mt-3">' : ''}
            ${oldValues ? '<div class="col-md-6"><h6>Old Values</h6>' + oldValues + '</div>' : ''}
            ${newValues ? '<div class="col-md-6"><h6>New Values</h6>' + newValues + '</div>' : ''}
        ${oldValues || newValues ? '</div>' : ''}
    `);
    
    $('#logDetailsModal').modal('show');
}

function clearFilters() {
    document.getElementById('filtersForm').reset();
    currentPage = 1;
    loadLogs();
}

function exportLogs() {
    const formData = new FormData(document.getElementById('filtersForm'));
    const params = new URLSearchParams(formData);
    params.append('ajax', 'export');
    
    // Open in new window to trigger download
    window.open('audit_logs.php?' + params.toString(), '_blank');
}

function getActionBadgeClass(action) {
    const classes = {
        'LOGIN': 'bg-success',
        'LOGOUT': 'bg-secondary',
        'CREATE': 'bg-primary',
        'UPDATE': 'bg-warning text-dark',
        'DELETE': 'bg-danger',
        'VIEW': 'bg-info',
        'EXPORT': 'bg-dark',
        'ACCESS_DENIED': 'bg-danger',
        'SETTINGS_CHANGE': 'bg-warning text-dark'
    };
    
    return classes[action] || 'bg-secondary';
}

function formatDateTime(dateTime) {
    const date = new Date(dateTime);
    return date.toLocaleDateString() + '<br><small class="text-muted">' + date.toLocaleTimeString() + '</small>';
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showError(message) {
    $('#logsTableBody').html(`
        <tr>
            <td colspan="7" class="text-center text-danger">
                <i class="fas fa-exclamation-triangle"></i> ${message}
            </td>
        </tr>
    `);
}
</script>

<style>
.audit-log-row:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

.user-avatar i {
    font-size: 1.5em;
}

.page-header {
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #dee2e6;
}

.stats-card {
    transition: transform 0.2s;
}

.stats-card:hover {
    transform: translateY(-2px);
}

pre {
    max-height: 200px;
    overflow-y: auto;
}
</style>

<?php include 'includes/admin_layout_footer.php'; ?>

