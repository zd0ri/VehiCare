-- VehiCare Client Module Database Schema
-- Import this file into your MySQL database to add client module functionality

-- First, add client_id column to invoices table if it doesn't exist
ALTER TABLE invoices ADD COLUMN IF NOT EXISTS client_id INT(11) NOT NULL DEFAULT 1 AFTER invoice_id;

-- Add other essential columns to invoices table if they don't exist
ALTER TABLE invoices ADD COLUMN IF NOT EXISTS payment_status ENUM('unpaid', 'partial', 'paid') DEFAULT 'unpaid';
ALTER TABLE invoices ADD COLUMN IF NOT EXISTS subtotal DECIMAL(10,2) DEFAULT 0.00;
ALTER TABLE invoices ADD COLUMN IF NOT EXISTS tax_amount DECIMAL(10,2) DEFAULT 0.00;
ALTER TABLE invoices ADD COLUMN IF NOT EXISTS grand_total DECIMAL(10,2) NOT NULL DEFAULT 0.00;

-- Ensure vehicles table has proper timestamp columns
ALTER TABLE vehicles ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE vehicles ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE vehicles ADD COLUMN IF NOT EXISTS status ENUM('active', 'inactive', 'sold') DEFAULT 'active';

-- Ensure users table has proper timestamp columns
ALTER TABLE users ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE users ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Ensure services table has proper status column
ALTER TABLE services ADD COLUMN IF NOT EXISTS status ENUM('active', 'inactive') DEFAULT 'active';

