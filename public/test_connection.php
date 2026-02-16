<?php
// Simple database connection test for Bluehost

echo "<h2>Diagnostic Test</h2>";

// Test 1: Check PHP version
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";

// Test 2: Check file paths
$basePath = dirname(__DIR__);
echo "<p><strong>Base Path:</strong> $basePath</p>";
echo "<p><strong>Exists:</strong> " . (is_dir($basePath) ? "Yes" : "No") . "</p>";

// Test 3: Try to load config
$configPath = $basePath . '/config/database.php';
echo "<p><strong>Config Path:</strong> $configPath</p>";
echo "<p><strong>Config Exists:</strong> " . (is_file($configPath) ? "Yes" : "No") . "</p>";

if (is_file($configPath)) {
  $dbConfig = require $configPath;
  echo "<p><strong>Database Config Loaded:</strong> Yes</p>";
  echo "<p><strong>Database:</strong> " . htmlspecialchars($dbConfig['database']) . "</p>";
  echo "<p><strong>Host:</strong> " . htmlspecialchars($dbConfig['host']) . "</p>";
  echo "<p><strong>Username:</strong> " . htmlspecialchars($dbConfig['username']) . "</p>";
}

// Test 4: Try database connection
echo "<h3>Database Connection Test:</h3>";
try {
  $dbConfig = require $configPath;
  $dsn = "mysql:host=" . $dbConfig['host'] . ";port=" . $dbConfig['port'] . ";dbname=" . $dbConfig['database'] . ";charset=" . $dbConfig['charset'];

  $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);
  echo "<p style='color: green;'><strong>✓ Connected to database successfully!</strong></p>";

  // Check if tables exist
  $tablesQuery = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '" . $dbConfig['database'] . "'");
  $tableCount = $tablesQuery->fetchColumn();
  echo "<p><strong>Tables in database:</strong> $tableCount</p>";
} catch (Exception $e) {
  echo "<p style='color: red;'><strong>✗ Database Connection Error:</strong></p>";
  echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<p><small>Created: " . date('Y-m-d H:i:s') . "</small></p>";
