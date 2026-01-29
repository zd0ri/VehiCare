-- ============================================================
-- VehiCare System - Complete Database Schema
-- ============================================================
-- This file contains all tables needed for the VehiCare
-- vehicle maintenance and service management system.
--
-- Version: 1.0
-- Last Updated: January 28, 2026
-- ============================================================

-- ============================================================
-- USERS TABLE (Core authentication and roles)
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff', 'client') NOT NULL DEFAULT 'client',
    full_name VARCHAR(150),
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_role (role),
    INDEX idx_email (email)
);

-- ============================================================
-- CUSTOMER PROFILES TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS customer_profiles (
    profile_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL UNIQUE,
    contact_number VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    province VARCHAR(100),
    postal_code VARCHAR(20),
    profile_image VARCHAR(255),
    is_profile_complete BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
);

-- ============================================================
-- VEHICLES TABLE (Customer vehicle information)
-- ============================================================
CREATE TABLE IF NOT EXISTS vehicles (
    vehicle_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    vehicle_type VARCHAR(100) NOT NULL,
    make VARCHAR(100),
    model VARCHAR(100),
    year INT,
    plate_number VARCHAR(50) NOT NULL UNIQUE,
    color VARCHAR(50),
    mileage INT DEFAULT 0,
    description TEXT,
    status ENUM('active', 'inactive', 'maintenance') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_plate_number (plate_number),
    INDEX idx_status (status)
);

-- ============================================================
-- SERVICES TABLE (Available services catalog)
-- ============================================================
CREATE TABLE IF NOT EXISTS services (
    service_id INT PRIMARY KEY AUTO_INCREMENT,
    service_name VARCHAR(150) NOT NULL,
    description TEXT,
    estimated_duration INT,
    base_price DECIMAL(10, 2),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status)
);

-- ============================================================
-- APPOINTMENTS TABLE (Service appointments and walk-ins)
-- ============================================================
CREATE TABLE IF NOT EXISTS appointments (
    appointment_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    service_id INT,
    appointment_type ENUM('appointment', 'walk-in') DEFAULT 'appointment',
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    status ENUM('pending', 'confirmed', 'in-progress', 'completed', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    assigned_to INT,
    queue_number INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(vehicle_id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(service_id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_vehicle_id (vehicle_id),
    INDEX idx_appointment_date (appointment_date),
    INDEX idx_status (status),
    INDEX idx_appointment_type (appointment_type)
);

-- ============================================================
-- SERVICE HISTORY TABLE (Completed services)
-- ============================================================
CREATE TABLE IF NOT EXISTS service_history (
    history_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    appointment_id INT,
    service_id INT,
    service_date DATE NOT NULL,
    service_time TIME,
    service_cost DECIMAL(10, 2),
    staff_member INT,
    description TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(vehicle_id) ON DELETE CASCADE,
    FOREIGN KEY (appointment_id) REFERENCES appointments(appointment_id) ON DELETE SET NULL,
    FOREIGN KEY (service_id) REFERENCES services(service_id) ON DELETE SET NULL,
    FOREIGN KEY (staff_member) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_vehicle_id (vehicle_id),
    INDEX idx_service_date (service_date)
);

-- ============================================================
-- QUEUE MANAGEMENT TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS queue_management (
    queue_id INT PRIMARY KEY AUTO_INCREMENT,
    appointment_id INT NOT NULL,
    queue_number INT NOT NULL,
    status ENUM('waiting', 'in-service', 'completed', 'cancelled') DEFAULT 'waiting',
    estimated_wait_time INT,
    actual_start_time TIMESTAMP NULL,
    actual_end_time TIMESTAMP NULL,
    service_bay INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(appointment_id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_queue_number (queue_number),
    INDEX idx_appointment_id (appointment_id)
);

-- ============================================================
-- PAYMENTS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS payments (
    payment_id INT PRIMARY KEY AUTO_INCREMENT,
    appointment_id INT,
    history_id INT,
    user_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    payment_method ENUM('cash', 'credit_card', 'debit_card', 'online') DEFAULT 'cash',
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    reference_number VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(appointment_id) ON DELETE SET NULL,
    FOREIGN KEY (history_id) REFERENCES service_history(history_id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_payment_method (payment_method)
);

-- ============================================================
-- INDEXES FOR PERFORMANCE OPTIMIZATION
-- ============================================================
CREATE INDEX idx_appointments_created_at ON appointments(created_at);
CREATE INDEX idx_service_history_created_at ON service_history(created_at);
CREATE INDEX idx_queue_created_at ON queue_management(created_at);
CREATE INDEX idx_vehicles_created_at ON vehicles(created_at);
CREATE INDEX idx_users_created_at ON users(created_at);

-- ============================================================
-- VIEWS FOR COMMON QUERIES
-- ============================================================

-- Active appointments view
CREATE OR REPLACE VIEW active_appointments AS
SELECT 
    a.appointment_id,
    a.user_id,
    u.full_name as customer_name,
    a.vehicle_id,
    v.vehicle_type,
    v.plate_number,
    a.service_id,
    s.service_name,
    a.appointment_date,
    a.appointment_time,
    a.status
FROM appointments a
JOIN users u ON a.user_id = u.user_id
JOIN vehicles v ON a.vehicle_id = v.vehicle_id
LEFT JOIN services s ON a.service_id = s.service_id
WHERE a.status IN ('pending', 'confirmed', 'in-progress');

-- Customer summary view
CREATE OR REPLACE VIEW customer_summary AS
SELECT 
    u.user_id,
    u.full_name,
    u.email,
    cp.contact_number,
    cp.is_profile_complete,
    COUNT(DISTINCT v.vehicle_id) as vehicle_count,
    COUNT(DISTINCT a.appointment_id) as appointment_count,
    SUM(CASE WHEN sh.history_id IS NOT NULL THEN sh.service_cost ELSE 0 END) as total_spent
FROM users u
LEFT JOIN customer_profiles cp ON u.user_id = cp.user_id
LEFT JOIN vehicles v ON u.user_id = v.user_id
LEFT JOIN appointments a ON u.user_id = a.user_id
LEFT JOIN service_history sh ON u.user_id = sh.user_id
WHERE u.role = 'client'
GROUP BY u.user_id;

-- ============================================================
-- SAMPLE DATA (Optional - for testing)
-- ============================================================

-- Insert sample services
INSERT INTO services (service_name, description, estimated_duration, base_price) VALUES
('Oil Change', 'Regular engine oil and filter change', 30, 500.00),
('Tire Rotation', 'Rotate tires for even wear', 45, 750.00),
('Battery Check', 'Battery health and charging system check', 20, 300.00),
('Brake Inspection', 'Complete brake system inspection', 60, 1000.00),
('AC Service', 'Air conditioning system maintenance', 90, 1500.00),
('General Inspection', 'Comprehensive vehicle health check', 120, 2000.00);

-- ============================================================
-- END OF DATABASE SCHEMA
-- ============================================================
-- This completes the VehiCare system database setup.
-- All tables are created and ready for use.
-- 
-- Next steps:
-- 1. Verify all tables are created successfully
-- 2. Check indexes are in place
-- 3. Test database connectivity
-- 4. Begin populating customer data
-- ============================================================
