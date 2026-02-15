<?php
require 'config/env.php';
require 'config/database-connection.php';
$db = require 'config/database-connection.php';

// Test the vendor growth query
$stmt = $db->query('
  SELECT 
    DATE_FORMAT(MIN(applied_date_ven), "%b %d") AS date_label,
    COUNT(*) AS vendor_count
  FROM vendor_ven
  WHERE application_status_ven = "approved" AND applied_date_ven >= DATE_SUB(NOW(), INTERVAL 4 WEEK)
  GROUP BY YEAR(applied_date_ven), WEEK(applied_date_ven)
  ORDER BY MIN(applied_date_ven)
');

$results = $stmt ? $stmt->fetchAll() : [];
echo "Vendor Growth Results:\n";
var_dump($results);

// Test recent products
$stmt = $db->query('
  SELECT p.id_prd, p.name_prd, p.is_active_prd, p.created_at_prd, v.id_ven, v.farm_name_ven, c.name_pct AS category
  FROM product_prd p
  JOIN vendor_ven v ON v.id_ven = p.id_ven_prd
  JOIN product_category_pct c ON c.id_pct = p.id_pct_prd
  ORDER BY p.created_at_prd DESC
  LIMIT 10
');
$products = $stmt ? $stmt->fetchAll() : [];
echo "\n\nRecent Products Results:\n";
var_dump($products);
