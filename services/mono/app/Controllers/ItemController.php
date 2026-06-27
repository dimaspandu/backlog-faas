<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Http\JsonResponse;

/**
 * ItemController
 *
 * Example REST controller demonstrating
 * CRUD-style JSON endpoints.
 */
final class ItemController
{
  /**
   * GET /api/items?limit=10
   *
   * Returns a list of items with optional limit parameter.
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
   *
   * Creates a new item from JSON request body.
   */
  public function store(): JsonResponse
  {
    $payload = json_decode(
      file_get_contents('php://input'),
      true
    );

    return (new JsonResponse([
      'action'  => 'create',
      'payload' => $payload,
    ]))->status(201);
  }

  /**
   * PUT /api/items/:id
   *
   * Updates an existing item.
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
   *
   * Deletes an item by ID.
   */
  public function destroy(array $params): JsonResponse
  {
    return (new JsonResponse([
      'action' => 'delete',
      'id'     => $params['id'],
    ]))->status(204);
  }
}
