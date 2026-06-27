<?php

declare(strict_types=1);

namespace App\Core\Database;

use PDO;
use PDOException;

/**
 * PDO Connection Factory.
 *
 * Provides a shared PDO instance configured
 * from config/database.php.
 */
final class Connection
{
  private static ?PDO $instance = null;

  public static function get(): PDO
  {
    if (self::$instance !== null) {
      return self::$instance;
    }

    $config = require dirname(__DIR__, 3) . '/config/database.php';

    $dsn = sprintf(
      '%s:host=%s;port=%s;dbname=%s;charset=%s',
      $config['driver'],
      $config['host'],
      $config['port'],
      $config['database'],
      $config['charset']
    );

    try {
      self::$instance = new PDO(
        $dsn,
        $config['username'],
        $config['password'],
        [
          PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
          PDO::ATTR_EMULATE_PREPARES   => false,
        ]
      );

      return self::$instance;
    } catch (PDOException $e) {
      throw new PDOException(
        'Database connection failed: ' . $e->getMessage(),
        (int) $e->getCode()
      );
    }
  }
}
