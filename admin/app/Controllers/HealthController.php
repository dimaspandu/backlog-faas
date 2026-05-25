<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Http\JsonResponse;

final class HealthController
{
  public function check(): JsonResponse
  {
    return new JsonResponse([
      'status'    => 'ok',
      'service'   => 'piedpi',
      'timestamp' => time(),
    ]);
  }
}
