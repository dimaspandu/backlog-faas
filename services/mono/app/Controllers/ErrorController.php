<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Http\JsonResponse;

/**
 * ErrorController
 *
 * Centralized JSON error responses for the API layer.
 */
class ErrorController
{
  /**
   * Handle 404 - Not Found errors.
   */
  public function notFound(): JsonResponse
  {
    return (new JsonResponse([
      'status'    => 404,
      'message'   => 'The requested resource was not found.',
      'timestamp' => time(),
    ]))->status(404);
  }

  /**
   * Handle 500 - Internal Server Error.
   *
   * @param array $params Optional context or exception data
   */
  public function serverError(array $params = []): JsonResponse
  {
    return (new JsonResponse([
      'status'    => 500,
      'message'   => 'An unexpected server error occurred.',
      'timestamp' => time(),
    ]))->status(500);
  }
}
