<?php

declare(strict_types=1);

return [
  'driver' => getenv('DB_DRIVER') ?: 'mysql',
  'host' => getenv('DB_HOST') ?: 'localhost',
  'port' => getenv('DB_PORT') ?: '3306',
  'database' => getenv('DB_NAME') ?: 'hqkmwgmy_blueridge_farmers_db',
  'username' => getenv('DB_USER') ?: 'hqkmwgmy_blueridge_user',
  'password' => getenv('DB_PASS') ?: '',
  'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
  'options' => [
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
  ],
];
