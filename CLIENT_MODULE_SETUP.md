# VehiCare Client Module - Database Setup Instructions

## 🚨 **IMPORTANT: Database Tables Missing**

The client dashboard error occurs because the required database tables haven't been created yet. Follow these steps to fix this:

## 📋 **Quick Fix Steps:**

### **Option 1: Using MySQL Command Line (Recommended)**

1. **Open Command Prompt or PowerShell as Administrator**

2. **Navigate to your VehiCare directory:**
   ```
   cd C:\TAGUIG-2025\htdocs\vehicare_db
   ```

3. **Import the SQL file using MySQL command line:**
   ```
   mysql -u root -p vehicare_db < client_module_tables.sql
   ```
   
   *Enter your MySQL root password when prompted*

### **Option 2: Using phpMyAdmin (Web Interface)**

1. **Open phpMyAdmin in your browser:** http://localhost/phpmyadmin
2. **Log in with your MySQL credentials** (usually root with no password)
3. **Select the 'vehicare_db' database** from the left sidebar
4. **Click on the 'Import' tab**
5. **Choose the file:** `C:\TAGUIG-2025\htdocs\vehicare_db\client_module_tables.sql`
6. **Click 'Go' to execute the import**

### **Option 3: Using MySQL Workbench**

1. **Open MySQL Workbench**
2. **Connect to your local MySQL server**
3. **Open the vehicare_db schema**
4. **Go to File → Open SQL Script**
5. **Select:** `C:\TAGUIG-2025\htdocs\vehicare_db\client_module_tables.sql`
6. **Click the Execute button (⚡️)**

## ✅ **What This Will Create:**

The SQL file will create these essential tables:

- **`invoices`** - Client invoicing (adds client_id column if missing)
- **`payments`** - Payment tracking and history
- **`reviews`** - Client feedback and ratings
- **`notifications`** - Client notifications system
- **`maintenance_reminders`** - Vehicle maintenance tracking
- **`user_preferences`** - Client notification preferences
- **`client_activity_logs`** - Activity tracking for security

## 🧪 **Testing the Fix:**

After importing the SQL file:

1. **Visit:** http://localhost/vehicare_db/client/dashboard.php
2. **Log in as a client** (or create a client account)
3. **The dashboard should now load without errors**

## 📊 **Expected Dashboard Features:**

✅ **Dashboard Statistics** - Appointment counts, vehicle counts, etc.
✅ **Appointment Management** - Book, view, cancel appointments
✅ **Vehicle Management** - Add, view, manage vehicles
✅ **Billing System** - View invoices, payment history
✅ **Reviews & Ratings** - Submit feedback for services
✅ **Account Management** - Profile, preferences, activity logs

## 🔧 **If You Still Get Errors:**

1. **Check MySQL is running:**
   ```
   mysqladmin -u root -p ping
   ```

2. **Verify database exists:**
   ```
   mysql -u root -p -e "SHOW DATABASES;"
   ```

3. **Check table creation:**
   ```
   mysql -u root -p -e "USE vehicare_db; SHOW TABLES;"
   ```

4. **Test a simple query:**
   ```
   mysql -u root -p -e "USE vehicare_db; SELECT COUNT(*) FROM invoices;"
   ```

## 📱 **Sample Data Included:**

The SQL file includes some sample data for testing:
- Sample invoices
- Sample notifications
- Default user preferences

## 🆘 **Need Help?**

If you encounter any issues:

1. **Check MySQL error logs** for specific error messages
2. **Ensure PHP has MySQL extension enabled** (`php -m | findstr mysql`)
3. **Verify database credentials** in `includes/config.php`
4. **Make sure MySQL service is running**

## 🎯 **Next Steps:**

Once the database is set up:
1. Test all client dashboard features
2. Create sample appointments and vehicles
3. Test the payment and review systems
4. Customize the client interface as needed

---

**✨ The client module provides a complete, modern interface for VehiCare customers with professional design and comprehensive functionality!**