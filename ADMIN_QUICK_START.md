# VehiCare Admin System - Quick Start Guide

## 🚀 Get Started in 3 Steps

### Step 1: Update Database Schema
Run the database updates to create all required tables and columns:

```bash
mysql -u root vehicare_db < database_updates.sql
```

### Step 2: Access Admin Dashboard
Open your browser and go to:
```
http://localhost/vehicare_db/admins/index.php
```

Log in with your admin credentials.

### Step 3: Start Using the System
Click on any module in the left sidebar to start managing:

---

## 📍 Main Dashboard
**URL:** `/vehicare_db/admins/index.php`
- View key statistics
- See recent appointments
- Check pending notifications
- Quick access to all modules

---

## 📅 Appointments
**URL:** `/vehicare_db/admins/appointments.php`
- Schedule new appointments
- Update appointment status
- Assign technicians
- Delete canceled appointments

**Status Options:**
- Pending (awaiting confirmation)
- Confirmed (approved)
- In Progress (being serviced)
- Completed (finished)
- Cancelled (rejected)

---

## 🚪 Walk-In Bookings
**URL:** `/vehicare_db/admins/walk_in_booking.php`
- Register walk-in customers
- Quick booking without pre-registration
- Same features as regular appointments

---

## 👥 Client Management
**URL:** `/vehicare_db/admins/clients.php`
- Add new clients
- Edit client details
- Delete clients
- View contact information
- Manage client status

---

## 🚗 Vehicles
**URL:** `/vehicare_db/admins/vehicles.php`
- View all registered vehicles
- Link vehicles to clients
- Track vehicle details
- Manage registration info

---

## 🔧 Technicians
**URL:** `/vehicare_db/admins/technicians.php`
- Add technician profiles
- View technician ratings
- Manage specializations
- Track performance

---

## 📋 Technician Assignments
**URL:** `/vehicare_db/admins/assignments.php`
- Assign technicians to appointments
- View current workload
- Track assignment history

---

## ⏱️ Queue Management
**URL:** `/vehicare_db/admins/queue.php`
- View service queue
- Mark services as complete
- Send queue notifications
- Display queue on public screen

---

## 🛠️ Inventory Management
**URL:** `/vehicare_db/admins/inventory.php`
- Add parts and equipment
- Track stock levels
- Update prices
- Monitor low stock items
- Delete obsolete items

**Stock Status Badges:**
- Green: In Stock (5+)
- Yellow: Low Stock (1-4)
- Red: Out of Stock (0)

---

## 💳 Payments
**URL:** `/vehicare_db/admins/payments.php`
- View all payments received
- Check payment methods
- Update payment status
- Filter by client or date

---

## 📄 Invoices & Billing
**URL:** `/vehicare_db/admins/invoices.php`
- Generate invoices
- Track invoice status
- Print invoices
- Monitor payment collection
- View labor and parts costs

---

## ⭐ Ratings & Reports
**URL:** `/vehicare_db/admins/ratings.php`
- View technician ratings
- Read client feedback
- Track average ratings
- Identify top performers
- Improve service quality

---

## 🔔 Notifications
**URL:** `/vehicare_db/admins/notifications.php`
- Send messages to clients
- Choose notification type
- Track sent messages
- Notify about service completion

**Notification Types:**
- Info (general updates)
- Warning (attention needed)
- Success (completed tasks)
- Alert (urgent messages)

---

## 📊 Audit Logs
**URL:** `/vehicare_db/admins/audit_logs.php`
- View all system activities
- Track admin actions
- Check activity timestamps
- Monitor user changes
- Ensure compliance

---

## 🔑 Common Tasks

### Book an Appointment
1. Go to **Appointments**
2. Click **"New Appointment"**
3. Select client, vehicle, service
4. Choose date and time
5. Save

### Add a New Client
1. Go to **Clients**
2. Click **"New Client"**
3. Enter full name, email, phone
4. Add address (optional)
5. Save

### Assign a Technician
1. Go to **Technician Assignments**
2. Click **"New Assignment"**
3. Select appointment and technician
4. Confirm

### Send a Notification
1. Go to **Notifications**
2. Click **"Send Notification"**
3. Select recipient
4. Write title and message
5. Send

### Generate Invoice
1. Go to **Invoices**
2. Find the appointment
3. Click **"View"**
4. Click **"Generate"** or **"Print"**

---

## 💡 Tips & Tricks

### Bulk Actions
- Most lists support status updates via dropdown
- Delete with confirmation to prevent accidents

### Status Filters
- Click status badges to filter views
- Use date ranges for reports

### Search & Sort
- Tables are sortable by clicking headers
- Use search fields where available

### Responsive Design
- Works on desktop, tablet, and mobile
- Sidebar collapses on small screens

---

## 🔐 Access Control

### Admin-Only Pages
- All admin pages require admin login
- Redirects to login if not authenticated
- Sessions expire after inactivity

### Audit Trail
- Every action is logged
- Track who did what and when
- Compliance ready

---

## 🎯 Key Metrics on Dashboard

| Metric | Location | Purpose |
|--------|----------|---------|
| Active Clients | Dashboard | Customer base size |
| Pending Appointments | Dashboard | Workload overview |
| Queue Pending | Dashboard | Current service queue |
| Active Technicians | Dashboard | Staff availability |
| Pending Payments | Dashboard | Revenue tracking |
| Total Revenue | Dashboard | Business performance |

---

## 📞 Support Features

### Built-in Help
- Hover over buttons for tooltips
- Badges show status clearly
- Forms have clear labels

### Data Validation
- Required fields are marked with *
- Invalid entries show error messages
- Confirmation dialogs for deletions

---

## 🔄 Workflow Example

**Customer Service Flow:**

1. **Customer Arrives** → Register in Walk-In Bookings
2. **Assess Needs** → Create/Confirm Appointment
3. **Assign Staff** → Add to Technician Assignments
4. **Queue Management** → Add to Queue
5. **Service Completion** → Update Appointment Status
6. **Billing** → Generate Invoice
7. **Payment** → Record Payment
8. **Feedback** → Collect Rating
9. **Follow-up** → Send Notification
10. **Records** → Service History Created (auto)

---

## 📱 Mobile Access

The admin system works on mobile devices:
- Touch-friendly buttons
- Responsive tables
- Full functionality
- Sidebar toggles on small screens

---

## ⚠️ Important Notes

1. **Always Backup:** Regularly backup your database
2. **Audit Logs:** Check audit logs regularly for security
3. **User Permissions:** Only admins can access these pages
4. **Data Integrity:** Confirmations prevent accidental deletions
5. **Session Security:** Log out when leaving the computer

---

## 🎓 Learning Path

**Day 1:** Familiarize with dashboard and client management
**Day 2:** Schedule appointments and assignments
**Day 3:** Process payments and generate invoices
**Day 4:** Use notifications and ratings
**Day 5:** Manage inventory and review audit logs

---

## ❓ Frequently Asked Questions

**Q: How do I reset a password?**
A: Go to Clients, find the user, click Edit, reset password.

**Q: Can I delete an appointment?**
A: Yes, but it's better to mark as Cancelled. Use delete only for errors.

**Q: How are invoices created?**
A: Automatically when an appointment is completed. Manually add labor and parts costs.

**Q: Who can access this system?**
A: Only logged-in admins. Other users can't access `/admins/` pages.

**Q: Where are audit logs stored?**
A: In the `audittrail` table. View via Audit Logs page.

---

**Ready to manage VehiCare?** Start with the dashboard! 🚀
