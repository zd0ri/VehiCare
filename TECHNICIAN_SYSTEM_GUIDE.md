# VehiCare Technician System Implementation Guide

## 📋 Overview

The VehiCare system now includes a complete **Technician Management System** with:
- ✅ 8 pre-configured technician accounts
- ✅ Kanban-style booking dashboard
- ✅ Real-time appointment status management
- ✅ Client information display
- ✅ Service specialization assignment

---

## 🚀 Getting Started

### Step 1: Import Technician Accounts

Add the 8 technician accounts to your database by running the SQL script:

**File Location:** `vehicare_db/ADD_TECHNICIANS.sql`

#### Using MySQL Command Line:
```bash
mysql -u root -p vehicare_db < ADD_TECHNICIANS.sql
```

#### Using phpMyAdmin:
1. Open phpMyAdmin
2. Select `vehicare_db` database
3. Click "Import" tab
4. Choose `ADD_TECHNICIANS.sql` file
5. Click "Import"

#### Using MySQL Workbench:
1. Open MySQL Workbench
2. File → Open SQL Script
3. Select `ADD_TECHNICIANS.sql`
4. Execute the script

---

## 👥 Technician Accounts

All accounts use the default password: **`Tech@123`**

| # | Name | Username | Email | Specialization | Status |
|---|------|----------|-------|-----------------|--------|
| 1 | John Smith | john.smith | john.smith@vehicare.com | Oil Change & Filter | Active |
| 2 | Mike Johnson | mike.johnson | mike.johnson@vehicare.com | Brake Service | Active |
| 3 | David Wilson | david.wilson | david.wilson@vehicare.com | Tire Rotation | Active |
| 4 | Carlos Martinez | carlos.martinez | carlos.martinez@vehicare.com | Battery Service | **Inactive** |
| 5 | Robert Brown | robert.brown | robert.brown@vehicare.com | Engine Diagnostics | Active |
| 6 | James Anderson | james.anderson | james.anderson@vehicare.com | Air Filter | Active |
| 7 | Thomas Lee | thomas.lee | thomas.lee@vehicare.com | Suspension Service | Active |
| 8 | Patricia Garcia | patricia.garcia | patricia.garcia@vehicare.com | Coolant Flush | Active |

---

## 🔑 Login Instructions

### For Technicians:

**URL:** `http://localhost/vehicare_db/login.php`

1. Enter **Username** or **Email**
   - Example: `john.smith` or `john.smith@vehicare.com`

2. Enter **Password:** `Tech@123`

3. Click **Login**

4. You'll be redirected to: `/staff/dashboard.php`

---

## 📊 Technician Dashboard Features

### Header Section
- Technician avatar with initials
- Full name and email display
- Logout button

### Statistics Cards
- **Pending Appointments** - Awaiting action
- **In Progress** - Currently working on
- **Completed** - Finished today

### Kanban Board (4 Columns)

#### 1. **Pending Column** 🟡
- New appointments not yet started
- **Quick Actions:**
  - **Start** - Moves appointment to "In Progress"
  - **Details** - View full appointment information

#### 2. **In Progress Column** 🔵
- Currently being serviced
- **Quick Actions:**
  - **Complete** - Mark as finished
  - **Details** - View appointment details

#### 3. **Completed Column** 🟢
- Successfully finished appointments
- Shows completed work history

#### 4. **Cancelled Column** 🔴
- Cancelled or rejected appointments
- Reference for bookkeeping

### Appointment Card Details
Each appointment card displays:
- 🔧 Service name
- 📅 Date (formatted: "Jan 15, 2025")
- ⏰ Time (formatted: "02:00 PM")
- 👤 Client name
- 📱 Client phone number

---

## 🔄 Workflow

### Typical Booking Management Process:

```
1. PENDING STATE
   ├─ Technician reviews appointment
   ├─ Checks client details and service requirements
   └─ Clicks "Start" button

2. IN PROGRESS STATE
   ├─ Technician works on appointment
   ├─ Can view all appointment details
   └─ Upon completion, clicks "Complete"

3. COMPLETED STATE
   ├─ Appointment marked as finished
   ├─ No further action needed
   └─ Stored in completed history

4. CANCELLED STATE
   ├─ If cancelled by client/admin
   ├─ Appears in cancelled section
   └─ Reference only
```

---

## 📁 File Structure

### New/Updated Files:

```
vehicare_db/
├── ADD_TECHNICIANS.sql              ✨ NEW - Technician account creation script
├── TECHNICIAN_CREDENTIALS.md        ✨ NEW - Credentials reference
├── TECHNICIAN_SYSTEM_GUIDE.md       ✨ NEW - This file
├── staff/
│   ├── dashboard.php                ✏️ UPDATED - Kanban dashboard
│   ├── update_appointment_status.php ✨ NEW - Status update endpoint
│   └── ...
├── includes/
│   └── config.php                   (No changes needed)
└── ...
```

