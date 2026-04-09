-- Add featured markets column to market_mkt table
-- Allows admins to feature specific markets on the home page

ALTER TABLE `market_mkt` 
ADD COLUMN `is_featured_mkt` TINYINT(1) DEFAULT 0 AFTER `is_active_mkt`,
ADD INDEX `idx_featured_mkt` (`is_featured_mkt`);

-- Optional: Set a few existing markets as featured if you want to bootstrap some data
-- Update this based on which markets you want featured
-- UPDATE market_mkt SET is_featured_mkt = 1 WHERE id_mkt IN (1, 2, 3);
