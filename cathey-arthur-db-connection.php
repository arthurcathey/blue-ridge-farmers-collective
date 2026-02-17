<?php

/**
 * Database Connection File Phase 3 Submission
 * Arthur Cathey
 * 
 * This file provides a standalone database connection for the 
 * Blue Ridge Farmers Collective application.
 * 
 * Returns a PDO database connection object.
 */

declare(strict_types=1);

// Local Development Credentials 
// Uncomment these for local AMPPS/XAMPP development
/*
$config = [
  'driver' => 'mysql',
  'host' => 'localhost',
  'port' => '3306',
  'database' => 'blueridge_farmers_db',
  'username' => 'root',
  'password' => 'mysql',
  'charset' => 'utf8mb4',
  'options' => [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
  ],
];
*/

// Production Credentials (Bluehost)
$config = [
  'driver' => 'mysql',
  'host' => getenv('DB_HOST') ?: 'localhost',
  'port' => getenv('DB_PORT') ?: '3306',
  'database' => getenv('DB_NAME') ?: 'hqkmwgmy_blueridge_farmers_db',
  'username' => getenv('DB_USER') ?: 'hqkmwgmy_blueridge_user',
  'password' => getenv('DB_PASS') ?: '',
  'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
  'options' => [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
  ],
];

try {
  $dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
    $config['host'],
    $config['port'],
    $config['database'],
    $config['charset']
  );

  $pdo = new PDO(
    $dsn,
    $config['username'],
    $config['password'],
    $config['options']
  );

  return $pdo;
} catch (PDOException $e) {
  die('Database connection failed: ' . $e->getMessage());
}
