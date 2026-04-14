<?php

/**
 * Product Search Diagnostic Script
 * 
 * This script helps diagnose why product search is not working correctly.
 * It checks:
 * 1. Database connectivity
 * 2. Product count and existence
 * 3. Search index population
 * 4. Sample searches with different methods
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/config/database-connection.php';

echo "=== Product Search Diagnostic ===\n\n";

try {
  // 1. Check database connection
  echo "1. Database Connection: ";
  if ($db instanceof PDO) {
    echo "✓ Connected\n\n";
  } else {
    echo "✗ Connection failed\n";
    exit(1);
  }

  // 2. Check product table exists and count products
  echo "2. Product Table Status:\n";
  $stmt = $db->query('SELECT COUNT(*) as total, SUM(CASE WHEN is_active_prd = 1 THEN 1 ELSE 0 END) as active FROM product_prd');
  if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "   Total products: " . $result['total'] . "\n";
    echo "   Active products: " . $result['active'] . "\n\n";
  }

  // 3. Check search index table
  echo "3. Search Index Table:\n";
  $stmt = $db->query('SHOW TABLES LIKE "product_search_index_psi"');
  if ($stmt->rowCount() > 0) {
    echo "   ✓ Table exists\n";
    $stmt = $db->query('SELECT COUNT(*) as total FROM product_search_index_psi');
    if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
      echo "   Search index records: " . $result['total'] . "\n\n";
    }
  } else {
    echo "   ✗ Table does not exist\n\n";
  }

  // 4. Show sample products
  echo "4. Sample Products:\n";
  $stmt = $db->query('SELECT id_prd, name_prd, is_active_prd FROM product_prd LIMIT 5');
  if ($results = $stmt->fetchAll(PDO::FETCH_ASSOC)) {
    foreach ($results as $product) {
      $status = $product['is_active_prd'] ? '✓ Active' : '✗ Inactive';
      echo "   ID: " . $product['id_prd'] . " | Name: " . $product['name_prd'] . " | " . $status . "\n";
    }
    echo "\n";
  }

  // 5. Test search for "apples"
  echo "5. Search Test - Term: 'apples'\n";

  $searchTerm = 'apples';
  $likeSearch = '%' . $searchTerm . '%';

  // Test LIKE search only (no full-text)
  echo "   a) LIKE search only:\n";
  $likeQuery = 'SELECT p.id_prd, p.name_prd, p.description_prd, v.farm_name_ven 
        FROM product_prd p 
        JOIN product_category_pct c ON c.id_pct = p.id_pct_prd 
        JOIN vendor_ven v ON v.id_ven = p.id_ven_prd 
        WHERE p.is_active_prd = 1 
        AND (p.name_prd LIKE ? OR p.description_prd LIKE ? OR v.farm_name_ven LIKE ?)
        LIMIT 5';

  $stmt = $db->prepare($likeQuery);
  $stmt->execute([$likeSearch, $likeSearch, $likeSearch]);
  $likeResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

  if (count($likeResults) > 0) {
    echo "      ✓ Found " . count($likeResults) . " product(s):\n";
    foreach ($likeResults as $row) {
      echo "        - " . $row['name_prd'] . " (Vendor: " . $row['farm_name_ven'] . ")\n";
    }
  } else {
    echo "      ✗ No products found with LIKE search\n";
  }

  // Test full search query (with full-text)
  echo "\n   b) Full search with FULLTEXT (if available):\n";
  try {
    $fullQuery = 'SELECT DISTINCT p.id_prd, p.name_prd, p.description_prd, v.farm_name_ven 
            FROM product_prd p 
            JOIN product_category_pct c ON c.id_pct = p.id_pct_prd 
            JOIN vendor_ven v ON v.id_ven = p.id_ven_prd 
            LEFT JOIN product_search_index_psi psi ON psi.id_prd_psi = p.id_prd
            WHERE p.is_active_prd = 1 
            AND (COALESCE(MATCH(psi.search_text_psi) AGAINST(? IN BOOLEAN MODE), 0) 
                 OR p.name_prd LIKE ? 
                 OR p.description_prd LIKE ? 
                 OR v.farm_name_ven LIKE ?)
            LIMIT 5';

    $stmt = $db->prepare($fullQuery);
    $stmt->execute([$searchTerm . '*', $likeSearch, $likeSearch, $likeSearch]);
    $fullResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($fullResults) > 0) {
      echo "      ✓ Found " . count($fullResults) . " product(s):\n";
      foreach ($fullResults as $row) {
        echo "        - " . $row['name_prd'] . " (Vendor: " . $row['farm_name_ven'] . ")\n";
      }
    } else {
      echo "      ✗ No products found with full search\n";
    }
  } catch (Exception $e) {
    echo "      ! Error: " . $e->getMessage() . "\n";
  }

  // 6. List all categories
  echo "\n6. Available Categories:\n";
  $stmt = $db->query('SELECT id_pct, name_pct FROM product_category_pct');
  if ($results = $stmt->fetchAll(PDO::FETCH_ASSOC)) {
    foreach ($results as $cat) {
      echo "   - " . $cat['name_pct'] . " (ID: " . $cat['id_pct'] . ")\n";
    }
  }

  // 7. List all vendors with product counts
  echo "\n7. Vendors with Product Counts:\n";
  $stmt = $db->query('SELECT v.id_ven, v.farm_name_ven, COUNT(p.id_prd) as product_count 
        FROM vendor_ven v 
        LEFT JOIN product_prd p ON p.id_ven_prd = v.id_ven AND p.is_active_prd = 1 
        GROUP BY v.id_ven, v.farm_name_ven 
        ORDER BY product_count DESC 
        LIMIT 10');
  if ($results = $stmt->fetchAll(PDO::FETCH_ASSOC)) {
    foreach ($results as $vendor) {
      echo "   - " . $vendor['farm_name_ven'] . ": " . $vendor['product_count'] . " active product(s)\n";
    }
  }

  echo "\n=== Diagnostic Complete ===\n";
} catch (PDOException $e) {
  echo "Database Error: " . $e->getMessage() . "\n";
  exit(1);
} catch (Throwable $e) {
  echo "Error: " . $e->getMessage() . "\n";
  exit(1);
}
