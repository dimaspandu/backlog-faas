<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Controllers\ResponseFactory;
use App\Core\Database\DB;
use App\Core\Http\JsonResponse;
use Closure;

final class SprintTokenMiddleware
{
  public static function with(callable|array $handler): Closure
  {
    return static function (array $params) use ($handler): mixed {
      $meta = self::validate($params['token'] ?? '');
      if ($meta instanceof JsonResponse) {
        return $meta;
      }

      $params['sprint'] = $meta;

      return is_array($handler)
        ? (new $handler[0])->{$handler[1]}($params)
        : call_user_func($handler, $params);
    };
  }

  public static function handle(array $params): JsonResponse
  {
    $meta = self::validate($params['token'] ?? '');

    return $meta instanceof JsonResponse ? $meta : new JsonResponse(['sprint' => $meta]);
  }

  private static function validate(string $token): array|JsonResponse
  {
    if ($token === '') {
      return ResponseFactory::notFound('Sprint token disappeared');
    }

    $meta = DB::one('
      SELECT
        token,
        name,
        description,
        end_at as endAt,
        is_open as isOpen,
        status
      FROM sprints
      WHERE
        token = ?
          AND is_visible = 1
          AND status IN (\'ACTIVE\', \'CLOSED\')
      LIMIT 1
    ', [$token]);

    if ($meta === null) {
      return ResponseFactory::notFound('Sprint not found or not open');
    }

    return $meta;
  }
}