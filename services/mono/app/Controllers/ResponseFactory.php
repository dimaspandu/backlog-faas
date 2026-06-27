<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Http\JsonResponse;

/**
 * Response factory mirroring Go response models.
 */
final class ResponseFactory
{
  public static function success(mixed $data): JsonResponse
  {
    return new JsonResponse(['data' => $data]);
  }

  public static function successWithMeta(mixed $data, int $page, int $perPage, int $total, int $totalPages): JsonResponse
  {
    return new JsonResponse([
      'data' => $data,
      'meta' => [
        'page'       => $page,
        'perPage'    => $perPage,
        'total'      => $total,
        'totalPages' => $totalPages,
      ],
    ]);
  }

  public static function error(int $status, string $message): JsonResponse
  {
    return (new JsonResponse(['message' => $message]))->status($status);
  }

  public static function badRequest(string $message = 'Bad Request'): JsonResponse
  {
    return self::error(400, $message);
  }

  public static function forbidden(string $message = 'Forbidden'): JsonResponse
  {
    return self::error(403, $message);
  }

  public static function notFound(string $message = 'Resource Not Found'): JsonResponse
  {
    return self::error(404, $message);
  }

  public static function serverError(string $message = 'Internal Server Error'): JsonResponse
  {
    return self::error(500, $message);
  }
}