---

## 🗄️ Database Schema

### Users Table (Technician Row)
```sql
INSERT INTO users (
    username, 
    email, 
    password,          -- bcrypt hashed "Tech@123"
    full_name, 
    phone, 
    role,              -- 'staff'
    status,            -- 'active' or 'inactive'
    created_date,
    profile_picture,
    address,
    city,
    state,
    zip_code
) VALUES (...)
```

### Appointments Table
```sql
SELECT 
    appointment_id,
    client_id,
    service_id,
    appointment_date,
    appointment_time,
    status,            -- pending, in-progress, completed, cancelled
    type
FROM appointments
```

### Assignments Table (Links Technician to Appointment)
```sql
SELECT 
    assignment_id,
    appointment_id,
    staff_id,          -- References users.user_id
    assigned_date
FROM assignments
```

---

## 🔧 Admin Management

### Managing Technician Accounts

**Location:** Admin Panel → Staff Management

### Access Technician List:
1. Go to `http://localhost/vehicare_db/admins/dashboard.php`
2. Login with admin credentials
3. Click "Staff" or "Technicians" section
4. View/Edit/Delete technician accounts

### Create New Technician:
1. Navigate to Staff Management
2. Click "Add New Staff"
3. Fill in details:
   - Full Name
   - Email
   - Phone
   - Password (will be hashed)
   - Status (active/inactive)
4. Save

### Edit Technician Status:
1. Select technician from list
2. Toggle status: Active ↔ Inactive
3. Inactive technicians cannot login or receive appointments

---

## 🎨 Customization

### Change Technician Specializations

Edit in `services.php` (service cards section):

```php
$services = [
    [
        'name' => 'Oil Change & Filter',
        'technician' => 'John Smith',  // Change name here
        'tech_initial' => 'JS',
        'available' => true,            // Set availability
        // ...
    ],
    // ... more services
];
```

### Customize Dashboard Colors

Edit styles in `staff/dashboard.php`:

```css
.stat-icon.pending {
    background: #fff3cd;  /* Change yellow */
    color: #ff9800;
}

.stat-icon.in-progress {
    background: #cfe2ff;  /* Change blue */
    color: #0066cc;
}

.btn-start {
    background: #0066cc;  /* Change action button color */
}
```

---

## 🐛 Troubleshooting

### Issue: "Account is inactive"
**Solution:** 
- Check database: `SELECT status FROM users WHERE username = 'john.smith'`
- Update status: `UPDATE users SET status = 'active' WHERE username = 'john.smith'`

### Issue: "Invalid email or password"
**Verify:**
- Username/email is correct
- Password is exactly: `Tech@123`
- Account exists in database

### Issue: "Appointment not found on dashboard"
**Check:**
- Appointment is assigned to technician in `assignments` table
- Service and client records exist
- Appointment date is valid

### Issue: "Dashboard not loading"
**Debug:**
- Clear browser cache
- Check browser console for errors
- Verify database connection in `includes/config.php`
- Ensure `staff/dashboard.php` has proper permissions

---

## 📝 Default Password Security

⚠️ **IMPORTANT:** Change default password after first login!

### To Change Password:

**Via Database:**
```sql
-- Generate new hash first (use PHP):
-- $hash = password_hash('NewPassword123', PASSWORD_BCRYPT);

UPDATE users 
SET password = '$2y$10$...(hashed_password_here)...' 
WHERE user_id = 1;
```

**Via Profile Settings** (if available):
1. Login to technician dashboard
2. Go to Profile/Settings
3. Click "Change Password"
4. Enter old password and new password
5. Save

---

## 📊 Reporting & Analytics

### View Technician Performance:

**SQL Query to get completed appointments per technician:**
```sql
SELECT 
    u.full_name,
    COUNT(a.appointment_id) as completed_count,
    MAX(a.appointment_date) as last_completed
FROM users u
JOIN assignments ass ON u.user_id = ass.staff_id
JOIN appointments a ON ass.appointment_id = a.appointment_id
WHERE a.status = 'completed'
GROUP BY u.user_id
ORDER BY completed_count DESC;
```

---

## 🔐 Security Considerations

1. **Change default passwords** after deployment
2. **Use HTTPS** in production
3. **Validate** all user inputs
4. **Log** all status changes
5. **Restrict** admin access to staff management
6. **Backup** database regularly

---

## 📞 Support & Contact

For issues or questions:
- Check this guide first
- Review error messages in browser console
- Check database for data integrity
- Contact system administrator

---

## 📅 Version History

| Date | Version | Changes |
|------|---------|---------|
| Jan 31, 2026 | 1.0 | Initial technician system implementation |

---

**Last Updated:** January 31, 2026
**System:** VehiCare v1.0
**Database:** vehicare_db
