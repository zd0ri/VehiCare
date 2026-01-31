# VehiCare Technician Account Credentials

## Overview
All technicians have been added to the system with the following default password: **Tech@123**

These accounts allow technicians to login and manage their appointments using the kanban-style dashboard.

---

## Technician Accounts

### 1. **John Smith** - Oil Change & Filter Specialist
- **Username:** `john.smith`
- **Email:** `john.smith@vehicare.com`
- **Phone:** 09171234561
- **Password:** `Tech@123`
- **Status:** Active
- **Specialization:** Oil Change & Filter Replacement
- **Location:** Manila, NCR

### 2. **Mike Johnson** - Brake Service Specialist
- **Username:** `mike.johnson`
- **Email:** `mike.johnson@vehicare.com`
- **Phone:** 09171234562
- **Password:** `Tech@123`
- **Status:** Active
- **Specialization:** Brake Service & Maintenance
- **Location:** Makati, NCR

### 3. **David Wilson** - Tire Rotation Specialist
- **Username:** `david.wilson`
- **Email:** `david.wilson@vehicare.com`
- **Phone:** 09171234563
- **Password:** `Tech@123`
- **Status:** Active
- **Specialization:** Tire Rotation & Wheel Balancing
- **Location:** Quezon City, NCR

### 4. **Carlos Martinez** - Battery Specialist
- **Username:** `carlos.martinez`
- **Email:** `carlos.martinez@vehicare.com`
- **Phone:** 09171234564
- **Password:** `Tech@123`
- **Status:** Inactive (Currently Busy)
- **Specialization:** Battery Installation & Diagnostics
- **Location:** Cebu, Visayas

### 5. **Robert Brown** - Engine Diagnostics Specialist
- **Username:** `robert.brown`
- **Email:** `robert.brown@vehicare.com`
- **Phone:** 09171234565
- **Password:** `Tech@123`
- **Status:** Active
- **Specialization:** Engine Diagnostics & Performance Analysis
- **Location:** Pasig, NCR

### 6. **James Anderson** - Air Filter Specialist
- **Username:** `james.anderson`
- **Email:** `james.anderson@vehicare.com`
- **Phone:** 09171234566
- **Password:** `Tech@123`
- **Status:** Active
- **Specialization:** Air Filter Replacement & Cabin Filters
- **Location:** Antipolo, NCR

### 7. **Thomas Lee** - Suspension Specialist
- **Username:** `thomas.lee`
- **Email:** `thomas.lee@vehicare.com`
- **Phone:** 09171234567
- **Password:** `Tech@123`
- **Status:** Active
- **Specialization:** Suspension Service & Alignment
- **Location:** Davao, Mindanao

### 8. **Patricia Garcia** - Coolant Flush Specialist
- **Username:** `patricia.garcia`
- **Email:** `patricia.garcia@vehicare.com`
- **Phone:** 09171234568
- **Password:** `Tech@123`
- **Status:** Active
- **Specialization:** Coolant Flush & Radiator Service
- **Location:** Cavite, CALABARZON

---

## How to Add These Accounts to Your Database

### Option 1: Run SQL Script
Execute the `ADD_TECHNICIANS.sql` file in your database:

```bash
mysql -u root -p vehicare_db < ADD_TECHNICIANS.sql
```

### Option 2: Manual Insert
Copy and paste the SQL INSERT statements from `ADD_TECHNICIANS.sql` into your phpMyAdmin or database management tool.

### Option 3: Admin Panel
Use the VehiCare admin panel to manually create staff accounts:
1. Go to Admin Dashboard
2. Navigate to Staff Management
3. Add New Staff
4. Fill in the technician details

---

## Technician Dashboard Features

Once logged in, each technician has access to:

### **Kanban Board View** (4 Columns)
1. **Pending** - Appointments waiting to be started
2. **In Progress** - Currently working appointments
3. **Completed** - Finished appointments
4. **Cancelled** - Cancelled appointments

### **Appointment Card Details**
- Service name
- Appointment date & time
- Client name and phone number
- Quick action buttons:
  - **Start** - Move appointment to "In Progress"
  - **Complete** - Mark appointment as "Completed"
  - **Details** - View full appointment information

### **Statistics Dashboard**
- Total pending appointments
- Total in-progress appointments
- Total completed appointments

---

## Login Instructions

### For Technicians:
1. Go to: `http://localhost/vehicare_db/login.php`
2. Enter username (e.g., `john.smith`)
3. Enter password: `Tech@123`
4. Click "Login"
5. You'll be redirected to your technician dashboard at: `/staff/dashboard.php`

### For Admin/Staff Management:
1. Go to: `http://localhost/vehicare_db/admins/index.php`
2. Login with admin credentials
3. Navigate to Staff section to manage technician accounts

---

## Changing Default Passwords

It's recommended to change the default password after first login.

**To change password:**
1. Update the database directly (use password_hash('NewPassword', PASSWORD_BCRYPT))
2. Or use the profile/settings page if available

---

## Database Schema

The technicians are stored in the `users` table with:
- `role = 'staff'`
- `status = 'active'` or `'inactive'`
- Full contact information
- Encrypted passwords using bcrypt hashing

---

## Troubleshooting

### Login Issues
- Verify username/email is correct (case-sensitive)
- Ensure password is exactly: `Tech@123`
- Check if account status is 'active' in database

### Dashboard Not Showing Appointments
- Ensure appointments are assigned to the technician via `assignments` table
- Check if appointment has a valid `status` field
- Verify service_id and client_id references exist

### Permission Denied Errors
- Verify user role is 'staff' in database
- Check session is properly set after login
- Clear browser cookies/cache and try again

---

## Support

For technical support or issues, contact the system administrator.

Last Updated: January 31, 2026
