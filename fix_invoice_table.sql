-- Fix missing columns in VehiCare database
-- Run this if you get "Unknown column 'payment_status'" errors

-- Add payment_status column to invoices table if it doesn't exist
ALTER TABLE invoices ADD COLUMN IF NOT EXISTS payment_status ENUM('unpaid', 'partial', 'paid') DEFAULT 'unpaid';

-- Add other missing columns that might be needed
ALTER TABLE invoices ADD COLUMN IF NOT EXISTS subtotal DECIMAL(10,2) DEFAULT 0.00;
ALTER TABLE invoices ADD COLUMN IF NOT EXISTS tax_amount DECIMAL(10,2) DEFAULT 0.00;
ALTER TABLE invoices ADD COLUMN IF NOT EXISTS grand_total DECIMAL(10,2) NOT NULL DEFAULT 0.00;

-- Update existing invoices to have reasonable default values
UPDATE invoices SET 
    payment_status = 'unpaid' WHERE payment_status IS NULL,
    subtotal = COALESCE(subtotal, 0.00),
    tax_amount = COALESCE(tax_amount, 0.00),
    grand_total = COALESCE(grand_total, subtotal + tax_amount, 0.00);

-- Show the updated table structure
DESCRIBE invoices;