<?php

// Database connection
$host = 'localhost';
$db = 'blue_ridge_farmers_collective_db';
$user = 'root';
$pass = 'ampps';

try {
  $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  ]);

  // Get all markets
  $stmt = $pdo->query('SELECT id_mkt, name_mkt, latitude_mkt, longitude_mkt, city_mkt FROM market_mkt ORDER BY id_mkt');
  $markets = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo "<h1>Markets and their coordinates</h1>";
  echo "<table border='1' cellpadding='10'>";
  echo "<tr><th>ID</th><th>Name</th><th>City</th><th>Latitude</th><th>Longitude</th></tr>";

  foreach ($markets as $market) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($market['id_mkt']) . "</td>";
    echo "<td>" . htmlspecialchars($market['name_mkt']) . "</td>";
    echo "<td>" . htmlspecialchars($market['city_mkt']) . "</td>";
    echo "<td>" . htmlspecialchars($market['latitude_mkt'] ?? 'NULL') . "</td>";
    echo "<td>" . htmlspecialchars($market['longitude_mkt'] ?? 'NULL') . "</td>";
    echo "</tr>";
  }
  echo "</table>";

  // Define coordinates for known markets (you can add more)
  $coordinates = [
    // Format: market_id => ['latitude' => X, 'longitude' => Y]
    1 => ['name' => 'Asheville City Market', 'latitude' => 35.5951, 'longitude' => -82.5516],
    2 => ['name' => 'Riverside Farmers Market', 'latitude' => 35.6066, 'longitude' => -82.5522],
    3 => ['name' => 'Mountain View Market', 'latitude' => 35.6200, 'longitude' => -82.5400],
    4 => ['name' => 'Downtown Farmers Market', 'latitude' => 35.5951, 'longitude' => -82.5516],
  ];

  echo "<h2>Updating coordinates...</h2>";

  foreach ($coordinates as $id => $data) {
    $updateStmt = $pdo->prepare('UPDATE market_mkt SET latitude_mkt = :lat, longitude_mkt = :lon WHERE id_mkt = :id');
    $updateStmt->execute([
      ':lat' => $data['latitude'],
      ':lon' => $data['longitude'],
      ':id' => $id,
    ]);

    if ($updateStmt->rowCount() > 0) {
      echo "✓ Updated market ID $id ({$data['name']}) with coordinates: {$data['latitude']}, {$data['longitude']}<br>";
    }
  }

  echo "<h2>Updated markets:</h2>";
  $stmt = $pdo->query('SELECT id_mkt, name_mkt, latitude_mkt, longitude_mkt FROM market_mkt ORDER BY id_mkt');
  $markets = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo "<table border='1' cellpadding='10'>";
  echo "<tr><th>ID</th><th>Name</th><th>Latitude</th><th>Longitude</th></tr>";

  foreach ($markets as $market) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($market['id_mkt']) . "</td>";
    echo "<td>" . htmlspecialchars($market['name_mkt']) . "</td>";
    echo "<td>" . htmlspecialchars($market['latitude_mkt'] ?? 'NULL') . "</td>";
    echo "<td>" . htmlspecialchars($market['longitude_mkt'] ?? 'NULL') . "</td>";
    echo "</tr>";
  }
  echo "</table>";
} catch (PDOException $e) {
  echo "Error: " . $e->getMessage();
}
