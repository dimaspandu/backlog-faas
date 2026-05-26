<?php

declare(strict_types=1);

namespace App\Core\Database;

use RuntimeException;
use Throwable;

/**
 * Class DatabaseException
 *
 * Boundary exception for all database-related failures.
 * Prevents SQL details from leaking to upper layers.
 */
final class DatabaseException extends RuntimeException
{
  public function __construct(
    string $message = 'Database operation failed',
    ?Throwable $previous = null
  ) {
    parent::__construct($message, 0, $previous);
  }
}
