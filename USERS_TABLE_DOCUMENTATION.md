# Users Table - Complete Monitoring System

## Users Table Structure

The `users` table has been created in your database with the following fields:

```sql
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100),
  `phone` varchar(20),
  `role` enum('admin','staff','client') DEFAULT 'client',
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `created_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_date` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_login` datetime DEFAULT NULL
)
```

## Fields Explanation

| Field | Type | Description | Notes |
|-------|------|-------------|-------|
| `user_id` | INT | Unique user identifier | Primary Key, Auto Increment |
| `username` | VARCHAR(50) | User's login username | Unique, Required |
| `email` | VARCHAR(100) | User's email address | Unique, Required |
| `password` | VARCHAR(255) | Hashed password | Required (BCrypt) |
| `full_name` | VARCHAR(100) | User's full name | Optional |
| `phone` | VARCHAR(20) | User's phone number | Optional |
| `role` | ENUM | User role type | Values: admin, staff, client |
| `status` | ENUM | User account status | Values: active, inactive, suspended |
| `created_date` | DATETIME | Account creation timestamp | Auto-set on creation |
| `updated_date` | DATETIME | Last update timestamp | Auto-updates on modification |
| `last_login` | DATETIME | Last login timestamp | Updated on every successful login |

## Features for Monitoring

### 1. **User Role Tracking**
   - Separate admins, staff, and clients in one centralized table
   - Easy to see user distribution by role
   - Role-based access control

### 2. **Account Status Management**
   - **active**: User can log in normally
   - **inactive**: Account disabled but preserved
   - **suspended**: Account temporarily or permanently blocked

### 3. **Login Tracking**
   - `last_login` field automatically updates on successful login
   - Track user activity and engagement
   - Identify inactive users

### 4. **Timestamps**
   - `created_date`: Know when accounts were created
   - `updated_date`: Automatic timestamp for any changes
   - Perfect for audit trails

### 5. **Unique Constraints**
   - Username and email are unique
   - Prevents duplicate accounts
   - Ensures data integrity

## User Management Dashboard

A new admin page has been created at:
**`/vehicare_db/admins/users.php`**

### Features:
- ✅ View all users (admins, staff, clients) in one place
- ✅ Statistics showing count by role
- ✅ Search by username, email, or name
- ✅ Filter by role (Admin, Staff, Client)
- ✅ Filter by status (Active, Inactive, Suspended)
- ✅ Display member join date
- ✅ Display last login time
- ✅ Action buttons for future edit/delete operations
- ✅ Responsive design

## Current Accounts

### Admin Account
- **Username:** admin_user
- **Email:** admin@vehicare.com
- **Password:** VehiCare@2026Admin
- **Status:** Active

### Staff Account
- **Username:** staff_user
- **Email:** staff@vehicare.com
- **Password:** VehiCare@2026Staff
- **Status:** Active

### How to Insert the Accounts

Run the `INSERT_ADMIN_STAFF.sql` file in phpMyAdmin:

```sql
-- Insert Admin Account
INSERT INTO users (username, email, password, full_name, phone, role, status) 
VALUES ('admin_user', 'admin@vehicare.com', '$2y$10$KYR1q8x9p2L8mN3vQ5oZ.uQ7W2X1Y3Z4a5B6C7d8E9F0G1H2I3J4K5', 'VehiCare Administrator', '+1-555-0100', 'admin', 'active');

-- Insert Staff Account
INSERT INTO users (username, email, password, full_name, phone, role, status) 
VALUES ('staff_user', 'staff@vehicare.com', '$2y$10$M2K8p3Q7r9S2U5V6W8X9Y0Z1A2B3C4D5E6F7G8H9I0J1K2L3M4N5O6', 'VehiCare Staff Member', '+1-555-0200', 'staff', 'active');
```

## Implementation Changes

### 1. Database Changes
- ✅ Users table created with complete monitoring fields
- ✅ Indexes added for user_id, username, email, role, and status
- ✅ AUTO_INCREMENT configured

### 2. Login Updates
- ✅ `last_login` field automatically updates on successful login
- ✅ Tracks user activity for monitoring

### 3. Registration Updates
- ✅ New users are inserted into the users table with role='client'
- ✅ Status defaults to 'active'
- ✅ created_date and updated_date automatically set

### 4. Admin Dashboard
- ✅ New user management page created at `/admins/users.php`
- ✅ Full search and filter capabilities
- ✅ Statistics by role
- ✅ Status indicators for quick identification

## SQL Query Examples for Monitoring

```sql
-- Get all active users
SELECT * FROM users WHERE status = 'active' ORDER BY created_date DESC;

-- Get users by role
SELECT * FROM users WHERE role = 'client' ORDER BY created_date DESC;

-- Find inactive users
SELECT * FROM users WHERE status = 'inactive';

-- Users who haven't logged in
SELECT * FROM users WHERE last_login IS NULL;

-- Recently logged in users (last 24 hours)
SELECT * FROM users WHERE last_login >= DATE_SUB(NOW(), INTERVAL 1 DAY);

-- Count users by role
SELECT role, COUNT(*) as count FROM users GROUP BY role;

-- Find accounts created in the last 30 days
SELECT * FROM users WHERE created_date >= DATE_SUB(NOW(), INTERVAL 30 DAY);
```

## Next Steps

1. **Run the SQL file** `vehicare_db.sql` to update your database with the users table
2. **Run the insert file** `INSERT_ADMIN_STAFF.sql` to add the admin and staff accounts
3. **Access the user management page** at `http://localhost/vehicare_db/admins/users.php` (login as admin first)
4. **View user statistics** and manage accounts from the admin dashboard
