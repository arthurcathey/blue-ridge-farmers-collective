<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class BaseModel
{
  protected PDO $db;

  public function __construct(?PDO $db = null)
  {
    $this->db = $db ?? self::defaultConnection();
  }

  public static function connection(): PDO
  {
    return self::defaultConnection();
  }

  protected static function defaultConnection(): PDO
  {
    $basePath = dirname(__DIR__, 2);
    return require $basePath . '/config/database-connection.php';
  }
}
