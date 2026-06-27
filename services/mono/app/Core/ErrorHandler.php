<?php

declare(strict_types=1);

namespace App\Core;

use Throwable;

/**
 * Class ErrorHandler
 *
 * Centralized error and exception handler for piedpi.
 * Responsible for safe error rendering and internal logging.
 */
final class ErrorHandler
{
  /**
   * Handle uncaught exceptions (500 Internal Server Error).
   */
  public static function handle(Throwable $exception): void
  {
    self::logException($exception);

    http_response_code(500);
    echo self::render(
      '500 - Server Error',
      'Something went wrong. Please try again later.'
    );

    exit;
  }

  /**
   * Render a 404 Not Found error page.
   */
  public static function renderNotFound(): void
  {
    http_response_code(404);

    echo self::render(
      '404 - Not Found',
      'The requested page could not be found.'
    );

    exit;
  }

  /**
   * Render a generic error page.
   */
  private static function render(string $title, string $message): string
  {
    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>{$title}</title>
</head>
<body>
  <h1>{$title}</h1>
  <p>{$message}</p>
</body>
</html>
HTML;
  }

  /**
   * Log exception details internally.
   */
  private static function logException(Throwable $exception): void
  {
    error_log(
      '[' . date('Y-m-d H:i:s') . '] ' .
      $exception->getMessage() . PHP_EOL .
      $exception->getTraceAsString()
    );
  }
}
