<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Http\JsonResponse;

/**
 * HealthController
 *
 * Provides a lightweight health check endpoint
 * to verify service availability.
 */
final class HealthController
{
  public function check(): JsonResponse
  {
    return new JsonResponse([
      'status'    => 'ok',
      'service'   => 'bcsaas',
      'timestamp' => time(),
    ]);
  }
}
