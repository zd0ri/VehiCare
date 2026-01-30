-- Database updates for comprehensive admin system
-- Run this to ensure all required tables are properly structured

-- Update appointments table to include more details
ALTER TABLE appointments ADD COLUMN IF NOT EXISTS notes TEXT;
ALTER TABLE appointments ADD COLUMN IF NOT EXISTS estimated_cost DECIMAL(10,2) DEFAULT 0;
ALTER TABLE appointments ADD COLUMN IF NOT EXISTS walk_in BOOLEAN DEFAULT FALSE;

-- Update staff table for technician ratings
ALTER TABLE staff ADD COLUMN IF NOT EXISTS specialization VARCHAR(100);
ALTER TABLE staff ADD COLUMN IF NOT EXISTS average_rating FLOAT DEFAULT 0;
ALTER TABLE staff ADD COLUMN IF NOT EXISTS user_id INT;

-- Ensure ratings table has complete structure
ALTER TABLE ratings ADD COLUMN IF NOT EXISTS appointment_id INT;
ALTER TABLE ratings ADD COLUMN IF NOT EXISTS rating_date DATETIME DEFAULT CURRENT_TIMESTAMP;

-- Ensure queue table has full structure
ALTER TABLE queue ADD COLUMN IF NOT EXISTS created_at DATETIME DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE queue ADD COLUMN IF NOT EXISTS completed_at DATETIME;

-- Ensure payments table is complete
ALTER TABLE payments ADD COLUMN IF NOT EXISTS status VARCHAR(50) DEFAULT 'pending';
ALTER TABLE payments ADD COLUMN IF NOT EXISTS appointment_id INT;
ALTER TABLE payments ADD COLUMN IF NOT EXISTS client_id INT;

-- Ensure invoices table is complete
ALTER TABLE invoices ADD COLUMN IF NOT EXISTS client_id INT;
ALTER TABLE invoices ADD COLUMN IF NOT EXISTS status VARCHAR(50) DEFAULT 'pending';
ALTER TABLE invoices ADD COLUMN IF NOT EXISTS payment_status VARCHAR(50) DEFAULT 'unpaid';

-- Create notifications table if not exists
CREATE TABLE IF NOT EXISTS notifications (
  notification_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  title VARCHAR(255) NOT NULL,
  message TEXT,
  type VARCHAR(50),
  related_id INT,
  is_read BOOLEAN DEFAULT FALSE,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Create service_history table if not exists
CREATE TABLE IF NOT EXISTS service_history (
  history_id INT AUTO_INCREMENT PRIMARY KEY,
  client_id INT NOT NULL,
  vehicle_id INT,
  service_id INT,
  appointment_id INT,
  service_date DATE,
  description TEXT,
  cost DECIMAL(10,2),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (client_id) REFERENCES clients(client_id),
  FOREIGN KEY (vehicle_id) REFERENCES vehicles(vehicle_id),
  FOREIGN KEY (service_id) REFERENCES services(service_id),
  FOREIGN KEY (appointment_id) REFERENCES appointments(appointment_id)
);

-- Update vehicles table
ALTER TABLE vehicles ADD COLUMN IF NOT EXISTS user_id INT;
ALTER TABLE vehicles ADD COLUMN IF NOT EXISTS registration_number VARCHAR(50);

-- Create walk-in bookings table
CREATE TABLE IF NOT EXISTS walk_in_bookings (
  booking_id INT AUTO_INCREMENT PRIMARY KEY,
  customer_name VARCHAR(100) NOT NULL,
  phone VARCHAR(20),
  email VARCHAR(100),
  vehicle_info VARCHAR(255),
  service_id INT,
  booking_date DATE,
  booking_time TIME,
  status VARCHAR(50) DEFAULT 'pending',
  notes TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (service_id) REFERENCES services(service_id)
);

-- Create index for common queries
CREATE INDEX IF NOT EXISTS idx_appointment_status ON appointments(status);
CREATE INDEX IF NOT EXISTS idx_appointment_date ON appointments(appointment_date);
CREATE INDEX IF NOT EXISTS idx_queue_status ON queue(status);
CREATE INDEX IF NOT EXISTS idx_notification_user ON notifications(user_id, is_read);
CREATE INDEX IF NOT EXISTS idx_service_history_client ON service_history(client_id);
