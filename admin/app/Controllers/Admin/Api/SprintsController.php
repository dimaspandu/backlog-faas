<?php

declare(strict_types=1);

namespace App\Controllers\Admin\Api;

use App\Core\Database\DB;
use App\Core\Http\JsonResponse;

final class SprintsController
{
  /**
   * GET /api/admin/sprints
   * Return list of sprints as JSON
   */
  public function index(): JsonResponse
  {
    try {
      $search = trim($_GET['search'] ?? '');
      $page = max(1, (int)($_GET['page'] ?? 1));
      $perPage = min(100, max(5, (int)($_GET['per_page'] ?? 25)));
      $offset = ($page - 1) * $perPage;

      $where = 'WHERE status != "DELETED"';
      $params = [];

      if ($search !== '') {
        $where .= ' AND (name LIKE :search OR token LIKE :search)';
        $params['search'] = '%' . $search . '%';
      }

      // Get total count
      $total = DB::one("SELECT COUNT(*) as total FROM sprints $where", $params)['total'] ?? 0;

      // Get paginated data
      $sql = "
        SELECT id, token, name, status, is_visible, is_open, start_at, end_at, created_at 
        FROM sprints 
        $where
        ORDER BY created_at DESC 
        LIMIT $perPage OFFSET $offset
      ";

      $sprints = DB::query($sql, $params);

      return new JsonResponse([
        'success' => true,
        'data' => $sprints,
        'pagination' => [
          'current_page' => $page,
          'per_page' => $perPage,
          'total' => (int)$total,
          'total_pages' => ceil($total / $perPage)
        ]
      ]);
    } catch (\Throwable $e) {
      return $this->errorResponse($e);
    }
  }

  /**
   * POST /api/admin/sprints
   * Create a new sprint from JSON body
   */
  public function store(): JsonResponse
  {
    try {
      $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

      $name = trim($input['name'] ?? '');
      if ($name === '') {
      $response = new JsonResponse([
        'success' => false,
        'error'   => 'Name is required.'
      ]);
      return $response->status(422);
      }

      // Generate token if not provided (32 char hex)
      $token = $input['token'] ?? bin2hex(random_bytes(16));

      $description = $input['description'] ?? null;
      $start_at = !empty($input['start_at']) ? date('Y-m-d H:i:s', strtotime($input['start_at'])) : null;
      $end_at   = !empty($input['end_at']) ? date('Y-m-d H:i:s', strtotime($input['end_at'])) : null;
      $status   = $input['status'] ?? 'DRAFT';
      $is_visible = !empty($input['is_visible']) ? 1 : 0;
      $is_open    = !empty($input['is_open']) ? 1 : 0;

      DB::exec(
        'INSERT INTO sprints (token, name, description, start_at, end_at, status, is_visible, is_open) 
         VALUES (:token, :name, :description, :start_at, :end_at, :status, :is_visible, :is_open)',
        [
          'token'       => $token,
          'name'        => $name,
          'description' => $description,
          'start_at'    => $start_at,
          'end_at'      => $end_at,
          'status'      => $status,
          'is_visible'  => $is_visible,
          'is_open'     => $is_open,
        ]
      );

      $response = new JsonResponse([
        'success' => true,
        'message' => 'Sprint created successfully.',
        'token'   => $token
      ]);
      return $response->status(201);

    } catch (\Throwable $e) {
      return $this->errorResponse($e);
    }
  }

  /**
   * GET /api/admin/sprints/:id
   */
  public function show(array $params): JsonResponse
  {
    try {
      $id = (int) ($params['id'] ?? 0);

      $sprint = DB::one(
        'SELECT * FROM sprints WHERE id = :id AND status != "DELETED"',
        ['id' => $id]
      );

      if (!$sprint) {
        return (new JsonResponse(['success' => false, 'error' => 'Sprint not found']))->status(404);
      }

      return new JsonResponse([
        'success' => true,
        'data'    => $sprint
      ]);
    } catch (\Throwable $e) {
      return $this->errorResponse($e);
    }
  }

  /**
   * PUT /api/admin/sprints/:id
   */
  public function update(array $params): JsonResponse
  {
    try {
      $id = (int) ($params['id'] ?? 0);

      $input = json_decode(file_get_contents('php://input'), true) ?? [];

      // Check if exists and not deleted
      $existing = DB::one(
        'SELECT id FROM sprints WHERE id = :id AND status != "DELETED"',
        ['id' => $id]
      );

      if (!$existing) {
        return (new JsonResponse(['success' => false, 'error' => 'Sprint not found']))->status(404);
      }

      $name = trim($input['name'] ?? '');
      if ($name === '') {
        return (new JsonResponse(['success' => false, 'error' => 'Name is required']))->status(422);
      }

      $description = $input['description'] ?? null;
      $start_at = !empty($input['start_at']) ? date('Y-m-d H:i:s', strtotime($input['start_at'])) : null;
      $end_at   = !empty($input['end_at']) ? date('Y-m-d H:i:s', strtotime($input['end_at'])) : null;
      $status   = $input['status'] ?? 'DRAFT';
      $is_visible = !empty($input['is_visible']) ? 1 : 0;
      $is_open    = !empty($input['is_open']) ? 1 : 0;

      DB::exec(
        'UPDATE sprints SET 
            name = :name,
            description = :description,
            start_at = :start_at,
            end_at = :end_at,
            status = :status,
            is_visible = :is_visible,
            is_open = :is_open,
            updated_at = NOW()
         WHERE id = :id',
        [
          'name'        => $name,
          'description' => $description,
          'start_at'    => $start_at,
          'end_at'      => $end_at,
          'status'      => $status,
          'is_visible'  => $is_visible,
          'is_open'     => $is_open,
          'id'          => $id
        ]
      );

      return new JsonResponse([
        'success' => true,
        'message' => 'Sprint updated successfully'
      ]);
    } catch (\Throwable $e) {
      return $this->errorResponse($e);
    }
  }

  /**
   * DELETE /api/admin/sprints/:id  (Soft Delete)
   */
  public function destroy(array $params): JsonResponse
  {
    try {
      $id = (int) ($params['id'] ?? 0);

      $affected = DB::exec(
        'UPDATE sprints SET status = "DELETED", updated_at = NOW() WHERE id = :id AND status != "DELETED"',
        ['id' => $id]
      );

      if ($affected === 0) {
        return (new JsonResponse(['success' => false, 'error' => 'Sprint not found or already deleted']))->status(404);
      }

      return new JsonResponse([
        'success' => true,
        'message' => 'Sprint deleted successfully (soft delete via status)'
      ]);
    } catch (\Throwable $e) {
      return $this->errorResponse($e);
    }
  }

  private function errorResponse(\Throwable $e): JsonResponse
  {
    if (env('APP_DEBUG', false)) {
      $response = new JsonResponse([
        'success' => false,
        'error'   => $e->getMessage(),
        'trace'   => $e->getTraceAsString()
      ]);
      return $response->status(500);
    }

    $response = new JsonResponse([
      'success' => false,
      'error'   => 'Internal server error.'
    ]);
    return $response->status(500);
  }
}
