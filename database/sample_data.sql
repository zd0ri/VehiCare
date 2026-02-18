-- VehiCare Sample Data
-- Test data for development and demonstration
-- Run AFTER schema.sql

SET sql_mode = '';

-- ================================================
-- 1. SAMPLE USERS
-- ================================================

INSERT INTO `users` (`email`, `password`, `full_name`, `phone`, `address`, `role`, `status`) VALUES
-- Admin Users
('admin@vehicare.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', '+1234567890', '123 Admin Street, Admin City', 'admin', 'active'),
('manager@vehicare.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Service Manager', '+1234567891', '456 Manager Ave, Admin City', 'admin', 'active'),

-- Staff/Technicians
('john.mechanic@vehicare.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Smith', '+1234567892', '789 Tech Street, Service City', 'staff', 'active'),
('jane.tech@vehicare.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane Rodriguez', '+1234567893', '321 Repair Lane, Service City', 'staff', 'active'),
('mike.senior@vehicare.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mike Johnson', '+1234567894', '654 Workshop Blvd, Service City', 'staff', 'active'),

-- Clients
('client1@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Robert Wilson', '+1234567895', '987 Client St, Customer City', 'client', 'active'),
('client2@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah Davis', '+1234567896', '147 Owner Ave, Customer City', 'client', 'active'),
('client3@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'David Brown', '+1234567897', '258 Vehicle Dr, Customer City', 'client', 'active'),
('client4@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lisa Anderson', '+1234567898', '369 Car Lane, Customer City', 'client', 'active'),
('client5@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mark Taylor', '+1234567899', '741 Auto Street, Customer City', 'client', 'active');

-- ================================================
-- 2. SAMPLE VEHICLES
-- ================================================

INSERT INTO `vehicles` (`client_id`, `plate_number`, `car_brand`, `car_model`, `year_model`, `color`, `engine_type`, `transmission_type`, `mileage`) VALUES
(6, 'ABC-1234', 'Toyota', 'Camry', 2020, 'Silver', 'Gasoline', 'automatic', 45000),
(6, 'DEF-5678', 'Honda', 'Civic', 2019, 'Blue', 'Gasoline', 'manual', 38000),
(7, 'GHI-9012', 'Ford', 'F-150', 2021, 'Black', 'Gasoline', 'automatic', 25000),
(8, 'JKL-3456', 'BMW', '330i', 2022, 'White', 'Gasoline', 'automatic', 15000),
(9, 'MNO-7890', 'Mercedes', 'C-Class', 2020, 'Gray', 'Gasoline', 'automatic', 32000),
(10, 'PQR-1357', 'Nissan', 'Altima', 2018, 'Red', 'Gasoline', 'automatic', 62000),
(10, 'STU-2468', 'Chevrolet', 'Malibu', 2019, 'White', 'Gasoline', 'automatic', 48000);

-- ================================================
-- 3. SAMPLE SERVICES
-- ================================================

INSERT INTO `services` (`service_name`, `description`, `category`, `estimated_duration`, `base_price`, `labor_cost`, `skill_level`) VALUES
-- Basic Services
('Oil Change', 'Regular engine oil and filter replacement', 'Maintenance', 30, 45.00, 25.00, 'basic'),
('Tire Rotation', 'Rotate tires for even wear distribution', 'Maintenance', 45, 35.00, 30.00, 'basic'),
('Battery Service', 'Battery testing, cleaning, and replacement', 'Electrical', 60, 120.00, 45.00, 'intermediate'),

-- Intermediate Services
('Brake Inspection', 'Complete brake system inspection and adjustment', 'Brakes', 90, 85.00, 65.00, 'intermediate'),
('Air Filter Replacement', 'Engine and cabin air filter replacement', 'Maintenance', 30, 55.00, 25.00, 'basic'),
('Coolant Flush', 'Complete cooling system flush and refill', 'Cooling System', 120, 95.00, 75.00, 'intermediate'),

-- Advanced Services
('Transmission Service', 'Transmission fluid and filter service', 'Transmission', 180, 245.00, 120.00, 'advanced'),
('Engine Diagnostics', 'Computer diagnostic scan and analysis', 'Diagnostics', 90, 125.00, 85.00, 'advanced'),
('Timing Belt Replacement', 'Timing belt and related component replacement', 'Engine', 300, 450.00, 280.00, 'expert'),

-- Specialty Services
('AC Service', 'Air conditioning system service and recharge', 'Climate Control', 120, 165.00, 95.00, 'intermediate'),
('Wheel Alignment', '4-wheel computerized alignment service', 'Suspension', 90, 89.00, 65.00, 'intermediate'),
('Exhaust System Repair', 'Exhaust system inspection and repair', 'Exhaust', 150, 195.00, 110.00, 'advanced');

-- ================================================
-- 4. SAMPLE APPOINTMENTS
-- ================================================

INSERT INTO `appointments` (`client_id`, `vehicle_id`, `service_id`, `technician_id`, `appointment_date`, `appointment_time`, `status`, `priority`, `customer_notes`, `total_estimated_cost`) VALUES
-- Today's appointments
(6, 1, 1, 3, CURDATE(), '09:00:00', 'confirmed', 'normal', 'Regular maintenance due', 45.00),
(7, 3, 4, 4, CURDATE(), '10:30:00', 'in_progress', 'normal', 'Brake noise reported', 85.00),
(8, 4, 8, 5, CURDATE(), '14:00:00', 'pending', 'normal', 'Check engine light on', 125.00),

-- Tomorrow's appointments  
(9, 5, 2, 3, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '08:00:00', 'confirmed', 'normal', 'Tire wear concern', 35.00),
(10, 6, 6, 4, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '11:00:00', 'confirmed', 'normal', 'Overheating issue', 95.00),

-- Future appointments
(6, 2, 7, 5, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '09:30:00', 'pending', 'normal', 'Transmission service due', 245.00),
(7, 3, 11, 4, DATE_ADD(CURDATE(), INTERVAL 5 DAY), '13:00:00', 'confirmed', 'high', 'Alignment needed after accident', 89.00),

-- Completed appointments (last week)
(8, 4, 1, 3, DATE_SUB(CURDATE(), INTERVAL 7 DAY), '10:00:00', 'completed', 'normal', 'Standard oil change', 45.00),
(9, 5, 5, 4, DATE_SUB(CURDATE(), INTERVAL 5 DAY), '15:00:00', 'completed', 'normal', 'Air filter replacement', 55.00);

-- ================================================
-- 5. SAMPLE ASSIGNMENTS
-- ================================================

INSERT INTO `assignments` (`appointment_id`, `technician_id`, `assigned_by`, `status`, `priority`, `estimated_hours`) VALUES
(1, 3, 1, 'assigned', 'normal', 0.5),
(2, 4, 1, 'in_progress', 'normal', 1.5),
(3, 5, 1, 'assigned', 'normal', 1.5),
(4, 3, 1, 'assigned', 'normal', 0.75),
(5, 4, 1, 'assigned', 'normal', 2.0),
(6, 5, 1, 'assigned', 'normal', 3.0),
(7, 4, 1, 'assigned', 'high', 1.5),
(8, 3, 1, 'completed', 'normal', 0.5),
(9, 4, 1, 'completed', 'normal', 0.5);

-- ================================================
-- 6. SAMPLE INVENTORY
-- ================================================

INSERT INTO `inventory` (`item_name`, `item_code`, `category`, `brand`, `description`, `unit_price`, `cost_price`, `current_stock`, `minimum_stock`, `location`) VALUES
-- Oils & Fluids
('5W-30 Motor Oil', 'OIL-5W30-5L', 'Oils & Fluids', 'Mobil1', '5 Liter synthetic motor oil', 35.99, 24.50, 45, 10, 'Shelf A-1'),
('Brake Fluid DOT3', 'FLUID-BRAKE-DOT3', 'Oils & Fluids', 'Valvoline', 'DOT3 brake fluid 32oz', 8.99, 5.75, 25, 5, 'Shelf A-2'),
('Coolant Antifreeze', 'COOL-AF-1GAL', 'Oils & Fluids', 'Prestone', '1 Gallon coolant antifreeze', 12.99, 8.25, 18, 8, 'Shelf A-3'),

-- Filters
('Oil Filter Standard', 'FILT-OIL-STD', 'Filters', 'Fram', 'Standard oil filter for most vehicles', 9.99, 6.50, 35, 15, 'Shelf B-1'),
('Air Filter Engine', 'FILT-AIR-ENG', 'Filters', 'K&N', 'High performance engine air filter', 24.99, 16.25, 20, 10, 'Shelf B-2'),
('Cabin Air Filter', 'FILT-AIR-CAB', 'Filters', 'Bosch', 'Cabin air filter with activated carbon', 18.99, 12.50, 22, 8, 'Shelf B-3'),

-- Belts & Hoses
('Serpentine Belt', 'BELT-SERP-STD', 'Belts & Hoses', 'Gates', 'Standard serpentine belt', 45.99, 28.75, 12, 5, 'Shelf C-1'),
('Radiator Hose Upper', 'HOSE-RAD-UPP', 'Belts & Hoses', 'Dayco', 'Upper radiator hose', 32.99, 21.50, 8, 4, 'Shelf C-2'),

-- Brake Parts
('Brake Pads Front', 'BRAKE-PAD-FRT', 'Brake Parts', 'Wagner', 'Front disc brake pads premium', 65.99, 42.75, 15, 6, 'Shelf D-1'),
('Brake Rotor', 'BRAKE-ROT-FRT', 'Brake Parts', 'Raybestos', 'Front brake rotor standard', 89.99, 58.50, 10, 4, 'Shelf D-2'),

-- Batteries
('Car Battery 12V', 'BATT-12V-STD', 'Electrical', 'Interstate', '12V automotive battery 600CCA', 129.99, 85.75, 8, 3, 'Battery Rack');

-- ================================================
-- 7. SAMPLE INVOICES
-- ================================================

INSERT INTO `invoices` (`invoice_number`, `appointment_id`, `client_id`, `vehicle_id`, `invoice_date`, `due_date`, `subtotal`, `tax_rate`, `tax_amount`, `total_amount`, `status`, `created_by`) VALUES
('INV-2026-001', 8, 8, 4, DATE_SUB(CURDATE(), INTERVAL 7 DAY), DATE_SUB(CURDATE(), INTERVAL 37 DAY), 45.00, 8.50, 3.83, 48.83, 'paid', 1),
('INV-2026-002', 9, 9, 5, DATE_SUB(CURDATE(), INTERVAL 5 DAY), DATE_SUB(CURDATE(), INTERVAL 35 DAY), 55.00, 8.50, 4.68, 59.68, 'paid', 1),
('INV-2026-003', NULL, 6, 1, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 75.50, 8.50, 6.42, 81.92, 'sent', 1);

-- Invoice Items
INSERT INTO `invoice_items` (`invoice_id`, `service_id`, `inventory_item_id`, `description`, `quantity`, `unit_price`, `total_price`, `item_type`) VALUES
-- Invoice 1 (Oil Change)
(1, 1, NULL, 'Oil Change Service', 1, 25.00, 25.00, 'service'),
(1, NULL, 1, '5W-30 Motor Oil 5L', 1, 35.99, 35.99, 'part'),
(1, NULL, 4, 'Oil Filter Standard', 1, 9.99, 9.99, 'part'),

-- Invoice 2 (Air Filter)  
(2, 5, NULL, 'Air Filter Replacement', 1, 25.00, 25.00, 'service'),
(2, NULL, 5, 'Air Filter Engine', 1, 24.99, 24.99, 'part'),
(2, NULL, 6, 'Cabin Air Filter', 1, 18.99, 18.99, 'part'),

-- Invoice 3 (Brake Service)
(3, 4, NULL, 'Brake Inspection Service', 1, 65.00, 65.00, 'service'),
(3, NULL, 9, 'Brake Pads Front', 1, 65.99, 65.99, 'part');

-- ================================================
-- 8. SAMPLE PAYMENTS  
-- ================================================

INSERT INTO `payments` (`invoice_id`, `client_id`, `payment_date`, `amount`, `payment_method`, `reference_number`, `status`, `processed_by`) VALUES
(1, 8, DATE_SUB(CURDATE(), INTERVAL 6 DAY), 48.83, 'credit_card', 'CC-20260212-001', 'completed', 1),
(2, 9, DATE_SUB(CURDATE(), INTERVAL 4 DAY), 59.68, 'cash', 'CASH-20260214-001', 'completed', 1);

-- ================================================
-- 9. SAMPLE RATINGS
-- ================================================

INSERT INTO `ratings` (`appointment_id`, `client_id`, `technician_id`, `service_quality_rating`, `timeliness_rating`, `professionalism_rating`, `overall_rating`, `review_title`, `review_text`, `would_recommend`) VALUES
(8, 8, 3, 5, 5, 5, 5.0, 'Excellent Service!', 'John did a fantastic job with my oil change. Very professional and completed on time.', 1),
(9, 9, 4, 4, 4, 5, 4.3, 'Good Service', 'Jane was very knowledgeable and explained everything clearly. Service was good overall.', 1);

-- ================================================
-- 10. SAMPLE NOTIFICATIONS
-- ================================================

INSERT INTO `notifications` (`recipient_id`, `title`, `message`, `type`, `priority`, `related_table`, `related_id`) VALUES
(1, 'New Appointment Scheduled', 'A new appointment has been scheduled for today at 2:00 PM', 'appointment', 'normal', 'appointments', 3),
(3, 'Assignment Updated', 'You have been assigned to appointment #1 scheduled for 9:00 AM', 'appointment', 'normal', 'assignments', 1),
(4, 'Service Completed', 'Your assigned service for appointment #2 is ready for review', 'appointment', 'normal', 'assignments', 2),
(6, 'Appointment Confirmed', 'Your appointment for tomorrow at 8:00 AM has been confirmed', 'appointment', 'normal', 'appointments', 4),
(1, 'Low Inventory Alert', 'Radiator Hose Upper is running low (8 remaining, minimum: 4)', 'warning', 'high', 'inventory', 8),
(1, 'Payment Received', 'Payment of $48.83 received for Invoice INV-2026-001', 'payment', 'normal', 'payments', 1);

-- ================================================
-- 11. SAMPLE SYSTEM SETTINGS
-- ================================================

INSERT INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `category`, `description`) VALUES
('company_name', 'VehiCare Service Center', 'string', 'general', 'Company name displayed in system'),
('company_address', '123 Service Street, Auto City, AC 12345', 'string', 'general', 'Company address for invoices'),
('company_phone', '+1 (555) 123-4567', 'string', 'general', 'Main company phone number'),
('company_email', 'info@vehicare.com', 'string', 'general', 'Main company email address'),
('default_tax_rate', '8.50', 'float', 'financial', 'Default tax rate percentage'),
('appointment_buffer_time', '15', 'integer', 'scheduling', 'Buffer time between appointments in minutes'),
('low_stock_threshold', '5', 'integer', 'inventory', 'Default minimum stock level for alerts'),
('invoice_payment_terms', '30', 'integer', 'financial', 'Default payment terms in days'),
('notification_auto_read_days', '30', 'integer', 'system', 'Days before notifications are auto-marked as read'),
('session_timeout', '3600', 'integer', 'security', 'User session timeout in seconds');

-- ================================================
-- 12. UPDATE SEQUENCES AND CLEANUP
-- ================================================

-- Update Auto Increment values to proper starting points
ALTER TABLE `users` AUTO_INCREMENT = 100;
ALTER TABLE `appointments` AUTO_INCREMENT = 1000;
ALTER TABLE `invoices` AUTO_INCREMENT = 10000;
ALTER TABLE `payments` AUTO_INCREMENT = 50000;
ALTER TABLE `notifications` AUTO_INCREMENT = 100000;