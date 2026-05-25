<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Http\JsonResponse;

final class ItemController
{
  /*
   |-------------------------------------------------
   | Alternative middleware pattern (from controller)
   |-------------------------------------------------
   | You can also call middleware at the start of the method
   | instead of wrapping in the route file.
   |
   | Example:
   |   public function store(): JsonResponse
   |   {
   |       \App\Core\Middleware\CsrfMiddleware::handle();
   |       ...
   |   }
   */

  /**
   * GET /api/items?limit=10
   */
  public function index(): JsonResponse
  {
    $limit = isset($_GET['limit'])
      ? (int) $_GET['limit']
      : 10;

    return new JsonResponse([
      'action' => 'list',
      'limit'  => $limit,
      'items'  => [],
    ]);
  }

  /**
   * POST /api/items
   */
  public function store(): JsonResponse
  {
    $payload = json_decode(
      file_get_contents('php://input'),
      true
    );

    $response = new JsonResponse([
      'action'  => 'create',
      'payload' => $payload,
    ]);

    return $response->status(201);
  }

  /**
   * PUT /api/items/:id
   */
  public function update(array $params): JsonResponse
  {
    $payload = json_decode(
      file_get_contents('php://input'),
      true
    );

    return new JsonResponse([
      'action'  => 'update',
      'id'      => $params['id'],
      'payload' => $payload,
    ]);
  }

  /**
   * DELETE /api/items/:id
   */
  public function destroy(array $params): JsonResponse
  {
    $response = new JsonResponse([
      'action' => 'delete',
      'id'     => $params['id'],
    ]);

    return $response->status(204);
  }
}
