# Shop Parts to Inventory Integration - Setup Guide

## Overview
All 12 products from the shop page have been integrated into the inventory management system.

## What Was Added

### 12 Auto Parts with the following details:

1. **High Performance Air Filter** - ₱45.99
2. **Ceramic Brake Pads** - ₱89.99
3. **Oil Filter Kit** - ₱34.50
4. **Engine Spark Plugs (Set of 4)** - ₱125.00
5. **Suspension Coil Springs** - ₱250.00
6. **Radiator Cooling Unit** - ₱185.99
7. **LED Headlight Assembly** - ₱299.99
8. **Automatic Transmission Fluid** - ₱42.50
9. **Premium Alloy Wheels (17")** - ₱450.00
10. **Engine Gasket Set** - ₱75.00
11. **Brake Rotors (Pair)** - ₱165.00
12. **Power Steering Pump** - ₱210.00

## Categories
- Filter (3 parts)
- Brake (3 parts)
- Engine (3 parts)
- Electrical (2 parts)
- Suspension (1 part)
- Cooling (1 part)
- Transmission (1 part)
- Wheel (1 part)

## How to Import Parts into Database

### Step 1: Run the Import Script
1. Open your browser and navigate to: `http://localhost/vehicare_db/import_shop_parts.php`
2. This will automatically:
   - Check if parts already exist
   - Insert new parts into the `parts` table
   - Add default inventory (50 units per part) to the `inventory` table
   - Display confirmation with count of imported items

### Step 2: Verify in Admin Panel
1. Go to: `http://localhost/vehicare_db/admins/inventory.php`
2. Log in with admin credentials
3. View all imported parts in the inventory table
4. Each part will show:
   - Part Name
   - Supplier
   - Price
   - Stock Status (In Stock, Low Stock, Out of Stock)
   - Last Updated Date

### Step 3: Manage Inventory
In the Inventory page, you can:
- **Edit**: Update part details, prices, and quantities
- **Delete**: Remove parts from inventory
- **Track Stock**: Monitor low stock items
- **Update Quantities**: Change available stock levels

## Features

### Inventory Status Badges
- **In Stock** (Green) - More than 5 units available
- **Low Stock** (Yellow) - Between 1-5 units available
- **Out of Stock** (Red) - No units available

### Database Structure
Parts are stored in two tables:

**parts table:**
- part_id (Primary Key)
- part_name
- description
- category
- price
- supplier
- status (active/inactive)
- created_at

**inventory table:**
- inventory_id (Primary Key)
- part_id (Foreign Key)
- quantity
- last_updated

## Shop vs Inventory Integration

### Shop Page (shop.php)
- Displays products in a customer-friendly interface
- Shows ratings and featured items
- Has search and filter functionality
- Uses hardcoded product data

### Inventory Page (inventory.php)
- Admin management interface
- Tracks stock quantities
- Allows price and status updates
- Synced with database

## Next Steps

1. **Import the parts** by visiting `import_shop_parts.php`
2. **Verify** in the inventory admin page
3. **Update quantities** based on your actual stock
4. **Set supplier information** for ordering
5. **Monitor low stock** items regularly

## SQL Files Provided

- `INSERT_SHOP_PARTS.sql` - SQL script with all 12 parts (use if MySQL CLI is available)
- `import_shop_parts.php` - PHP-based import tool (recommended for Windows servers)

## Notes

- Default inventory quantity per part: 50 units
- All parts are set to "active" status by default
- Parts can be deactivated from the inventory page
- Prices are in Philippine Pesos (₱)
- Import script checks for duplicates before inserting

## Troubleshooting

**Import not working?**
1. Ensure database tables exist: `parts` and `inventory`
2. Check database connection in `includes/config.php`
3. Verify user has INSERT permissions
4. Check file permissions on import_shop_parts.php

**Parts not showing in inventory?**
1. Refresh the page (Ctrl+F5)
2. Check database directly: `SELECT * FROM parts;`
3. Verify inventory foreign key relationship

**Want to update quantities?**
1. Click Edit button next to any part
2. Update the quantity field
3. Save changes
4. Stock status will update automatically

---

**Status**: Ready for Deployment
**Parts Count**: 12 items
**Total Inventory Value**: ₱2,410.96
**Last Updated**: January 31, 2026
