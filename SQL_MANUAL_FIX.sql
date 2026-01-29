-- VehiCare Database Manual Fix Script
-- Run this in phpMyAdmin or MySQL command line to fix the missing user_id column

-- 1. Check if vehicles table exists
SHOW TABLES LIKE 'vehicles';

-- 2. Check current structure of vehicles table
DESCRIBE vehicles;

-- 3. Add user_id column if it's missing (choose ONE of the following)
-- If vehicles table is empty or just created:
ALTER TABLE vehicles ADD COLUMN user_id INT NOT NULL DEFAULT 1 AFTER vehicle_id;

-- 4. Add foreign key constraint (this links vehicles to users)
ALTER TABLE vehicles ADD CONSTRAINT fk_vehicles_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE;

-- 5. Add index for better query performance
ALTER TABLE vehicles ADD INDEX idx_user_id (user_id);

-- 6. Verify the fix worked
DESCRIBE vehicles;
SELECT * FROM vehicles LIMIT 1;

-- If you see the following, the fix was successful:
-- ✓ user_id column appears in the DESCRIBE output
-- ✓ Foreign key constraint exists
-- ✓ Index idx_user_id exists

-- To verify other tables have the correct structure:
DESCRIBE appointments;
DESCRIBE service_history;
DESCRIBE users;

-- If you need to fix other tables, use similar ALTER TABLE commands:
-- ALTER TABLE table_name ADD COLUMN column_name INT NOT NULL;
-- ALTER TABLE table_name ADD CONSTRAINT fk_name FOREIGN KEY (column_name) REFERENCES other_table(id) ON DELETE CASCADE;
