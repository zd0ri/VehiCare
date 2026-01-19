-- ============================================
-- VehiCare Users Table - Monitoring Queries
-- ============================================

-- ============================================
-- 1. VIEW ALL USERS
-- ============================================

-- Get all users with detailed information
SELECT 
    user_id, 
    username, 
    email, 
    full_name, 
    phone, 
    role, 
    status, 
    created_date, 
    updated_date, 
    last_login
FROM users
ORDER BY created_date DESC;

-- ============================================
-- 2. USER STATISTICS
-- ============================================

-- Count users by role
SELECT 
    role, 
    COUNT(*) as count,
    ROUND((COUNT(*) / (SELECT COUNT(*) FROM users) * 100), 2) as percentage
FROM users
GROUP BY role
ORDER BY count DESC;

-- Count users by status
SELECT 
    status, 
    COUNT(*) as count
FROM users
GROUP BY status;

-- Total active users
SELECT COUNT(*) as total_active_users
FROM users
WHERE status = 'active';

-- ============================================
-- 3. ADMIN ACCOUNTS MONITORING
-- ============================================

-- View all admin accounts
SELECT 
    user_id, 
    username, 
    email, 
    full_name, 
    created_date, 
    last_login
FROM users
WHERE role = 'admin'
ORDER BY created_date DESC;

-- Admins who haven't logged in
SELECT 
    username, 
    email, 
    created_date
FROM users
WHERE role = 'admin' AND last_login IS NULL;

-- ============================================
-- 4. STAFF ACCOUNTS MONITORING
-- ============================================

-- View all staff accounts
SELECT 
    user_id, 
    username, 
    email, 
    full_name, 
    phone, 
    status, 
    created_date, 
    last_login
FROM users
WHERE role = 'staff'
ORDER BY created_date DESC;

-- Staff members by status
SELECT 
    status, 
    COUNT(*) as count
FROM users
WHERE role = 'staff'
GROUP BY status;

-- Active staff members
SELECT 
    username, 
    email, 
    full_name, 
    last_login
FROM users
WHERE role = 'staff' AND status = 'active'
ORDER BY last_login DESC NULLS LAST;

-- ============================================
-- 5. CLIENT ACCOUNTS MONITORING
-- ============================================

-- View all client accounts
SELECT 
    user_id, 
    username, 
    email, 
    full_name, 
    phone, 
    status, 
    created_date, 
    last_login
FROM users
WHERE role = 'client'
ORDER BY created_date DESC;

-- Total clients by status
SELECT 
    status, 
    COUNT(*) as count
FROM users
WHERE role = 'client'
GROUP BY status;

-- Active clients
SELECT 
    username, 
    email, 
    full_name, 
    created_date, 
    last_login
FROM users
WHERE role = 'client' AND status = 'active'
ORDER BY last_login DESC NULLS LAST;

-- ============================================
-- 6. ACTIVITY MONITORING
-- ============================================

-- Users who logged in today
SELECT 
    user_id, 
    username, 
    email, 
    role, 
    last_login
FROM users
WHERE DATE(last_login) = CURDATE()
ORDER BY last_login DESC;

-- Users who logged in this week
SELECT 
    user_id, 
    username, 
    email, 
    role, 
    last_login
FROM users
WHERE last_login >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
ORDER BY last_login DESC;

-- Users who haven't logged in for 30 days
SELECT 
    user_id, 
    username, 
    email, 
    role, 
    status, 
    last_login
FROM users
WHERE last_login < DATE_SUB(NOW(), INTERVAL 30 DAY) OR last_login IS NULL
ORDER BY last_login ASC;

-- Most recently active users
SELECT 
    user_id, 
    username, 
    email, 
    role, 
    last_login
FROM users
WHERE last_login IS NOT NULL
ORDER BY last_login DESC
LIMIT 10;

-- ============================================
-- 7. ACCOUNT MANAGEMENT QUERIES
-- ============================================

-- Suspended accounts
SELECT 
    user_id, 
    username, 
    email, 
    role, 
    status, 
    created_date
FROM users
WHERE status = 'suspended'
ORDER BY updated_date DESC;

-- Inactive accounts
SELECT 
    user_id, 
    username, 
    email, 
    role, 
    created_date
FROM users
WHERE status = 'inactive'
ORDER BY updated_date DESC;

-- Inactive users who haven't logged in for 60 days
SELECT 
    user_id, 
    username, 
    email, 
    role, 
    last_login
FROM users
WHERE status = 'inactive' AND (last_login < DATE_SUB(NOW(), INTERVAL 60 DAY) OR last_login IS NULL)
ORDER BY last_login ASC;

-- ============================================
-- 8. NEW USER REGISTRATIONS
-- ============================================

-- New registrations today
SELECT 
    user_id, 
    username, 
    email, 
    full_name, 
    role, 
    created_date
FROM users
WHERE DATE(created_date) = CURDATE()
ORDER BY created_date DESC;

-- New registrations this month
SELECT 
    user_id, 
    username, 
    email, 
    full_name, 
    role, 
    created_date
FROM users
WHERE YEAR(created_date) = YEAR(NOW()) AND MONTH(created_date) = MONTH(NOW())
ORDER BY created_date DESC;

-- New registrations in the last 7 days
SELECT 
    user_id, 
    username, 
    email, 
    full_name, 
    role, 
    created_date
FROM users
WHERE created_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
ORDER BY created_date DESC;

-- ============================================
-- 9. DUPLICATE CHECK
-- ============================================

-- Check for duplicate emails (should be 0)
SELECT 
    email, 
    COUNT(*) as count
FROM users
GROUP BY email
HAVING COUNT(*) > 1;

-- Check for duplicate usernames (should be 0)
SELECT 
    username, 
    COUNT(*) as count
FROM users
GROUP BY username
HAVING COUNT(*) > 1;

-- ============================================
-- 10. UPDATE OPERATIONS
-- ============================================

-- Activate a user (replace user_id with actual ID)
UPDATE users
SET status = 'active'
WHERE user_id = 1;

-- Deactivate a user
UPDATE users
SET status = 'inactive'
WHERE user_id = 1;

-- Suspend a user
UPDATE users
SET status = 'suspended'
WHERE user_id = 1;

-- Manually update last login (for testing)
UPDATE users
SET last_login = NOW()
WHERE user_id = 1;

-- ============================================
-- 11. BACKUP/EXPORT QUERIES
-- ============================================

-- Export active users (without passwords for security)
SELECT 
    user_id, 
    username, 
    email, 
    full_name, 
    phone, 
    role, 
    status, 
    created_date, 
    last_login
FROM users
WHERE status = 'active'
INTO OUTFILE '/tmp/vehicare_users_export.csv'
FIELDS TERMINATED BY ','
ENCLOSED BY '"'
LINES TERMINATED BY '\n';

-- ============================================
-- 12. SEARCH QUERIES
-- ============================================

-- Search user by email
SELECT * FROM users WHERE email LIKE '%example@email.com%';

-- Search user by username
SELECT * FROM users WHERE username LIKE '%admin%';

-- Search user by full name
SELECT * FROM users WHERE full_name LIKE '%John%';

-- Search user by phone
SELECT * FROM users WHERE phone LIKE '%555%';
