-- Insert Admin Account
-- Email: admin@vehicare.com
-- Password: VehiCare@2026Admin
INSERT INTO users (username, email, password, full_name, phone, role, status) 
VALUES ('admin_user', 'admin@vehicare.com', '$2y$10$IN8g1ooEgn6zup7MdNH0zezcBX2xx6GmZkEb58ifoFqY8/n/U05Pu', 'VehiCare Administrator', '+1-555-0100', 'admin', 'active');

-- Insert Staff Account
-- Email: staff@vehicare.com
-- Password: VehiCare@2026Staff
INSERT INTO users (username, email, password, full_name, phone, role, status) 
VALUES ('staff_user', 'staff@vehicare.com', '$2y$10$hkqgpmntm6V.EDUGg3cGCuzEQDVp2ykEKCM7N3sJX2Qu3aEPiZIRS', 'VehiCare Staff Member', '+1-555-0200', 'staff', 'active');
