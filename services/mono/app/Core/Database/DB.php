<?php

declare(strict_types=1);

namespace App\Core\Database;

use PDOException;
use Throwable;

/**
 * Class DB
 *
 * Safe PDO wrapper.
 */
final class DB
{
  public static function query(string $sql, array $params = []): array
  {
    try {
      $stmt = Connection::get()->prepare($sql);
      $stmt->execute($params);
      return $stmt->fetchAll();
    } catch (PDOException $e) {
      throw new DatabaseException(previous: $e);
    }
  }

  public static function one(string $sql, array $params = []): ?array
  {
    $rows = self::query($sql, $params);
    return $rows[0] ?? null;
  }

  public static function exec(string $sql, array $params = []): int
  {
    try {
      $stmt = Connection::get()->prepare($sql);
      $stmt->execute($params);
      return $stmt->rowCount();
    } catch (PDOException $e) {
      throw new DatabaseException(previous: $e);
    }
  }

  public static function transaction(callable $callback): mixed
  {
    $pdo = Connection::get();

    try {
      $pdo->beginTransaction();
      $result = $callback();
      $pdo->commit();
      return $result;
    } catch (Throwable $e) {
      if ($pdo->inTransaction()) {
        $pdo->rollBack();
      }
      throw new DatabaseException('Transaction failed', $e);
    }
  }
}
