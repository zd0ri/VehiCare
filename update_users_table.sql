-- Add profile_picture column to users table if it doesn't exist
ALTER TABLE users ADD COLUMN profile_picture VARCHAR(255) DEFAULT NULL AFTER last_login;
ALTER TABLE users ADD COLUMN address TEXT DEFAULT NULL AFTER phone;
ALTER TABLE users ADD COLUMN city VARCHAR(100) DEFAULT NULL AFTER address;
ALTER TABLE users ADD COLUMN state VARCHAR(100) DEFAULT NULL AFTER city;
ALTER TABLE users ADD COLUMN zip_code VARCHAR(20) DEFAULT NULL AFTER state;
