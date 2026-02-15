<?php

/**
 * Database Connection Proof Page
 * Demonstrates that PHP and MySQL database are working correctly
 * Displays content from the Blue Ridge Farmers Collective database
 */

declare(strict_types=1);

try {
  require_once dirname(__DIR__) . '/config/env.php';
  $pdo = require_once dirname(__DIR__) . '/config/database-connection.php';

  $connected = true;
  $selectedDb = 'blueridge_farmers_db';
  $timestamp = date('Y-m-d H:i:s');
} catch (Exception $e) {
  $connected = false;
  $error = $e->getMessage();
  $timestamp = date('Y-m-d H:i:s');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Blue Ridge Farmers Collective</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      padding: 20px;
    }

    .container {
      max-width: 1000px;
      margin: 0 auto;
    }

    .header {
      background: white;
      padding: 30px;
      border-radius: 8px 8px 0 0;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      border-left: 5px solid #667eea;
    }

    .header h1 {
      color: #333;
      margin-bottom: 10px;
    }

    .header p {
      color: #666;
      font-size: 14px;
    }

    .status {
      background: white;
      padding: 20px 30px;
      border-bottom: 1px solid #eee;
    }

    .status-badge {
      display: inline-block;
      padding: 8px 12px;
      border-radius: 4px;
      font-weight: bold;
      font-size: 14px;
    }

    .status-success {
      background: #d4edda;
      color: #155724;
    }

    .status-error {
      background: #f8d7da;
      color: #721c24;
    }

    .content {
      background: white;
      padding: 30px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .section {
      margin-bottom: 30px;
    }

    .section h2 {
      color: #333;
      margin-bottom: 15px;
      font-size: 18px;
      border-bottom: 2px solid #667eea;
      padding-bottom: 10px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }

    th {
      background: #f5f5f5;
      color: #333;
      padding: 12px;
      text-align: left;
      font-weight: 600;
      border-bottom: 2px solid #ddd;
    }

    td {
      padding: 12px;
      border-bottom: 1px solid #eee;
    }

    tr:hover {
      background: #f9f9f9;
    }

    .no-data {
      color: #666;
      font-style: italic;
      padding: 20px;
      background: #f9f9f9;
      border-radius: 4px;
      text-align: center;
    }

    .error-message {
      background: #f8d7da;
      color: #721c24;
      padding: 15px;
      border-radius: 4px;
      border: 1px solid #f5c6cb;
    }

    .footer {
      background: white;
      padding: 20px 30px;
      border-radius: 0 0 8px 8px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      border-top: 1px solid #eee;
      color: #666;
      font-size: 13px;
      text-align: center;
    }

    .info-box {
      background: #e7f3ff;
      border-left: 4px solid #2196F3;
      padding: 12px;
      margin-bottom: 15px;
      border-radius: 4px;
    }

    .info-box strong {
      color: #1976D2;
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="header">
      <h1>🌾 Database Connection Proof</h1>
      <p>Blue Ridge Farmers Collective - WEB-289</p>
    </div>

    <div class="status">
      <?php if ($connected): ?>
        <span class="status-badge status-success">✓ Connected</span>
      <?php else: ?>
        <span class="status-badge status-error">✗ Connection Failed</span>
      <?php endif; ?>
    </div>

    <div class="content">
      <?php if (!$connected): ?>
        <div class="error-message">
          <strong>Database Connection Error:</strong><br>
          <?= htmlspecialchars($error) ?>
        </div>
      <?php else: ?>
        <div class="section">
          <h2>Connection Information</h2>
          <div class="info-box">
            <strong>Database:</strong> blueridge_farmers_db<br>
            <strong>Host:</strong> 127.0.0.1:3306<br>
            <strong>Status:</strong> Connected and Operational<br>
            <strong>Timestamp:</strong> <?= $timestamp ?>
          </div>
        </div>

        <div class="section">
          <h2>User Roles</h2>
          <?php
          try {
            $stmt = $pdo->query('SELECT id_rol, name_rol, description_rol, permission_level_rol FROM role_rol ORDER BY permission_level_rol');
            $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($roles)):
          ?>
              <table>
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Role Name</th>
                    <th>Description</th>
                    <th>Permission Level</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($roles as $role): ?>
                    <tr>
                      <td><?= htmlspecialchars((string)$role['id_rol']) ?></td>
                      <td><strong><?= htmlspecialchars($role['name_rol']) ?></strong></td>
                      <td><?= htmlspecialchars($role['description_rol'] ?? 'N/A') ?></td>
                      <td><?= htmlspecialchars((string)$role['permission_level_rol']) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php else: ?>
              <div class="no-data">No roles found in database</div>
          <?php endif;
          } catch (Exception $e) {
            echo '<div class="error-message">Error fetching roles: ' . htmlspecialchars($e->getMessage()) . '</div>';
          }
          ?>
        </div>

        <div class="section">
          <h2>Vendors</h2>
          <?php
          try {
            $stmt = $pdo->query('
                            SELECT v.id_ven, v.farm_name_ven, v.city_ven, v.state_ven, v.application_status_ven, v.created_at_ven
                            FROM vendor_ven v
                            ORDER BY v.created_at_ven DESC
                            LIMIT 10
                        ');
            $vendors = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($vendors)):
          ?>
              <table>
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Farm Name</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Created</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($vendors as $vendor): ?>
                    <tr>
                      <td><?= htmlspecialchars((string)$vendor['id_ven']) ?></td>
                      <td><strong><?= htmlspecialchars($vendor['farm_name_ven']) ?></strong></td>
                      <td><?= htmlspecialchars($vendor['city_ven']) ?>, <?= htmlspecialchars($vendor['state_ven']) ?></td>
                      <td>
                        <span style="background: <?= $vendor['application_status_ven'] === 'approved' ? '#d4edda' : '#fff3cd' ?>; padding: 4px 8px; border-radius: 3px; font-size: 12px;">
                          <?= htmlspecialchars(ucfirst($vendor['application_status_ven'])) ?>
                        </span>
                      </td>
                      <td><?= htmlspecialchars(date('M d, Y', strtotime($vendor['created_at_ven']))) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php else: ?>
              <div class="no-data">No vendors found in database</div>
          <?php endif;
          } catch (Exception $e) {
            echo '<div class="error-message">Error fetching vendors: ' . htmlspecialchars($e->getMessage()) . '</div>';
          }
          ?>
        </div>

        <div class="section">
          <h2>Products</h2>
          <?php
          try {
            $stmt = $pdo->query('
                            SELECT p.id_prd, p.name_prd, v.farm_name_ven, c.name_pct, p.is_active_prd, p.created_at_prd
                            FROM product_prd p
                            JOIN vendor_ven v ON v.id_ven = p.id_ven_prd
                            JOIN product_category_pct c ON c.id_pct = p.id_pct_prd
                            ORDER BY p.created_at_prd DESC
                            LIMIT 10
                        ');
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($products)):
          ?>
              <table>
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Product Name</th>
                    <th>Vendor</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Created</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($products as $product): ?>
                    <tr>
                      <td><?= htmlspecialchars((string)$product['id_prd']) ?></td>
                      <td><strong><?= htmlspecialchars($product['name_prd']) ?></strong></td>
                      <td><?= htmlspecialchars($product['farm_name_ven']) ?></td>
                      <td><?= htmlspecialchars($product['name_pct']) ?></td>
                      <td>
                        <span style="background: <?= $product['is_active_prd'] ? '#d4edda' : '#f8d7da' ?>; padding: 4px 8px; border-radius: 3px; font-size: 12px;">
                          <?= $product['is_active_prd'] ? 'Active' : 'Inactive' ?>
                        </span>
                      </td>
                      <td><?= htmlspecialchars(date('M d, Y', strtotime($product['created_at_prd']))) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php else: ?>
              <div class="no-data">No products found in database</div>
          <?php endif;
          } catch (Exception $e) {
            echo '<div class="error-message">Error fetching products: ' . htmlspecialchars($e->getMessage()) . '</div>';
          }
          ?>
        </div>

        <div class="section">
          <h2>Markets</h2>
          <?php
          try {
            $stmt = $pdo->query('
                            SELECT id_mkt, name_mkt, city_mkt, state_mkt, is_active_mkt, created_at_mkt
                            FROM market_mkt
                            ORDER BY created_at_mkt DESC
                            LIMIT 10
                        ');
            $markets = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($markets)):
          ?>
              <table>
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Market Name</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Created</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($markets as $market): ?>
                    <tr>
                      <td><?= htmlspecialchars((string)$market['id_mkt']) ?></td>
                      <td><strong><?= htmlspecialchars($market['name_mkt']) ?></strong></td>
                      <td><?= htmlspecialchars($market['city_mkt']) ?>, <?= htmlspecialchars($market['state_mkt']) ?></td>
                      <td>
                        <span style="background: <?= $market['is_active_mkt'] ? '#d4edda' : '#f8d7da' ?>; padding: 4px 8px; border-radius: 3px; font-size: 12px;">
                          <?= $market['is_active_mkt'] ? 'Active' : 'Inactive' ?>
                        </span>
                      </td>
                      <td><?= htmlspecialchars(date('M d, Y', strtotime($market['created_at_mkt']))) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php else: ?>
              <div class="no-data">No markets found in database</div>
          <?php endif;
          } catch (Exception $e) {
            echo '<div class="error-message">Error fetching markets: ' . htmlspecialchars($e->getMessage()) . '</div>';
          }
          ?>
        </div>

      <?php endif; ?>
    </div>

    <div class="footer">
      Generated: <?= date('F j, Y \a\t g:i A') ?> | Blue Ridge Farmers Collective Database Proof
    </div>
  </div>
</body>

</html>
