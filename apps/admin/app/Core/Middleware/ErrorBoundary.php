<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use Throwable;

/**
 * Class ErrorBoundary
 *
 * Isolates execution errors without deciding
 * how errors are rendered.
 */
final class ErrorBoundary
{
  public function handle(callable $callback): void
  {
    try {
      $callback();
    } catch (Throwable $e) {
      // Let Router decide how to handle errors
      throw $e;
    }
  }
}
