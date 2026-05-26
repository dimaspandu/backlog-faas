<?php

declare(strict_types=1);

namespace App\Core\Middleware;

/**
 * CsrfMiddleware
 *
 * Example middleware for protecting state-changing requests (POST, PUT, DELETE).
 * This is a demonstration only — in production use proper token generation & storage.
 */
final class CsrfMiddleware
{
  /**
   * Validates CSRF token for mutating HTTP methods.
   */
  public static function handle(): void
  {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if (!in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
      return;
    }

    $token = $_POST['csrf_token'] ?? '';

    // Demo token only — replace with real CSRF implementation
    if ($token !== 'demo-csrf-token-123') {
      http_response_code(419);
      header('Content-Type: application/json');
      echo json_encode(['error' => 'CSRF token mismatch']);
      exit;
    }
  }
}
