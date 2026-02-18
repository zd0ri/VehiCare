<?php
/**
 * Role Test Page
 * Quick test to verify role-based access control is working
 */

// Include necessary files
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/helpers/auth_helpers.php';

// Test authentication and role functions
echo "<h1>Role & Permission Test</h1>";

if (!checkAuth(false)) {
    echo "<p style='color: red;'>❌ Not authenticated</p>";
    echo "<a href='/vehicare_db/login.php'>Login to test</a>";
    exit;
}

$user = getCurrentUser();
$role = getUserRole();

echo "<h2>User Information:</h2>";
echo "<p><strong>Name:</strong> " . htmlspecialchars($user['full_name']) . "</p>";
echo "<p><strong>Email:</strong> " . htmlspecialchars($user['email']) . "</p>";
echo "<p><strong>Role:</strong> " . htmlspecialchars($role) . "</p>";

echo "<h2>Role Checks:</h2>";
echo "<p>Is Admin: " . (isAdmin() ? "✅ Yes" : "❌ No") . "</p>";
echo "<p>Is Staff: " . (isStaff() ? "✅ Yes" : "❌ No") . "</p>";
echo "<p>Is Client: " . (isClient() ? "✅ Yes" : "❌ No") . "</p>";

echo "<h2>Module Access:</h2>";
$modules = ['dashboard', 'appointments', 'users', 'inventory', 'reports'];
foreach ($modules as $module) {
    $access = canAccessModule($module);
    echo "<p>$module: " . ($access ? "✅ Allowed" : "❌ Denied") . "</p>";
}

echo "<h2>Permission Checks:</h2>";
$permissions = ['view_all_appointments', 'create_appointments', 'manage_users', 'full_admin_access'];
foreach ($permissions as $permission) {
    $has = hasPermission($permission);
    echo "<p>$permission: " . ($has ? "✅ Has Permission" : "❌ No Permission") . "</p>";
}

echo "<h2>Test Links:</h2>";
echo "<p><a href='/vehicare_db/admins/dashboard.php'>Admin Dashboard</a> (Admin only)</p>";
echo "<p><a href='/vehicare_db/staff/dashboard.php'>Staff Dashboard</a> (Staff only)</p>";
echo "<p><a href='/vehicare_db/client/dashboard.php'>Client Dashboard</a> (Client only)</p>";
echo "<p><a href='/vehicare_db/403.php'>403 Page Test</a></p>";

echo "<hr>";
echo "<p><a href='/vehicare_db/logout.php'>Logout</a></p>";
?>