# 🚀 VehiCare Multi-Role Authentication - Getting Started

## Quick Start (3 Steps)

### Step 1: Setup Database
```
Visit: http://localhost/vehicare_db/setup.php
```
This creates the users table and a test admin account.

### Step 2: Check System Status
```
Visit: http://localhost/vehicare_db/status.php
```
Verify all components are working correctly.

### Step 3: Login or Register
```
Login:      http://localhost/vehicare_db/login.php
Register:   http://localhost/vehicare_db/register.php
```

---

## 📊 Feature Summary

### ✅ Three User Roles
- **Admin** - Full system access and management
- **Staff** - Task management and service history
- **Client** - Book appointments and view services

### ✅ Custom Form Validation (NO HTML5)
- Real-time field validation
- Error messages on blur and submit
- Password strength requirements
- Email/Username uniqueness checks
- Phone number format validation

### ✅ Security Features
- Bcrypt password hashing
- SQL injection prevention
- Session-based authentication
- XSS protection with htmlspecialchars()
- Account status verification

### ✅ User Experience
- Responsive design
- Professional UI with gradients
- Dark blue + yellow accent colors
- Demo credentials displayed
- Clear error messages
- Loading states

---

## 📝 Registration Form Fields & Validation

```
┌─────────────────────────────────────┐
│          REGISTRATION FORM          │
├─────────────────────────────────────┤
│                                     │
│  Role Selection:                    │
│  ○ Client  ○ Staff  ○ Admin        │
│                                     │
│  Username: ________________         │
│  (3-50 chars, alphanumeric+_.-)    │
│                                     │
│  Email: ________________            │
│  (valid format, must be unique)     │
│                                     │
│  Full Name: ________________         │
│  (2-100 chars, letters/spaces)      │
│                                     │
│  Phone: _________________ (optional) │
│  (valid format, 20 chars max)       │
│                                     │
│  Password: ________________          │
│  (6+ chars, letters + numbers)      │
│                                     │
│  Confirm Password: ________________  │
│  (must match)                       │
│                                     │
│         [CREATE ACCOUNT]            │
│                                     │
└─────────────────────────────────────┘
```

---

## 🔐 Password Requirements

```
✓ Minimum 6 characters
✓ Must contain letters (a-z, A-Z)
✓ Must contain numbers (0-9)
✓ Maximum 255 characters

Examples of VALID passwords:
  - Pass123
  - MyPassword456
  - Admin2025

Examples of INVALID passwords:
  - abc123 (only lowercase + numbers)
  - password (no numbers)
  - 12345 (no letters)
```

---

## 🎯 User Journey Map

```
START
  │
  ├─→ [HOME PAGE]
  │     │
  │     ├─→ "Register" → REGISTRATION FORM
  │     │     │
  │     │     ├─ Fill Form (custom validation)
  │     │     ├─ Select Role
  │     │     ├─ Password Strength Check
  │     │     ├─ Uniqueness Check (DB)
  │     │     │
  │     │     └─→ SUCCESS → LOGIN PAGE
  │     │
  │     └─→ "Sign In" → LOGIN FORM
  │           │
  │           ├─ Email Validation
  │           ├─ Password Validation
  │           ├─ Credentials Check (DB)
  │           │
  │           └─→ ROLE-BASED DASHBOARD
  │                 ├─ ADMIN → /admins/dashboard.php
  │                 ├─ STAFF → /staff/dashboard.php
  │                 └─ CLIENT → /client/dashboard.php
  │
  └─→ LOGOUT
```

---

## 🧪 Test Scenarios

### Scenario 1: Admin Login
1. Visit login page
2. Enter: admin@vehicare.com / admin123
3. Expected: Redirect to Admin Dashboard

### Scenario 2: New Client Registration
1. Visit register page
2. Fill all fields with valid data
3. Select "Client" role
4. Submit
5. Expected: Success message, redirect to login
6. Login with new credentials
7. Expected: Redirect to Client Dashboard

### Scenario 3: Validation Error
1. Visit registration page
2. Enter username: "ab" (less than 3 chars)
3. Leave field and check error
4. Expected: "Username must be at least 3 characters"

### Scenario 4: Password Mismatch
1. Visit registration page
2. Enter password: Pass123
3. Enter confirm: Pass124
4. Try to submit
5. Expected: "Passwords do not match" error

---

## 📁 Complete File Structure

