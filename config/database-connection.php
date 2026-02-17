<?php

declare(strict_types=1);

return (function (): \PDO {
  $basePath = dirname(__DIR__);
  $config = require $basePath . '/config/database.php';

  $driver = $config['driver'] ?? 'mysql';
  $options = $config['options'] ?? [];

  if ($driver === 'sqlite') {
    $database = $config['database'] ?? ($basePath . '/storage/database.sqlite');
    return new \PDO('sqlite:' . $database, null, null, $options);
  }

  $host = $config['host'] ?? '127.0.0.1';
  $port = $config['port'] ?? ($driver === 'sqlsrv' ? '1433' : '3306');
  $database = $config['database'] ?? '';
  $username = $config['username'] ?? '';
  $password = $config['password'] ?? '';

  if ($driver === 'sqlsrv') {
    $dsn = 'sqlsrv:Server=' . $host . ',' . $port . ';Database=' . $database;
    return new \PDO($dsn, $username, $password, $options);
  }

  $charset = $config['charset'] ?? 'utf8mb4';
  $dsn = 'mysql:host=' . $host . ';port=' . $port . ';dbname=' . $database . ';charset=' . $charset;
  return new \PDO($dsn, $username, $password, $options);
})();
