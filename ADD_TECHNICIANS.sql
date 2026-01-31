-- Add Technician Accounts to VehiCare System
-- These accounts are for the 8 technicians assigned to specific services

-- Insert Technician Users (password is hashed using password_hash() from PHP)
-- All passwords are set to "Tech@123" for initial setup

INSERT INTO `users` (`username`, `email`, `password`, `full_name`, `phone`, `role`, `status`, `created_at`, `updated_at`, `created_date`, `updated_date`, `last_login`) VALUES

-- 1. John Smith - Oil Change & Filter Specialist
('john.smith', 'john.smith@vehicare.com', '$2y$10$qwcqABm16JMQH3aY5ssAGuHy2Ai.SDntzMlfoLz91rKefh38EeFbC', 'John Smith', '09171234561', 'staff', 'active', NOW(), NOW(), NOW(), NOW(), NULL),

-- 2. Mike Johnson - Brake Service Specialist
('mike.johnson', 'mike.johnson@vehicare.com', '$2y$10$qwcqABm16JMQH3aY5ssAGuHy2Ai.SDntzMlfoLz91rKefh38EeFbC', 'Mike Johnson', '09171234562', 'staff', 'active', NOW(), NOW(), NOW(), NOW(), NULL),

-- 3. David Wilson - Tire Rotation Specialist
('david.wilson', 'david.wilson@vehicare.com', '$2y$10$qwcqABm16JMQH3aY5ssAGuHy2Ai.SDntzMlfoLz91rKefh38EeFbC', 'David Wilson', '09171234563', 'staff', 'active', NOW(), NOW(), NOW(), NOW(), NULL),

-- 4. Carlos Martinez - Battery Specialist
('carlos.martinez', 'carlos.martinez@vehicare.com', '$2y$10$qwcqABm16JMQH3aY5ssAGuHy2Ai.SDntzMlfoLz91rKefh38EeFbC', 'Carlos Martinez', '09171234564', 'staff', 'inactive', NOW(), NOW(), NOW(), NOW(), NULL),

-- 5. Robert Brown - Engine Diagnostics Specialist
('robert.brown', 'robert.brown@vehicare.com', '$2y$10$qwcqABm16JMQH3aY5ssAGuHy2Ai.SDntzMlfoLz91rKefh38EeFbC', 'Robert Brown', '09171234565', 'staff', 'active', NOW(), NOW(), NOW(), NOW(), NULL),

-- 6. James Anderson - Air Filter Specialist
('james.anderson', 'james.anderson@vehicare.com', '$2y$10$qwcqABm16JMQH3aY5ssAGuHy2Ai.SDntzMlfoLz91rKefh38EeFbC', 'James Anderson', '09171234566', 'staff', 'active', NOW(), NOW(), NOW(), NOW(), NULL),

-- 7. Thomas Lee - Suspension Specialist
('thomas.lee', 'thomas.lee@vehicare.com', '$2y$10$qwcqABm16JMQH3aY5ssAGuHy2Ai.SDntzMlfoLz91rKefh38EeFbC', 'Thomas Lee', '09171234567', 'staff', 'active', NOW(), NOW(), NOW(), NOW(), NULL),

-- 8. Patricia Garcia - Coolant Flush Specialist
('patricia.garcia', 'patricia.garcia@vehicare.com', '$2y$10$qwcqABm16JMQH3aY5ssAGuHy2Ai.SDntzMlfoLz91rKefh38EeFbC', 'Patricia Garcia', '09171234568', 'staff', 'active', NOW(), NOW(), NOW(), NOW(), NULL);

-- Note: Password is "Tech@123" (hashed using password_hash())
-- Username/Email credentials:
-- 1. john.smith / john.smith@vehicare.com
-- 2. mike.johnson / mike.johnson@vehicare.com
-- 3. david.wilson / david.wilson@vehicare.com
-- 4. carlos.martinez / carlos.martinez@vehicare.com (Inactive - for when busy)
-- 5. robert.brown / robert.brown@vehicare.com
-- 6. james.anderson / james.anderson@vehicare.com
-- 7. thomas.lee / thomas.lee@vehicare.com
-- 8. patricia.garcia / patricia.garcia@vehicare.com
