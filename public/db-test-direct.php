<?php
// Direct database test - no APP code
$host = 'localhost';
$db = 'hqkmwgmy_blueridge_farmers_db';
$user = 'hqkmwgmy_blueridge_user';
$pass = '$Chopper1984';

try {
  $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
  echo "✓ Database connection SUCCESS!";
} catch (PDOException $e) {
  echo "✗ Database connection FAILED: " . $e->getMessage();
}
