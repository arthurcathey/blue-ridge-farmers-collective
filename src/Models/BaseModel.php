<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class BaseModel
{
  protected PDO $db;

  /**
   * Initialize base model with database connection
   *
   * @param PDO|null $db Database connection instance, uses default if null
   */
  public function __construct(?PDO $db = null)
  {
    $this->db = $db ?? self::defaultConnection();
  }

  /**
   * Get database connection instance
   *
   * @return PDO Database connection
   */
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
