<?php

declare(strict_types=1);

namespace App\Core\Middleware;

/**
 * CORS Middleware
 *
 * Handles Cross-Origin Resource Sharing for browser-based clients.
 *
 * Key design principles:
 * - Environment-driven configuration
 * - Explicit allowlist (no wildcard origins)
 * - Safe defaults for authenticated APIs
 * - Zero framework dependency
 */
final class CorsMiddleware
{
  public static function handle(): void
  {
    /*
     * Browser sends Origin header for cross-origin requests.
     * Server-to-server or CLI requests usually do not.
     */
    $origin = $_SERVER['HTTP_ORIGIN'] ?? null;

    if ($origin === null) {
      return;
    }

    /*
     * Load allowed origins from environment.
     * Expected format:
     *   CORS_ALLOWED_ORIGINS=origin1,origin2,origin3
     */
    $raw = env('CORS_ALLOWED_ORIGINS', '');

    if ($raw === '') {
      return;
    }

    $allowedOrigins = array_map(
      static fn(string $o): string => rtrim(trim($o), '/'),
      explode(',', $raw)
    );

    $normalizedOrigin = rtrim($origin, '/');

    if (!in_array($normalizedOrigin, $allowedOrigins, true)) {
      return;
    }

    /*
     * Apply CORS headers
     */
    header("Access-Control-Allow-Origin: {$origin}");
    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Allow-Credentials: true');
    header('Vary: Origin');

    /*
     * Preflight requests should not reach router or controllers.
     */
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
      http_response_code(204);
      exit;
    }
  }
}
