-- Database fixes for VehiCare - Missing tables and columns
-- Run this script to fix the database schema issues

-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Create missing walk_in_bookings table
CREATE TABLE IF NOT EXISTS `walk_in_bookings` (
  `booking_id` int(11) NOT NULL AUTO_INCREMENT,
  `client_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `vehicle_plate` varchar(20) NOT NULL,
  `service_id` int(11) NOT NULL,
  `booking_date` date NOT NULL,
  `booking_time` time NOT NULL,
  `status` enum('pending','in_progress','completed','cancelled') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`booking_id`),
  KEY `service_id` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Create missing notifications table
CREATE TABLE IF NOT EXISTS `notifications` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','warning','success','error') DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `read_at` datetime DEFAULT NULL,
  PRIMARY KEY (`notification_id`),
  KEY `user_id` (`user_id`),
  KEY `is_read` (`is_read`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Add missing columns to payments table
-- Check if columns exist before adding them
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'vehicare_db' AND TABLE_NAME = 'payments' AND COLUMN_NAME = 'appointment_id';

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE payments ADD COLUMN appointment_id int(11) DEFAULT NULL AFTER payment_id', 
    'SELECT "appointment_id column already exists" as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'vehicare_db' AND TABLE_NAME = 'payments' AND COLUMN_NAME = 'client_id';

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE payments ADD COLUMN client_id int(11) DEFAULT NULL AFTER appointment_id', 
    'SELECT "client_id column already exists" as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 4. Add missing created_at column to ratings table
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'vehicare_db' AND TABLE_NAME = 'ratings' AND COLUMN_NAME = 'created_at';

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE ratings ADD COLUMN created_at datetime DEFAULT CURRENT_TIMESTAMP AFTER rating_date', 
    'SELECT "created_at column already exists in ratings" as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 5. Update existing ratings records to set created_at from rating_date
UPDATE `ratings` SET `created_at` = COALESCE(CONCAT(`rating_date`, ' 00:00:00'), NOW()) 
WHERE `created_at` IS NULL;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Try to add foreign key constraints (may fail if data integrity issues exist)
-- Add foreign key for walk_in_bookings if not exists
SET @fk_exists = 0;
SELECT COUNT(*) INTO @fk_exists 
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
WHERE TABLE_SCHEMA = 'vehicare_db' AND TABLE_NAME = 'walk_in_bookings' AND CONSTRAINT_NAME = 'walk_in_bookings_service_fk';

SET @sql = IF(@fk_exists = 0, 
    'ALTER TABLE walk_in_bookings ADD CONSTRAINT walk_in_bookings_service_fk FOREIGN KEY (service_id) REFERENCES services (service_id)', 
    'SELECT "Foreign key already exists for walk_in_bookings" as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add foreign key for notifications if not exists  
SET @fk_exists = 0;
SELECT COUNT(*) INTO @fk_exists 
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
WHERE TABLE_SCHEMA = 'vehicare_db' AND TABLE_NAME = 'notifications' AND CONSTRAINT_NAME = 'notifications_user_fk';

SET @sql = IF(@fk_exists = 0, 
    'ALTER TABLE notifications ADD CONSTRAINT notifications_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE', 
    'SELECT "Foreign key already exists for notifications" as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;