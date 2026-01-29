-- ============================================================
-- VehiCare Enhanced Database Schema
-- ============================================================
-- Additional tables for complete system functionality

-- PARTS/INVENTORY TABLE
CREATE TABLE IF NOT EXISTS inventory_parts (
    part_id INT PRIMARY KEY AUTO_INCREMENT,
    part_name VARCHAR(150) NOT NULL,
    part_code VARCHAR(50) UNIQUE,
    category VARCHAR(100),
    description TEXT,
    quantity INT DEFAULT 0,
    reorder_level INT,
    unit_price DECIMAL(10, 2),
    supplier VARCHAR(100),
    status ENUM('active', 'discontinued') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_part_code (part_code),
    INDEX idx_category (category),
    INDEX idx_status (status)
);

-- INVENTORY TRANSACTIONS
CREATE TABLE IF NOT EXISTS inventory_transactions (
    transaction_id INT PRIMARY KEY AUTO_INCREMENT,
    part_id INT NOT NULL,
    transaction_type ENUM('purchase', 'usage', 'adjustment', 'return') DEFAULT 'purchase',
    quantity INT NOT NULL,
    reference_id INT,
    reference_type VARCHAR(50),
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (part_id) REFERENCES inventory_parts(part_id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_part_id (part_id),
    INDEX idx_created_at (created_at)
);

-- STAFF RATINGS/REVIEWS
CREATE TABLE IF NOT EXISTS staff_ratings (
    rating_id INT PRIMARY KEY AUTO_INCREMENT,
    staff_id INT NOT NULL,
    client_id INT NOT NULL,
    appointment_id INT,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    review_text TEXT,
    rating_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (staff_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (appointment_id) REFERENCES appointments(appointment_id) ON DELETE SET NULL,
    INDEX idx_staff_id (staff_id),
    INDEX idx_rating (rating)
);

-- NOTIFICATIONS
CREATE TABLE IF NOT EXISTS notifications (
    notification_id INT PRIMARY KEY AUTO_INCREMENT,
    recipient_id INT NOT NULL,
    sender_id INT,
    title VARCHAR(200) NOT NULL,
    message TEXT,
    notification_type ENUM('appointment', 'payment', 'queue', 'service', 'system') DEFAULT 'system',
    reference_id INT,
    reference_type VARCHAR(50),
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recipient_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_recipient_id (recipient_id),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at)
);

-- INVOICES/BILLING
CREATE TABLE IF NOT EXISTS invoices (
    invoice_id INT PRIMARY KEY AUTO_INCREMENT,
    appointment_id INT,
    history_id INT,
    user_id INT NOT NULL,
    invoice_number VARCHAR(50) UNIQUE,
    invoice_date DATE NOT NULL,
    due_date DATE,
    subtotal DECIMAL(10, 2),
    tax DECIMAL(10, 2),
    total_amount DECIMAL(10, 2),
    paid_amount DECIMAL(10, 2) DEFAULT 0,
    status ENUM('draft', 'issued', 'paid', 'overdue', 'cancelled') DEFAULT 'draft',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (appointment_id) REFERENCES appointments(appointment_id) ON DELETE SET NULL,
    FOREIGN KEY (history_id) REFERENCES service_history(history_id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_invoice_date (invoice_date)
);

-- INVOICE ITEMS
CREATE TABLE IF NOT EXISTS invoice_items (
    item_id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_id INT NOT NULL,
    description VARCHAR(200),
    quantity INT,
    unit_price DECIMAL(10, 2),
    total_price DECIMAL(10, 2),
    FOREIGN KEY (invoice_id) REFERENCES invoices(invoice_id) ON DELETE CASCADE,
    INDEX idx_invoice_id (invoice_id)
);

-- AUDIT LOG / ACTIVITY LOG
CREATE TABLE IF NOT EXISTS audit_logs (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(100),
    record_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    status ENUM('success', 'failed') DEFAULT 'success',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
);

-- APPOINTMENT SERVICE ITEMS (for linking multiple services/parts to appointments)
CREATE TABLE IF NOT EXISTS appointment_services (
    appointment_service_id INT PRIMARY KEY AUTO_INCREMENT,
    appointment_id INT NOT NULL,
    service_id INT,
    part_id INT,
    quantity INT DEFAULT 1,
    unit_price DECIMAL(10, 2),
    FOREIGN KEY (appointment_id) REFERENCES appointments(appointment_id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(service_id) ON DELETE SET NULL,
    FOREIGN KEY (part_id) REFERENCES inventory_parts(part_id) ON DELETE SET NULL,
    INDEX idx_appointment_id (appointment_id)
);
