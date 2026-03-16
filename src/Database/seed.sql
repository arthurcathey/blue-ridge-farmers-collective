-- Blue Ridge Farmers Collective - Seed Data
-- Comprehensive test data for development and testing
-- Created: March 16, 2026

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- ROLES (if not already present)
-- ============================================
INSERT IGNORE INTO `role_rol` (`id_rol`, `name_rol`, `description_rol`, `permission_level_rol`) VALUES
(1, 'public', 'Public user with no special permissions', 0),
(2, 'vendor', 'Vendor account for market booth holders', 2),
(3, 'admin', 'Market administrator with management capabilities', 3),
(4, 'super_admin', 'Network administrator with full system access', 4);

-- ============================================
-- TEST ACCOUNTS
-- ============================================
-- New test vendors (existing accounts in your DB will be used)
-- seed_vendor1: seedvendor1 / Test123!
-- seed_vendor2: seedvendor2 / Test123!

INSERT IGNORE INTO `account_acc` (`id_acc`, `username_acc`, `email_acc`, `password_hash_acc`, `id_rol_acc`, `is_active_acc`, `is_email_verified_acc`, `created_at_acc`) VALUES
(20, 'seedvendor1', 'seedvendor1@blueridge.test', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36P4/lSa', 2, 1, 1, NOW()),
(21, 'seedvendor2', 'seedvendor2@blueridge.test', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36P4/lSa', 2, 1, 1, NOW());

-- ============================================
-- MARKETS (NC Farmers Markets)
-- ============================================
INSERT IGNORE INTO `market_mkt` (`id_mkt`, `name_mkt`, `slug_mkt`, `city_mkt`, `state_mkt`, `zip_mkt`, `contact_name_mkt`, `contact_email_mkt`, `contact_phone_mkt`, `default_location_mkt`, `latitude_mkt`, `longitude_mkt`, `timezone_mkt`, `is_active_mkt`, `created_at_mkt`) VALUES
(1, 'Asheville City Market', 'asheville-city-market', 'Asheville', 'NC', '28801', 'Sarah Mitchell', 'sarah@ashevillecitymarket.org', '(828) 252-8899', 'Pack Square Park', 35.5951, -82.5516, 'America/New_York', 1, NOW()),
(2, 'Riverside Farmers Market', 'riverside-farmers-market', 'Asheville', 'NC', '28806', 'Marcus Johnson', 'marcus@riversidemarkets.org', '(828) 555-0123', 'Riverside Park', 35.6066, -82.5522, 'America/New_York', 1, NOW()),
(3, 'Blue Hill Market', 'blue-hill-market', 'Brevard', 'NC', '28712', 'Emily Torres', 'emily@bluehillmarket.org', '(828) 555-0456', 'Blue Hill Community Center', 35.2365, -82.7337, 'America/New_York', 1, NOW()),
(4, 'Hendersonville Farmers Market', 'hendersonville-farmers-market', 'Hendersonville', 'NC', '28739', 'David Chen', 'david@hendersonvillemarkets.org', '(828) 555-0789', 'Main Street Pavilion', 35.3165, -82.4682, 'America/New_York', 1, NOW());

-- ============================================
-- VENDORS (New Showcase Vendors)
-- ============================================
INSERT IGNORE INTO `vendor_ven` (`id_ven`, `id_acc_ven`, `farm_name_ven`, `farm_description_ven`, `philosophy_ven`, `phone_ven`, `website_ven`, `primary_categories_ven`, `production_methods_ven`, `years_in_operation_ven`, `city_ven`, `state_ven`, `zip_ven`, `latitude_ven`, `longitude_ven`, `application_status_ven`, `is_featured_ven`, `created_at_ven`) VALUES
(8, 20, 'Sunny Slope Orchards', 'Family-owned apple orchard with a 50-year tradition of growing premium heirloom varieties in the Blue Ridge Mountains.', 'We believe in sustainable farming practices and preserving the land for future generations.', '(828) 555-2001', 'https://sunnyslope.local', '["Produce", "Specialty"]', '["Organic", "Pesticide-Free"]', 50, 'Asheville', 'NC', '28801', 35.6000, -82.5400, 'approved', 1, NOW()),
(9, 21, 'Heritage Valley Farms', 'Organic vegetable farm specializing in heirloom tomatoes, seasonal greens, and root vegetables.', 'Organic and local starts small but creates real change in our food systems.', '(828) 555-2002', 'https://heritagevalley.local', '["Produce"]', '["Organic", "Certified-Organic"]', 12, 'Brevard', 'NC', '28712', 35.2400, -82.7300, 'approved', 1, NOW()),
(10, 20, 'Riverside Artisan Bakery', 'Small-batch sourdough, pastries, and specialty breads using heritage grains and natural fermentation.', 'Traditional methods meet modern taste—supporting local and sustainable.', '(828) 555-2003', 'https://riversideartisan.local', '["Baked Goods"]', '["Small-Batch", "Heritage-Grains"]', 8, 'Hendersonville', 'NC', '28739', 35.3200, -82.4700, 'approved', 1, NOW()),
(11, 21, 'Mountain Honey Apiary', 'Raw, unfiltered wildflower and clover honey harvested from pristine mountain apiaries.', 'Bees and beekeepers together - supporting pollinator health and local ecosystems.', '(828) 555-2004', 'https://mountainhoney.local', '["Pantry", "Honey"]', '["Raw", "Unfiltered"]', 6, 'Brevard', 'NC', '28712', 35.2350, -82.7350, 'approved', 1, NOW()),
(12, 20, 'Wildflower Dairy Co.', 'Small-batch artisan cheese and dairy products made from grass-fed cow milk.', 'Quality over quantity. Handcrafted dairy traditions.', '(828) 555-2005', 'https://wildflowerdairy.local', '["Dairy"]', '["Grass-Fed", "Artisan"]', 7, 'Asheville', 'NC', '28801', 35.6050, -82.5450, 'approved', 0, NOW());

-- ============================================
-- PRODUCT CATEGORIES
-- ============================================
INSERT IGNORE INTO `product_category_pct` (`id_pct`, `name_pct`, `icon_pct`, `color_hex_pct`, `display_order_pct`) VALUES
(1, 'Produce', '🥬', '#22c55e', 1),
(2, 'Dairy', '🧀', '#f59e0b', 2),
(3, 'Baked Goods', '🍞', '#d4a373', 3),
(4, 'Meat', '🥩', '#dc2626', 4),
(5, 'Pantry', '🥫', '#8b7355', 5),
(6, 'Honey', '🍯', '#fbbf24', 6),
(7, 'Beverages', '🧃', '#3b82f6', 7),
(8, 'Flowers', '🌸', '#ec4899', 8);

-- ============================================
-- PRODUCTS
-- ============================================
INSERT IGNORE INTO `product_prd` (`id_prd`, `id_ven_prd`, `id_pct_prd`, `name_prd`, `description_prd`, `is_active_prd`, `created_at_prd`) VALUES
-- Sunny Slope Orchards (vendor 8)
(30, 8, 1, 'Honeycrisp Apples', 'Crisp, sweet apples perfect for fresh eating and storage. Naturally ripened in mountain orchards.', 1, NOW()),
(31, 8, 1, 'Fuji Apples', 'Dense, sweet apples with a perfect balance of sugar and acid. Excellent for pies and storage.', 1, NOW()),
(32, 8, 5, 'Apple Cider', 'Fresh-pressed, unpasteurized apple cider. Peak season fall through winter.', 1, NOW()),

-- Heritage Valley Farms (vendor 9)
(33, 9, 1, 'Heirloom Tomatoes', 'Mixed heirloom varieties in red, yellow, and striped. Rich, complex flavor profiles.', 1, NOW()),
(34, 9, 1, 'Seasonal Greens Mix', 'Rotating selection of lettuces, spinach, kale, and specialty greens. Pesticide-free.', 1, NOW()),
(35, 9, 1, 'Root Vegetable Bundle', 'Carrots, beets, turnips, and parsnips. Organic and locally grown.', 1, NOW()),
(36, 9, 1, 'Fresh Herbs', 'Basil, cilantro, parsley, dill, and seasonal herbs. Grown in certified organic greenhouses.', 1, NOW()),

-- Riverside Artisan Bakery (vendor 10)
(37, 10, 3, 'Sourdough Loaf', 'Traditional long-fermented sourdough with a crispy crust and complex flavor. Made daily.', 1, NOW()),
(38, 10, 3, 'Whole Grain Bread', 'Heritage grain blends including spelt, einkorn, and rye. Naturally leavened.', 1, NOW()),
(39, 10, 3, 'Croissants', 'Butter-laminated French-style croissants. Made fresh several times per week.', 1, NOW()),
(40, 10, 3, 'Seasonal Pastries', 'Danish, pain au chocolat, and fruit tarts using seasonal, local fruits.', 1, NOW()),

-- Mountain Honey Apiary (vendor 11)
(41, 11, 6, 'Wildflower Honey', 'Raw, unfiltered wildflower honey. Support local pollinator ecosystems.', 1, NOW()),
(42, 11, 6, 'Clover Honey', 'Pure clover honey with a mild, sweet flavor profile and creamy texture.', 1, NOW()),
(43, 11, 6, 'Honey Sampler', 'Three 4oz jars of different honey varieties for comparison tasting.', 1, NOW()),
(44, 11, 5, 'Honey Sticks', 'Portable honey in individual sticks. Perfect for tea, smoothies, or on-the-go.', 1, NOW()),

-- Wildflower Dairy Co. (vendor 12)
(45, 12, 2, 'Fresh Mozzarella', 'Hand-stretched fresh mozzarella made daily from grass-fed milk.', 1, NOW()),
(46, 12, 2, 'Aged Cheddar', 'Creamy aged cheddar cheese aged for 12 months. Sharp and complex.', 1, NOW()),
(47, 12, 2, 'Artisan Yogurt', 'Thick, creamy yogurt with live cultures. Available in plain and seasonal flavors.', 1, NOW());

-- ============================================
-- PRODUCT SEASONALITY
-- ============================================
-- Apples: August - February (peak: Sept-Feb)
INSERT IGNORE INTO `product_seasonality_pse` (`id_pse`, `id_prd_pse`, `month_pse`, `is_peak_season_pse`) VALUES
(40, 30, 8, 0), (41, 30, 9, 1), (42, 30, 10, 1), (43, 30, 11, 1), (44, 30, 12, 1), (45, 30, 1, 1), (46, 30, 2, 0),
(47, 31, 8, 0), (48, 31, 9, 1), (49, 31, 10, 1), (50, 31, 11, 1), (51, 31, 12, 1), (52, 31, 1, 1), (53, 31, 2, 0);

-- Tomatoes: June - September (peak: July-Sept)
INSERT IGNORE INTO `product_seasonality_pse` (`id_pse`, `id_prd_pse`, `month_pse`, `is_peak_season_pse`) VALUES
(54, 33, 6, 0), (55, 33, 7, 1), (56, 33, 8, 1), (57, 33, 9, 1);

-- Greens: Year-round (peak: spring, fall)
INSERT IGNORE INTO `product_seasonality_pse` (`id_pse`, `id_prd_pse`, `month_pse`, `is_peak_season_pse`) VALUES
(58, 34, 1, 1), (59, 34, 2, 1), (60, 34, 3, 1), (61, 34, 4, 1), 
(62, 34, 5, 0), (63, 34, 6, 0), (64, 34, 7, 0), (65, 34, 8, 0),
(66, 34, 9, 1), (67, 34, 10, 1), (68, 34, 11, 1), (69, 34, 12, 1);

-- ============================================
-- VENDOR MARKET ASSOCIATIONS
-- ============================================
INSERT IGNORE INTO `vendor_market_venmkt` (`id_venmkt`, `id_ven_venmkt`, `id_mkt_venmkt`, `membership_status_venmkt`, `approved_date_venmkt`, `id_acc_approved_by_venmkt`, `booth_preference_venmkt`, `is_featured_venmkt`, `created_at_venmkt`) VALUES
(10, 8, 1, 'approved', '2026-01-15', 1, 'A-12', 1, NOW()),
(11, 8, 2, 'approved', '2026-01-20', 1, 'B-05', 0, NOW()),
(12, 9, 3, 'approved', '2025-11-10', 1, 'A-08', 1, NOW()),
(13, 10, 4, 'approved', '2026-02-01', 1, 'B-10', 0, NOW()),
(14, 11, 3, 'approved', '2025-12-15', 1, 'A-03', 1, NOW()),
(15, 12, 1, 'approved', '2026-01-10', 1, 'A-15', 1, NOW());

-- ============================================
-- MARKET DATES (Next 3 Months)
-- ============================================
INSERT IGNORE INTO `market_date_mda` (`id_mda`, `id_mkt_mda`, `date_mda`, `start_time_mda`, `end_time_mda`, `location_mda`, `status_mda`, `weather_status_mda`, `notes_mda`, `created_at_mda`) VALUES
-- Asheville City Market
(1, 1, '2026-03-20', '08:00:00', '14:00:00', 'Pack Square Park', 'confirmed', NULL, '', NOW()),
(2, 1, '2026-03-27', '08:00:00', '14:00:00', 'Pack Square Park', 'scheduled', NULL, '', NOW()),
(3, 1, '2026-04-03', '08:00:00', '14:00:00', 'Pack Square Park', 'scheduled', NULL, '', NOW()),
(4, 1, '2026-04-10', '08:00:00', '14:00:00', 'Pack Square Park', 'scheduled', NULL, '', NOW()),
(5, 1, '2026-04-17', '08:00:00', '14:00:00', 'Pack Square Park', 'scheduled', NULL, '', NOW()),
(6, 1, '2026-05-01', '08:00:00', '14:00:00', 'Pack Square Park', 'scheduled', NULL, '', NOW()),

-- Riverside Farmers Market
(7, 2, '2026-03-28', '08:00:00', '14:00:00', 'Riverside Park', 'scheduled', NULL, '', NOW()),
(8, 2, '2026-04-04', '08:00:00', '14:00:00', 'Riverside Park', 'scheduled', NULL, '', NOW()),
(9, 2, '2026-04-11', '08:00:00', '14:00:00', 'Riverside Park', 'scheduled', NULL, '', NOW()),
(10, 2, '2026-04-18', '08:00:00', '14:00:00', 'Riverside Park', 'scheduled', NULL, '', NOW()),

-- Blue Hill Market
(11, 3, '2026-03-21', '09:00:00', '13:00:00', 'Blue Hill Community Center', 'scheduled', NULL, '', NOW()),
(12, 3, '2026-04-04', '09:00:00', '13:00:00', 'Blue Hill Community Center', 'scheduled', NULL, '', NOW()),
(13, 3, '2026-04-18', '09:00:00', '13:00:00', 'Blue Hill Community Center', 'scheduled', NULL, '', NOW()),

-- Hendersonville Farmers Market
(14, 4, '2026-03-22', '09:00:00', '14:00:00', 'Main Street Pavilion', 'scheduled', NULL, '', NOW()),
(15, 4, '2026-04-05', '09:00:00', '14:00:00', 'Main Street Pavilion', 'scheduled', NULL, '', NOW()),
(16, 4, '2026-04-19', '09:00:00', '14:00:00', 'Main Street Pavilion', 'scheduled', NULL, '', NOW());

-- ============================================
-- VENDOR ATTENDANCE
-- ============================================
INSERT IGNORE INTO `vendor_attendance_vat` (`id_vat`, `id_ven_vat`, `id_mda_vat`, `status_vat`, `booth_number_vat`, `declared_at_vat`) VALUES
(20, 8, 1, 'confirmed', 'A-12', NOW()),
(21, 8, 2, 'intended', 'A-12', NOW()),
(22, 9, 11, 'intended', 'A-08', NOW()),
(23, 10, 14, 'confirmed', 'B-10', NOW()),
(24, 11, 11, 'confirmed', 'A-03', NOW()),
(25, 12, 1, 'intended', 'A-15', NOW());

-- ============================================
-- REVIEWS (Vendor Reviews)
-- ============================================
INSERT IGNORE INTO `vendor_review_vre` (`id_vre`, `id_ven_vre`, `customer_name_vre`, `rating_vre`, `review_text_vre`, `market_date_vre`, `is_verified_purchase_vre`, `is_approved_vre`, `is_featured_vre`, `created_at_vre`) VALUES
(20, 8, 'John Smith', 5, 'Fantastic apples! The Honeycrisp were the crispiest I\'ve ever had. Will be back every week.', '2026-03-13', 1, 1, 1, NOW() - INTERVAL 7 DAY),
(21, 8, 'Maria Garcia', 5, 'Best local apples in town. Fresh and delicious. Support local farming!', '2026-03-06', 1, 1, 0, NOW() - INTERVAL 10 DAY),
(22, 8, 'David Lee', 4, 'Good quality, great flavors. Prices are fair for organic, local produce.', '2026-02-27', 1, 1, 0, NOW() - INTERVAL 14 DAY),

(23, 9, 'Sarah M.', 5, 'The best tomatoes I\'ve ever tasted! Heirloom varieties have so much flavor compared to supermarket.', '2026-03-14', 1, 1, 1, NOW() - INTERVAL 6 DAY),
(24, 9, 'Emma Wilson', 5, 'Fresh greens are incredibly flavorful and last so long in the fridge. Definitely organic!', '2026-03-07', 1, 1, 0, NOW() - INTERVAL 9 DAY),
(25, 9, 'Tom Anderson', 4, 'Love supporting local organic farms. Prices reflect the quality and care.', '2026-02-28', 1, 1, 0, NOW() - INTERVAL 13 DAY),

(26, 10, 'Catherine Davis', 5, 'The sourdough from Riverside Artisan is absolutely phenomenal. That crust, that crumb structure!', '2026-03-14', 1, 1, 1, NOW() - INTERVAL 6 DAY),
(27, 10, 'Robert Taylor', 4, 'Great fresh bread. The croissants are buttery perfection. A bit pricey but worth the quality.', '2026-03-07', 1, 1, 0, NOW() - INTERVAL 9 DAY),

(28, 11, 'Lisa Chen', 5, 'Raw honey from local bees - exactly what I was looking for. Pure, delicious, supports pollinators!', '2026-03-14', 1, 1, 1, NOW() - INTERVAL 6 DAY),
(29, 11, 'Michael Johnson', 5, 'Best-tasting honey I\'ve had. The flavor is complex and the color is beautiful.', '2026-03-08', 1, 1, 0, NOW() - INTERVAL 8 DAY),

(30, 12, 'Jessica Brown', 5, 'Their mozzarella is incredible - so fresh and creamy. Makes the best caprese salads!', '2026-03-12', 1, 1, 1, NOW() - INTERVAL 7 DAY),
(31, 12, 'Kevin White', 4, 'Artisan yogurt tastes like pure cream. So much better than store brands.', '2026-03-09', 1, 1, 0, NOW() - INTERVAL 8 DAY);

-- ============================================
-- REVIEW RESPONSES (Vendor Replies)
-- ============================================
INSERT IGNORE INTO `review_response_rre` (`id_rre`, `id_vre_rre`, `id_ven_rre`, `response_text_rre`, `created_at_rre`) VALUES
(10, 20, 8, 'Thank you so much! We\'re proud of our apples and love serving the Asheville community. See you next week!', NOW() - INTERVAL 6 DAY),
(11, 23, 9, 'We\'re thrilled you enjoyed the heirlooms! Each variety has its own story. Come back in summer for peak tomato season!', NOW() - INTERVAL 5 DAY),
(12, 26, 10, 'We\'re so grateful! Our bakers wake up early every morning to create the best bread possible. Croissant season is here!', NOW() - INTERVAL 5 DAY),
(13, 28, 11, 'Thank you for supporting local beekeeping! Raw honey truly is liquid gold. See you at the market!', NOW() - INTERVAL 5 DAY),
(14, 30, 12, 'We love crafting the freshest dairy! Grass-fed milk makes all the difference in taste and quality.', NOW() - INTERVAL 6 DAY);

SET FOREIGN_KEY_CHECKS = 1;
