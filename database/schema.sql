-- VehiCare Database Schema
-- Complete database structure for vehicle service management system
-- Created: February 2026

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- ================================================
-- 1. USERS & AUTHENTICATION
-- ================================================

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `role` enum('admin','staff','client') NOT NULL DEFAULT 'client',
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `profile_picture` varchar(255) DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  INDEX `idx_email` (`email`),
  INDEX `idx_role` (`role`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- 2. VEHICLES MANAGEMENT
-- ================================================

DROP TABLE IF EXISTS `vehicles`;
CREATE TABLE `vehicles` (
  `vehicle_id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `plate_number` varchar(20) NOT NULL UNIQUE,
  `car_brand` varchar(100) NOT NULL,
  `car_model` varchar(100) NOT NULL,
  `year_model` year(4) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `engine_type` varchar(50) DEFAULT NULL,
  `transmission_type` enum('manual','automatic') DEFAULT NULL,
  `mileage` int(11) DEFAULT NULL,
  `vin_number` varchar(50) DEFAULT NULL,
  `insurance_info` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('active','inactive','archived') DEFAULT 'active',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`vehicle_id`),
  UNIQUE KEY `unique_plate` (`plate_number`),
  KEY `fk_vehicle_client` (`client_id`),
  CONSTRAINT `fk_vehicle_client` FOREIGN KEY (`client_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- 3. SERVICES CATALOG
-- ================================================

DROP TABLE IF EXISTS `services`;
CREATE TABLE `services` (
  `service_id` int(11) NOT NULL AUTO_INCREMENT,
  `service_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `estimated_duration` int(11) DEFAULT NULL COMMENT 'Duration in minutes',
  `base_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `labor_cost` decimal(10,2) DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `requires_parts` tinyint(1) DEFAULT 0,
  `skill_level` enum('basic','intermediate','advanced','expert') DEFAULT 'basic',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`service_id`),
  INDEX `idx_category` (`category`),
  INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- 4. APPOINTMENTS & BOOKINGS
-- ================================================

DROP TABLE IF EXISTS `appointments`;
CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `technician_id` int(11) DEFAULT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `estimated_completion` datetime DEFAULT NULL,
  `actual_completion` datetime DEFAULT NULL,
  `status` enum('pending','confirmed','in_progress','completed','cancelled','no_show') NOT NULL DEFAULT 'pending',
  `priority` enum('low','normal','high','urgent') DEFAULT 'normal',
  `customer_notes` text DEFAULT NULL,
  `technician_notes` text DEFAULT NULL,
  `total_estimated_cost` decimal(10,2) DEFAULT 0.00,
  `actual_cost` decimal(10,2) DEFAULT 0.00,
  `payment_status` enum('unpaid','partial','paid','refunded') DEFAULT 'unpaid',
  `booking_type` enum('online','walk_in','phone','recurring') DEFAULT 'online',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`appointment_id`),
  KEY `fk_appointment_client` (`client_id`),
  KEY `fk_appointment_vehicle` (`vehicle_id`),
  KEY `fk_appointment_service` (`service_id`),
  KEY `fk_appointment_technician` (`technician_id`),
  INDEX `idx_date_time` (`appointment_date`, `appointment_time`),
  INDEX `idx_status` (`status`),
  CONSTRAINT `fk_appointment_client` FOREIGN KEY (`client_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `fk_appointment_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`vehicle_id`),
  CONSTRAINT `fk_appointment_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`),
  CONSTRAINT `fk_appointment_technician` FOREIGN KEY (`technician_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- 5. TECHNICIAN ASSIGNMENTS
-- ================================================

DROP TABLE IF EXISTS `assignments`;
CREATE TABLE `assignments` (
  `assignment_id` int(11) NOT NULL AUTO_INCREMENT,
  `appointment_id` int(11) NOT NULL,
  `technician_id` int(11) NOT NULL,
  `assigned_by` int(11) NOT NULL,
  `assigned_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `status` enum('assigned','accepted','in_progress','completed','cancelled') DEFAULT 'assigned',
  `priority` enum('low','normal','high','urgent') DEFAULT 'normal',
  `special_instructions` text DEFAULT NULL,
  `completion_notes` text DEFAULT NULL,
  `quality_rating` int(1) DEFAULT NULL COMMENT '1-5 rating',
  `estimated_hours` decimal(4,2) DEFAULT NULL,
  `actual_hours` decimal(4,2) DEFAULT NULL,
  PRIMARY KEY (`assignment_id`),
  KEY `fk_assignment_appointment` (`appointment_id`),
  KEY `fk_assignment_technician` (`technician_id`),
  KEY `fk_assignment_assigned_by` (`assigned_by`),
  INDEX `idx_status` (`status`),
  INDEX `idx_dates` (`assigned_date`),
  CONSTRAINT `fk_assignment_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_assignment_technician` FOREIGN KEY (`technician_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `fk_assignment_assigned_by` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- 6. QUEUE MANAGEMENT
-- ================================================

DROP TABLE IF EXISTS `queue`;
CREATE TABLE `queue` (
  `queue_id` int(11) NOT NULL AUTO_INCREMENT,
  `appointment_id` int(11) DEFAULT NULL,
  `client_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `queue_number` varchar(20) NOT NULL,
  `entry_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estimated_wait_time` int(11) DEFAULT NULL COMMENT 'Minutes',
  `actual_wait_time` int(11) DEFAULT NULL,
  `priority` enum('low','normal','high','urgent') DEFAULT 'normal',
  `status` enum('waiting','called','in_service','completed','cancelled','no_show') DEFAULT 'waiting',
  `called_time` datetime DEFAULT NULL,
  `service_start_time` datetime DEFAULT NULL,
  `service_complete_time` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`queue_id`),
  UNIQUE KEY `unique_queue_number_date` (`queue_number`, `entry_time`),
  KEY `fk_queue_appointment` (`appointment_id`),
  KEY `fk_queue_client` (`client_id`),
  KEY `fk_queue_vehicle` (`vehicle_id`),
  KEY `fk_queue_service` (`service_id`),
  INDEX `idx_status_entry` (`status`, `entry_time`),
  CONSTRAINT `fk_queue_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`),
  CONSTRAINT `fk_queue_client` FOREIGN KEY (`client_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `fk_queue_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`vehicle_id`),
  CONSTRAINT `fk_queue_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- 7. INVENTORY MANAGEMENT
-- ================================================

DROP TABLE IF EXISTS `inventory`;
CREATE TABLE `inventory` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_name` varchar(255) NOT NULL,
  `item_code` varchar(100) UNIQUE DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cost_price` decimal(10,2) DEFAULT 0.00,
  `current_stock` int(11) NOT NULL DEFAULT 0,
  `minimum_stock` int(11) DEFAULT 0,
  `maximum_stock` int(11) DEFAULT NULL,
  `reorder_point` int(11) DEFAULT NULL,
  `supplier_info` text DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `unit_of_measure` varchar(50) DEFAULT 'pcs',
  `is_service_item` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`item_id`),
  UNIQUE KEY `unique_item_code` (`item_code`),
  INDEX `idx_category` (`category`),
  INDEX `idx_stock_levels` (`current_stock`, `minimum_stock`),
  INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- 8. INVOICING & PAYMENTS
-- ================================================

DROP TABLE IF EXISTS `invoices`;
CREATE TABLE `invoices` (
  `invoice_id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(50) NOT NULL UNIQUE,
  `appointment_id` int(11) DEFAULT NULL,
  `client_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax_rate` decimal(5,2) DEFAULT 0.00,
  `tax_amount` decimal(10,2) DEFAULT 0.00,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `paid_amount` decimal(10,2) DEFAULT 0.00,
  `balance_due` decimal(10,2) DEFAULT 0.00,
  `status` enum('draft','sent','paid','partial','overdue','cancelled') DEFAULT 'draft',
  `payment_terms` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`invoice_id`),
  UNIQUE KEY `unique_invoice_number` (`invoice_number`),
  KEY `fk_invoice_appointment` (`appointment_id`),
  KEY `fk_invoice_client` (`client_id`),
  KEY `fk_invoice_vehicle` (`vehicle_id`),
  KEY `fk_invoice_created_by` (`created_by`),
  INDEX `idx_status_date` (`status`, `invoice_date`),
  CONSTRAINT `fk_invoice_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`),
  CONSTRAINT `fk_invoice_client` FOREIGN KEY (`client_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `fk_invoice_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`vehicle_id`),
  CONSTRAINT `fk_invoice_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `invoice_items`;
CREATE TABLE `invoice_items` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `service_id` int(11) DEFAULT NULL,
  `inventory_item_id` int(11) DEFAULT NULL,
  `description` varchar(500) NOT NULL,
  `quantity` decimal(10,3) NOT NULL DEFAULT 1.000,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `item_type` enum('service','part','labor','other') NOT NULL DEFAULT 'service',
  PRIMARY KEY (`item_id`),
  KEY `fk_invoice_item_invoice` (`invoice_id`),
  KEY `fk_invoice_item_service` (`service_id`),
  KEY `fk_invoice_item_inventory` (`inventory_item_id`),
  CONSTRAINT `fk_invoice_item_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`invoice_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_invoice_item_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`),
  CONSTRAINT `fk_invoice_item_inventory` FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','credit_card','debit_card','bank_transfer','check','online') NOT NULL DEFAULT 'cash',
  `reference_number` varchar(100) DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `status` enum('pending','completed','failed','refunded') DEFAULT 'completed',
  `notes` text DEFAULT NULL,
  `processed_by` int(11) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`payment_id`),
  KEY `fk_payment_invoice` (`invoice_id`),
  KEY `fk_payment_client` (`client_id`),
  KEY `fk_payment_processed_by` (`processed_by`),
  INDEX `idx_payment_date` (`payment_date`),
  INDEX `idx_status` (`status`),
  CONSTRAINT `fk_payment_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`invoice_id`),
  CONSTRAINT `fk_payment_client` FOREIGN KEY (`client_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `fk_payment_processed_by` FOREIGN KEY (`processed_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- 9. RATINGS & REVIEWS
-- ================================================

DROP TABLE IF EXISTS `ratings`;
CREATE TABLE `ratings` (
  `rating_id` int(11) NOT NULL AUTO_INCREMENT,
  `appointment_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `technician_id` int(11) DEFAULT NULL,
  `service_quality_rating` int(1) NOT NULL COMMENT '1-5 stars',
  `timeliness_rating` int(1) DEFAULT NULL,
  `professionalism_rating` int(1) DEFAULT NULL,
  `overall_rating` decimal(2,1) DEFAULT NULL,
  `review_title` varchar(255) DEFAULT NULL,
  `review_text` text DEFAULT NULL,
  `would_recommend` tinyint(1) DEFAULT NULL,
  `is_anonymous` tinyint(1) DEFAULT 0,
  `is_approved` tinyint(1) DEFAULT 1,
  `admin_response` text DEFAULT NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`rating_id`),
  UNIQUE KEY `unique_appointment_rating` (`appointment_id`),
  KEY `fk_rating_client` (`client_id`),
  KEY `fk_rating_technician` (`technician_id`),
  INDEX `idx_overall_rating` (`overall_rating`),
  INDEX `idx_approved` (`is_approved`),
  CONSTRAINT `fk_rating_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`),
  CONSTRAINT `fk_rating_client` FOREIGN KEY (`client_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `fk_rating_technician` FOREIGN KEY (`technician_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- 10. NOTIFICATIONS SYSTEM
-- ================================================

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `recipient_id` int(11) NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','error','appointment','payment','system') DEFAULT 'info',
  `priority` enum('low','normal','high','urgent') DEFAULT 'normal',
  `related_table` varchar(50) DEFAULT NULL COMMENT 'appointments, invoices, etc.',
  `related_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `read_date` timestamp NULL DEFAULT NULL,
  `is_sent` tinyint(1) DEFAULT 0,
  `sent_via` set('email','sms','push','system') DEFAULT 'system',
  `scheduled_send` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notification_id`),
  KEY `fk_notification_recipient` (`recipient_id`),
  KEY `fk_notification_sender` (`sender_id`),
  INDEX `idx_unread` (`recipient_id`, `is_read`),
  INDEX `idx_type_priority` (`type`, `priority`),
  INDEX `idx_created` (`created_date`),
  CONSTRAINT `fk_notification_recipient` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_notification_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- 11. AUDIT LOG SYSTEM
-- ================================================

DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `severity` enum('low','medium','high','critical') DEFAULT 'medium',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `fk_audit_user` (`user_id`),
  INDEX `idx_action` (`action`),
  INDEX `idx_table_record` (`table_name`, `record_id`),
  INDEX `idx_created_date` (`created_date`),
  INDEX `idx_severity` (`severity`),
  CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- 12. SYSTEM SETTINGS
-- ================================================

DROP TABLE IF EXISTS `system_settings`;
CREATE TABLE `system_settings` (
  `setting_id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL UNIQUE,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('string','integer','float','boolean','json') DEFAULT 'string',
  `category` varchar(100) DEFAULT 'general',
  `description` text DEFAULT NULL,
  `is_editable` tinyint(1) DEFAULT 1,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_id`),
  UNIQUE KEY `unique_setting_key` (`setting_key`),
  INDEX `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- 13. SESSIONS TABLE
-- ================================================

DROP TABLE IF EXISTS `user_sessions`;
CREATE TABLE `user_sessions` (
  `session_id` varchar(255) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session_data` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`session_id`),
  KEY `fk_session_user` (`user_id`),
  INDEX `idx_expires` (`expires_at`),
  INDEX `idx_active` (`is_active`),
  CONSTRAINT `fk_session_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;