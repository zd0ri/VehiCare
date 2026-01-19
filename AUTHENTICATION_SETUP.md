# VehiCare Authentication System Setup Guide

## Overview
Multi-role authentication system with custom form validation for Clients, Staff, and Admins.

## Files Created/Modified

### New Files Created:
1. **login.php** - Login page with custom validation
2. **register.php** - Registration page with role selection and custom validation
3. **setup.php** - Database setup script (run once to create users table)
4. **client/dashboard.php** - Client dashboard
5. **staff/dashboard.php** - Staff dashboard

### Modified Files:
1. **includes/header.php** - Added Login/Register buttons
2. **logout.php** - Enhanced logout redirect
3. **admins/delete.php** - No changes needed (already checks for admin role)

---

## Setup Instructions

### Step 1: Create the Users Table
1. Open your browser and go to: `http://localhost/vehicare_db/setup.php`
2. This will create the `users` table and a test admin account
3. You should see success messages

### Step 2: Database Structure
The `users` table will be created with the following structure:
```
- user_id (INT, Primary Key, Auto Increment)
- username (VARCHAR 50, UNIQUE)
- email (VARCHAR 100, UNIQUE)
- password (VARCHAR 255, hashed with bcrypt)
- full_name (VARCHAR 100)
- phone (VARCHAR 20)
- role (ENUM: 'admin', 'staff', 'client')
- status (ENUM: 'active', 'inactive')
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

---

## Default Test Accounts

### Admin Account:
- **Email:** admin@vehicare.com
- **Password:** admin123
- **Role:** Admin
- **Access:** `/vehicare_db/login.php` → Dashboard at `/vehicare_db/admins/dashboard.php`

---

## Features Implemented

### Login Page (`/vehicare_db/login.php`)
✅ Custom form validation (NO HTML5 validation)
✅ Real-time error messages on blur and submit
✅ Email format validation
✅ Password length validation
✅ Role-based dashboard redirection
✅ Remember me checkbox
✅ "Forgot password" link placeholder
✅ Session management
✅ Responsive design

### Registration Page (`/vehicare_db/register.php`)
✅ Role selection (Client, Staff, Admin)
✅ Custom form validation for all fields
✅ Real-time field validation
✅ Username availability check
✅ Email availability check
✅ Password strength requirements:
   - Minimum 6 characters
   - Must contain letters and numbers
   - Confirmation matching
✅ Phone number format validation
✅ Full name format validation
✅ Secure password hashing (bcrypt)
✅ Responsive design with role selector

### Validation Rules

#### Username:
- Required
- 3-50 characters
- Only letters, numbers, underscores, dots, and hyphens

#### Email:
- Required
- Valid email format
- Must be unique

#### Full Name:
- Required
- 2-100 characters
- Only letters, spaces, hyphens, and apostrophes

#### Phone (Optional):
- Valid phone format
- Maximum 20 characters

#### Password:
- Required
- Minimum 6 characters
- Must contain both letters and numbers
- Maximum 255 characters

#### Confirm Password:
- Required
- Must match password field

---

## User Roles & Dashboards

### 1. **Admin Role**
- **Dashboard:** `/vehicare_db/admins/dashboard.php`
- **Capabilities:** Full system access, manage all users and data
- **Features:**
  - Client management
  - Vehicle management
  - Appointment management
  - Staff management
  - Services management
  - Payment tracking

### 2. **Staff Role**
- **Dashboard:** `/vehicare_db/staff/dashboard.php`
- **Capabilities:** Access assigned tasks and service history
- **Features:**
  - View today's tasks
  - Complete service requests
  - View work history
  - Receive ratings

### 3. **Client Role**
- **Dashboard:** `/vehicare_db/client/dashboard.php`
- **Capabilities:** Book appointments, view vehicles and service history
- **Features:**
  - Book appointments
  - View vehicles
  - View service history
  - Track payments

---

## Navigation Flow

### Unauthenticated User:
1. Home page (`index.php`)
2. Click "Register" → Registration page
3. Fill form and select role
4. Redirected to login page after successful registration
5. Log in with credentials
6. Redirected to role-specific dashboard

### Login Page Behavior:
- If user is already logged in → Redirected to appropriate dashboard
- Invalid credentials → Shows error messages
- Account inactive → Shows status message

### Logout:
- Click "Logout" button
- Session destroyed
- Redirected to home page

---

## Creating More Test Accounts

### Via Registration Page:
1. Go to `http://localhost/vehicare_db/register.php`
2. Fill in all required fields
3. Select role (Client, Staff, or Admin)
4. Submit
5. Go to login page and test