```
vehicare_db/
│
├── 📄 index.php (Home page)
├── 📄 login.php ⭐ (NEW - Login with validation)
├── 📄 register.php ⭐ (NEW - Register with validation)
├── 📄 logout.php (Enhanced)
├── 📄 setup.php ⭐ (NEW - Database setup)
├── 📄 status.php ⭐ (NEW - System status check)
│
├── 📚 admins/
│   ├── 📄 dashboard.php (Admin dashboard - existing)
│   ├── 📄 clients.php
│   ├── 📄 vehicles.php
│   ├── 📄 appointments.php
│   └── ... (other admin pages)
│
├── 📚 client/ ⭐ (NEW)
│   └── 📄 dashboard.php (Client dashboard)
│
├── 📚 staff/ ⭐ (NEW)
│   └── 📄 dashboard.php (Staff dashboard)
│
├── 📚 includes/
│   ├── 📄 header.php (Updated with Login/Register)
│   ├── 📄 footer.php
│   ├── 📄 config.php
│   ├── 📄 adminHeader.php
│   └── 📚 style/
│       ├── 📄 style.css
│       └── 📄 admin.css
│
└── 📄 AUTHENTICATION_SETUP.md ⭐ (NEW - Setup guide)
```

⭐ = New or Modified Files

---

## 🔧 Configuration

### Database Connection
File: `includes/config.php`
```php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vehicare_db";
```

### Users Table Schema
```sql
CREATE TABLE users (
  user_id INT PRIMARY KEY AUTO_INCREMENT,
  username VARCHAR(50) UNIQUE NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  full_name VARCHAR(100) NOT NULL,
  phone VARCHAR(20),
  role ENUM('admin', 'staff', 'client') NOT NULL,
  status ENUM('active', 'inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

---

## 🎨 Design Theme

### Colors
- **Primary:** #1a3a52 (Dark Blue)
- **Secondary:** #2d5a7b (Medium Blue)
- **Accent:** #ffc107, #ff9800 (Yellow/Orange)
- **Text:** #333 (Dark Gray)
- **Border:** #e0e0e0 (Light Gray)

### Typography
- **Font Family:** Poppins, Arial
- **Headers:** Bold, 16-32px
- **Body:** Regular, 12-16px

### Components
- **Forms:** Clean, minimal
- **Buttons:** Gradient with hover effects
- **Cards:** White backgrounds with shadows
- **Alerts:** Color-coded (danger/success/info)

---

## ✨ Special Features

### Real-Time Validation
Fields are validated as user types and on blur event:
```javascript
// On blur
emailInput.addEventListener('blur', validateEmail);

// On input - clear errors
emailInput.addEventListener('input', function() {
    if (emailError.textContent) {
        emailError.textContent = '';
    }
});
```

### Responsive Design
Works on all device sizes:
- Desktop: Full layout
- Tablet: Adjusted spacing
- Mobile: Single column layout

### Demo Credentials Display
Login page shows test credentials:
```
Demo Credentials:
Email: admin@vehicare.com | Password: admin123
```

---

## 🚨 Troubleshooting

### Issue: "Users table doesn't exist"
**Solution:** Run `http://localhost/vehicare_db/setup.php`

### Issue: Password hashing fails
**Solution:** Ensure PHP has bcrypt support (available in PHP 5.3+)

### Issue: Validation not working
**Solution:** Check browser console for JavaScript errors

### Issue: Login always shows "Invalid credentials"
**Solution:** 
1. Check admin user exists in database
2. Verify password is hashed with bcrypt
3. Check `password_verify()` function works

---

## 📞 Support Resources

1. **Setup Guide:** `AUTHENTICATION_SETUP.md`
2. **Implementation Summary:** `IMPLEMENTATION_SUMMARY.md`
3. **System Status:** `status.php`
4. **Source Code:** Comments in each PHP file

---

## ✅ Pre-Launch Checklist

- [ ] Run `setup.php` to create tables
- [ ] Check `status.php` - all green
- [ ] Test login with admin@vehicare.com / admin123
- [ ] Test registration with new account
- [ ] Test logout
- [ ] Test role-based redirects
- [ ] Test form validation (enter invalid data)
- [ ] Test responsive design (mobile view)
- [ ] Check all buttons work
- [ ] Verify database entries after registration

---

## 🎓 Learning Points

This authentication system demonstrates:
- ✅ Custom form validation without HTML5
- ✅ Server-side validation with PHP
- ✅ Client-side validation with JavaScript
- ✅ Database operations (CREATE, SELECT, INSERT)
- ✅ Password hashing with bcrypt
- ✅ Session management
- ✅ Role-based access control
- ✅ Responsive web design
- ✅ Security best practices

---

**Status:** ✅ Ready to Use
**Last Updated:** January 19, 2026
**Version:** 1.0

For detailed information, visit: `/vehicare_db/status.php`
