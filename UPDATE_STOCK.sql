-- Update Stock Queries for Parts Inventory
-- These queries manage stock levels using the inventory table
-- Table Schema: inventory (inventory_id, part_id, quantity, last_updated)

-- ========================================
-- 1. DECREASE STOCK (when parts are used)
-- ========================================
-- Usage: When a technician uses a part for a service
UPDATE `inventory` SET `quantity` = `quantity` - 1, `last_updated` = NOW() WHERE `part_id` = 1;

-- Example: Decrease High Performance Air Filter by 2 units
UPDATE `inventory` SET `quantity` = `quantity` - 2, `last_updated` = NOW() WHERE `part_id` = 1;

-- Example: Decrease Ceramic Brake Pads by 1 unit
UPDATE `inventory` SET `quantity` = `quantity` - 1, `last_updated` = NOW() WHERE `part_id` = 2;

-- ========================================
-- 2. INCREASE STOCK (when parts are restocked)
-- ========================================
-- Example: Add 10 units to Oil Filter Kit
UPDATE `inventory` SET `quantity` = `quantity` + 10, `last_updated` = NOW() WHERE `part_id` = 3;

-- Example: Add 15 units to Engine Spark Plugs
UPDATE `inventory` SET `quantity` = `quantity` + 15, `last_updated` = NOW() WHERE `part_id` = 4;

-- ========================================
-- 3. SET EXACT STOCK (manual inventory)
-- ========================================
-- Example: Set Suspension Coil Springs to exactly 25 units
UPDATE `inventory` SET `quantity` = 25, `last_updated` = NOW() WHERE `part_id` = 5;

-- Example: Set Radiator Cooling Unit to exactly 30 units
UPDATE `inventory` SET `quantity` = 30, `last_updated` = NOW() WHERE `part_id` = 6;

-- ========================================
-- 4. INITIALIZE INVENTORY FOR ALL PARTS (50 units each)
-- ========================================
-- This inserts initial inventory records for all parts
INSERT INTO `inventory` (`part_id`, `quantity`, `last_updated`)
SELECT `part_id`, 50, NOW() FROM `parts`
ON DUPLICATE KEY UPDATE `quantity` = 50, `last_updated` = NOW();

-- ========================================
-- 5. VIEW LOW STOCK ITEMS (quantity <= 20)
-- ========================================
SELECT p.`part_id`, p.`part_name`, p.`brand`, i.`quantity`, i.`last_updated`
FROM `parts` p
LEFT JOIN `inventory` i ON p.`part_id` = i.`part_id`
WHERE i.`quantity` <= 20
ORDER BY i.`quantity` ASC;

-- ========================================
-- 6. VIEW OUT OF STOCK ITEMS (quantity = 0)
-- ========================================
SELECT p.`part_id`, p.`part_name`, p.`brand`, p.`price`, i.`quantity`
FROM `parts` p
LEFT JOIN `inventory` i ON p.`part_id` = i.`part_id`
WHERE i.`quantity` = 0 OR i.`quantity` IS NULL
ORDER BY p.`part_name`;

-- ========================================
-- 7. INVENTORY STATISTICS
-- ========================================
SELECT 
  COUNT(p.`part_id`) as `total_parts`,
  SUM(i.`quantity`) as `total_quantity`,
  SUM(p.`price` * i.`quantity`) as `total_inventory_value`,
  COUNT(CASE WHEN i.`quantity` = 0 OR i.`quantity` IS NULL THEN 1 END) as `out_of_stock_count`,
  COUNT(CASE WHEN i.`quantity` <= 20 AND i.`quantity` > 0 THEN 1 END) as `low_stock_count`,
  COUNT(CASE WHEN i.`quantity` > 20 THEN 1 END) as `in_stock_count`,
  AVG(i.`quantity`) as `average_quantity`
FROM `parts` p
LEFT JOIN `inventory` i ON p.`part_id` = i.`part_id`;

-- ========================================
-- 8. DETAILED INVENTORY REPORT
-- ========================================
SELECT 
  p.`part_id`,
  p.`part_name`,
  p.`brand`,
  p.`price`,
  COALESCE(i.`quantity`, 0) as `quantity`,
  (p.`price` * COALESCE(i.`quantity`, 0)) as `total_value`,
  i.`last_updated`,
  CASE 
    WHEN COALESCE(i.`quantity`, 0) > 20 THEN 'In Stock'
    WHEN COALESCE(i.`quantity`, 0) > 0 THEN 'Low Stock'
    ELSE 'Out of Stock'
  END as `status`
FROM `parts` p
LEFT JOIN `inventory` i ON p.`part_id` = i.`part_id`
ORDER BY p.`part_name`;

-- ========================================
-- 9. BATCH UPDATE QUANTITY FOR MULTIPLE PARTS
-- ========================================
-- Example: Decrease quantity for multiple parts
UPDATE `inventory` SET `quantity` = `quantity` - 1, `last_updated` = NOW()
WHERE `part_id` IN (1, 2, 3, 4);

-- Example: Restock multiple parts
UPDATE `inventory` SET `quantity` = `quantity` + 5, `last_updated` = NOW()
WHERE `part_id` IN (5, 6, 7, 8);

-- ========================================
-- 10. UPDATE BY PART NAME (using JOIN)
-- ========================================
-- Example: Decrease quantity for High Performance Air Filter
UPDATE `inventory` i
JOIN `parts` p ON i.`part_id` = p.`part_id`
SET i.`quantity` = i.`quantity` - 1, i.`last_updated` = NOW()
WHERE p.`part_name` = 'High Performance Air Filter';

-- Example: Set quantity for parts from specific brand
UPDATE `inventory` i
JOIN `parts` p ON i.`part_id` = p.`part_id`
SET i.`quantity` = 25, i.`last_updated` = NOW()
WHERE p.`brand` = 'AutoParts Inc';