-- Create reviews table for client feedback
CREATE TABLE IF NOT EXISTS reviews (
    review_id INT(11) PRIMARY KEY AUTO_INCREMENT,
    client_id INT(11) NOT NULL,
    appointment_id INT(11) NOT NULL,
    rating INT(1) NOT NULL CHECK (rating BETWEEN 1 AND 5),
    review_text TEXT NOT NULL,
    service_quality INT(1) DEFAULT NULL CHECK (service_quality BETWEEN 1 AND 5),
    staff_friendliness INT(1) DEFAULT NULL CHECK (staff_friendliness BETWEEN 1 AND 5),
    timeliness INT(1) DEFAULT NULL CHECK (timeliness BETWEEN 1 AND 5),
    value_for_money INT(1) DEFAULT NULL CHECK (value_for_money BETWEEN 1 AND 5),
    recommend ENUM('yes', 'no') DEFAULT 'yes',
    review_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'hidden', 'flagged') DEFAULT 'active',
    UNIQUE KEY unique_client_appointment (client_id, appointment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create payments table for payment tracking
CREATE TABLE IF NOT EXISTS payments (
    payment_id INT(11) PRIMARY KEY AUTO_INCREMENT,
    client_id INT(11) NOT NULL,
    invoice_id INT(11) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash', 'credit_card', 'debit_card', 'gcash', 'paymaya', 'bank_transfer') NOT NULL,
    payment_status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reference_number VARCHAR(100) DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create notifications table for client communications
CREATE TABLE IF NOT EXISTS notifications (
    notification_id INT(11) PRIMARY KEY AUTO_INCREMENT,
    client_id INT(11) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('appointment', 'payment', 'reminder', 'promotion', 'system') DEFAULT 'system',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL DEFAULT NULL,
    related_id INT(11) DEFAULT NULL COMMENT 'Related appointment_id, payment_id, etc.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create maintenance reminders table
CREATE TABLE IF NOT EXISTS maintenance_reminders (
    reminder_id INT(11) PRIMARY KEY AUTO_INCREMENT,
    client_id INT(11) NOT NULL,
    vehicle_id INT(11) NOT NULL,
    reminder_type ENUM('mileage', 'time', 'both') DEFAULT 'time',
    service_type VARCHAR(100) NOT NULL,
    due_date DATE DEFAULT NULL,
    due_mileage INT(11) DEFAULT NULL,
    current_mileage INT(11) DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    is_completed BOOLEAN DEFAULT FALSE,
    completed_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create user preferences table
CREATE TABLE IF NOT EXISTS user_preferences (
    preference_id INT(11) PRIMARY KEY AUTO_INCREMENT,
    client_id INT(11) NOT NULL,
    email_notifications BOOLEAN DEFAULT TRUE,
    sms_notifications BOOLEAN DEFAULT TRUE,
    appointment_reminders BOOLEAN DEFAULT TRUE,
    maintenance_reminders BOOLEAN DEFAULT TRUE,
    promotional_emails BOOLEAN DEFAULT FALSE,
    newsletter_subscription BOOLEAN DEFAULT FALSE,
    language ENUM('en', 'fil') DEFAULT 'en',
    timezone VARCHAR(50) DEFAULT 'Asia/Manila',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_client_prefs (client_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create client activity logs table
CREATE TABLE IF NOT EXISTS client_activity_logs (
    log_id INT(11) PRIMARY KEY AUTO_INCREMENT,
    client_id INT(11) NOT NULL,
    activity_type ENUM('login', 'logout', 'appointment', 'payment', 'profile_update', 'vehicle_add', 'review', 'other') NOT NULL,
    activity_description TEXT NOT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create invoices table if it doesn't exist
CREATE TABLE IF NOT EXISTS invoices (
    invoice_id INT(11) PRIMARY KEY AUTO_INCREMENT,
    client_id INT(11) NOT NULL,
    appointment_id INT(11) DEFAULT NULL,
    invoice_date DATE NOT NULL,
    subtotal DECIMAL(10,2) DEFAULT 0.00,
    tax_amount DECIMAL(10,2) DEFAULT 0.00,
    grand_total DECIMAL(10,2) NOT NULL,
    payment_status ENUM('unpaid', 'partial', 'paid') DEFAULT 'unpaid',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample data for testing (optional)
-- You can remove this section if you don't want sample data

-- Insert sample invoices for testing
INSERT IGNORE INTO invoices (client_id, invoice_date, subtotal, tax_amount, grand_total, payment_status) VALUES
(1, '2025-02-01', 1500.00, 180.00, 1680.00, 'unpaid'),
(1, '2025-01-15', 800.00, 96.00, 896.00, 'paid'),
(1, '2024-12-20', 2200.00, 264.00, 2464.00, 'partial');

-- Insert sample notifications
INSERT IGNORE INTO notifications (client_id, title, message, type, is_read) VALUES
(1, 'Payment Received', 'Your payment for invoice #000002 has been processed successfully.', 'payment', TRUE),
(1, 'Appointment Reminder', 'Your vehicle service appointment is tomorrow at 10:00 AM.', 'appointment', FALSE),
(1, 'Maintenance Due', 'Your vehicle is due for regular maintenance. Schedule an appointment today!', 'reminder', FALSE);

-- Insert default preferences for existing users (adjust client_id as needed)
INSERT IGNORE INTO user_preferences (client_id) VALUES (1);

-- Add foreign key constraints (if tables support it)
-- Note: These may fail if referenced tables don't exist yet, which is okay
ALTER TABLE reviews ADD CONSTRAINT fk_review_client FOREIGN KEY (client_id) REFERENCES users(user_id) ON DELETE CASCADE;
ALTER TABLE reviews ADD CONSTRAINT fk_review_appointment FOREIGN KEY (appointment_id) REFERENCES appointments(appointment_id) ON DELETE CASCADE;

ALTER TABLE payments ADD CONSTRAINT fk_payment_client FOREIGN KEY (client_id) REFERENCES users(user_id) ON DELETE CASCADE;
ALTER TABLE payments ADD CONSTRAINT fk_payment_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(invoice_id) ON DELETE CASCADE;

ALTER TABLE notifications ADD CONSTRAINT fk_notification_client FOREIGN KEY (client_id) REFERENCES users(user_id) ON DELETE CASCADE;

ALTER TABLE maintenance_reminders ADD CONSTRAINT fk_reminder_client FOREIGN KEY (client_id) REFERENCES users(user_id) ON DELETE CASCADE;
ALTER TABLE maintenance_reminders ADD CONSTRAINT fk_reminder_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(vehicle_id) ON DELETE CASCADE;

ALTER TABLE user_preferences ADD CONSTRAINT fk_prefs_client FOREIGN KEY (client_id) REFERENCES users(user_id) ON DELETE CASCADE;

ALTER TABLE client_activity_logs ADD CONSTRAINT fk_activity_client FOREIGN KEY (client_id) REFERENCES users(user_id) ON DELETE CASCADE;

ALTER TABLE invoices ADD CONSTRAINT fk_invoice_client FOREIGN KEY (client_id) REFERENCES users(user_id) ON DELETE CASCADE;
ALTER TABLE invoices ADD CONSTRAINT fk_invoice_appointment FOREIGN KEY (appointment_id) REFERENCES appointments(appointment_id) ON DELETE SET NULL;