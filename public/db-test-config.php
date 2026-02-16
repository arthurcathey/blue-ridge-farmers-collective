<?php
// Use exact app logic for database connection
$basePath = dirname(__DIR__);

// Step 1: Load env (like the app does)
require $basePath . '/config/env.php';
load_env($basePath . '/.env');

// Step 2: Load config (like the app does)  
$config = require $basePath . '/config/database.php';

echo "Config loaded:\n";
echo "Host: " . $config['host'] . "\n";
echo "Database: " . $config['database'] . "\n";
echo "Username: " . $config['username'] . "\n";
echo "Password: " . $config['password'] . "\n\n";

// Step 3: Try connection with config values
try {
  $dsn = 'mysql:host=' . $config['host'] . ';dbname=' . $config['database'] . ';charset=utf8mb4';
  $pdo = new PDO($dsn, $config['username'], $config['password']);
  echo "✓ Connection with config values SUCCESS!";
} catch (PDOException $e) {
  echo "✗ Connection with config values FAILED: " . $e->getMessage();
}
