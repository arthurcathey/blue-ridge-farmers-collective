<?php
echo "<h1>Standalone Database Test</h1>";

// Test database connection directly
try {
  $host = 'localhost';
  $database = 'hqkmwgmy_blueridge_farmers_db';
  $username = 'hqkmwgmy_blueridge_user';
  $password = '$Chopper1984';

  $dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4";
  $pdo = new PDO($dsn, $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  ]);

  echo "<p style='color: green;'><strong>✓ Database connected successfully!</strong></p>";

  // Test query
  $result = $pdo->query("SELECT COUNT(*) as table_count FROM information_schema.tables WHERE table_schema = '$database'");
  $row = $result->fetch(PDO::FETCH_ASSOC);
  echo "<p>Tables in database: " . $row['table_count'] . "</p>";
} catch (Exception $e) {
  echo "<p style='color: red;'><strong>✗ Error:</strong></p>";
  echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}