### Via phpMyAdmin (Manual):
```sql
INSERT INTO users (username, email, password, full_name, phone, role, status) 
VALUES (
    'teststaff',
    'staff@vehicare.com',
    '$2y$10$...', -- Use password_hash('password123', PASSWORD_BCRYPT)
    'Test Staff',
    '+1234567890',
    'staff',
    'active'
);
```

---

## Security Features

✅ **Password Security:**
- Passwords hashed using bcrypt (PASSWORD_BCRYPT)
- Uses `password_verify()` for validation
- No plain text passwords stored

✅ **SQL Injection Prevention:**
- Uses `real_escape_string()` for database queries
- Input sanitization

✅ **Session Management:**
- Session-based authentication
- `$_SESSION` variables for user state
- Automatic redirect for unauthorized access

✅ **Input Validation:**
- Server-side validation (PHP)
- Client-side validation (JavaScript)
- No HTML5 validation dependency

✅ **Account Status:**
- Users can be marked as inactive
- Inactive accounts cannot log in

---

## Customization Guide

### Change Default Dashboard Redirect:
Edit `login.php` line ~55-60:
```php
if ($user['role'] === 'admin') {
    header("Location: /vehicare_db/admins/dashboard.php");
} elseif ($user['role'] === 'staff') {
    header("Location: /vehicare_db/staff/dashboard.php");
} else {
    header("Location: /vehicare_db/client/dashboard.php");
}
```

### Add New Role:
1. Update users table role enum:
```sql
ALTER TABLE users MODIFY role ENUM('admin', 'staff', 'client', 'newrole');
```
2. Add role option to register.php form
3. Add role validation in both login and register
4. Create dashboard for new role

### Add Password Reset Feature:
1. Create `forgot-password.php`
2. Create `reset-password.php`
3. Update database schema to add password reset tokens
4. Implement email notification

---

## Troubleshooting

### "Users table doesn't exist" error:
- Run `http://localhost/vehicare_db/setup.php` again
- Check if database exists

### "Invalid email or password" but credentials are correct:
- Ensure bcrypt is enabled in PHP
- Check `php.ini` for password_* function availability

### Can't register due to validation errors:
- Check browser console for JavaScript errors
- Ensure all required fields are filled
- Check password strength requirements

### Redirect not working after login:
- Check browser cookies are enabled
- Verify session.save_path is writable
- Check for PHP errors in logs

---

## File Structure
```
vehicare_db/
├── login.php (NEW)
├── register.php (NEW)
├── setup.php (NEW)
├── logout.php (MODIFIED)
├── index.php
├── client/
│   └── dashboard.php (NEW)
├── staff/
│   └── dashboard.php (NEW)
├── admins/
│   └── dashboard.php (existing)
└── includes/
    └── header.php (MODIFIED)
```

---

## Next Steps

1. ✅ Run setup.php to create users table
2. ✅ Test login with admin@vehicare.com / admin123
3. ✅ Create test accounts via registration
4. ✅ Customize dashboards as needed
5. ✅ Implement additional features per role
6. ✅ Add password reset functionality (optional)
7. ✅ Set up email notifications (optional)

---

For more information or support, contact: admin@vehicare.com
