<?php

declare(strict_types=1);

namespace App\Controllers\Admin\Api;

use App\Core\Database\DB;
use App\Core\Http\JsonResponse;
use InvalidArgumentException;
use JsonException;

final class ProductsController
{
  public function index(): JsonResponse
  {
    try {
      $search = trim($_GET['search'] ?? '');
      $page = max(1, (int) ($_GET['page'] ?? 1));
      $perPage = min(100, max(5, (int) ($_GET['per_page'] ?? 25)));
      $offset = ($page - 1) * $perPage;
      $where = 'WHERE status != "DELETED"';
      $params = [];

      if ($search !== '') {
        $where .= ' AND (sku LIKE :search_sku OR name LIKE :search_name)';
        $params['search_sku'] = '%' . $search . '%';
        $params['search_name'] = '%' . $search . '%';
      }

      $total = DB::one("SELECT COUNT(*) AS total FROM products $where", $params)['total'] ?? 0;
      $products = DB::query(
        "SELECT id, sku, name, description, images, status, created_at, updated_at
         FROM products
         $where
         ORDER BY created_at DESC, id DESC
         LIMIT $perPage OFFSET $offset",
        $params
      );

      return new JsonResponse([
        'success' => true,
        'data' => $products,
        'pagination' => [
          'current_page' => $page,
          'per_page' => $perPage,
          'total' => (int) $total,
          'total_pages' => (int) ceil((int) $total / $perPage),
        ],
      ]);
    } catch (\Throwable $e) {
      return $this->errorResponse($e);
    }
  }

  public function store(): JsonResponse
  {
    try {
      $input = $this->input();
      $validation = $this->validate($input);

      if ($validation instanceof JsonResponse) {
        return $validation;
      }

      if ($this->skuExists($validation['sku'])) {
        return $this->validationError('SKU is already in use.');
      }

      DB::exec(
        'INSERT INTO products (sku, name, description, images)
         VALUES (:sku, :name, :description, :images)',
        $validation
      );

      return (new JsonResponse([
        'success' => true,
        'message' => 'Product created successfully.',
      ]))->status(201);
    } catch (\Throwable $e) {
      return $this->errorResponse($e);
    }
  }

  public function show(array $params): JsonResponse
  {
    try {
      $product = $this->find((int) ($params['id'] ?? 0));

      if (!$product) {
        return (new JsonResponse(['success' => false, 'error' => 'Product not found.']))->status(404);
      }

      return new JsonResponse(['success' => true, 'data' => $product]);
    } catch (\Throwable $e) {
      return $this->errorResponse($e);
    }
  }

  public function update(array $params): JsonResponse
  {
    try {
      $id = (int) ($params['id'] ?? 0);

      if (!$this->find($id)) {
        return (new JsonResponse(['success' => false, 'error' => 'Product not found.']))->status(404);
      }

      $validation = $this->validate($this->input());

      if ($validation instanceof JsonResponse) {
        return $validation;
      }

      if ($this->skuExists($validation['sku'], $id)) {
        return $this->validationError('SKU is already in use.');
      }

      $validation['id'] = $id;
      DB::exec(
        'UPDATE products
         SET sku = :sku, name = :name, description = :description, images = :images
         WHERE id = :id',
        $validation
      );

      return new JsonResponse([
        'success' => true,
        'message' => 'Product updated successfully.',
      ]);
    } catch (\Throwable $e) {
      return $this->errorResponse($e);
    }
  }

  public function destroy(array $params): JsonResponse
  {
    try {
      $id = (int) ($params['id'] ?? 0);

      if (!$this->find($id)) {
        return (new JsonResponse(['success' => false, 'error' => 'Product not found.']))->status(404);
      }

      DB::exec(
        'UPDATE products SET status = "DELETED" WHERE id = :id AND status != "DELETED"',
        ['id' => $id]
      );

      return new JsonResponse([
        'success' => true,
        'message' => 'Product deleted successfully (soft delete via status).',
      ]);
    } catch (\Throwable $e) {
      return $this->errorResponse($e);
    }
  }

  private function input(): array
  {
    return json_decode(file_get_contents('php://input'), true) ?? $_POST;
  }

  private function validate(array $input): array|JsonResponse
  {
    $sku = trim($input['sku'] ?? '');
    $name = trim($input['name'] ?? '');

    if ($sku === '') {
      return $this->validationError('SKU is required.');
    }

    if ($name === '') {
      return $this->validationError('Name is required.');
    }

    try {
      $images = $this->normalizeImages($input['images'] ?? null);
    } catch (InvalidArgumentException $e) {
      return $this->validationError($e->getMessage());
    }

    return [
      'sku' => $sku,
      'name' => $name,
      'description' => trim($input['description'] ?? '') ?: null,
      'images' => $images,
    ];
  }

  private function normalizeImages(mixed $images): ?string
  {
    if ($images === null || (is_string($images) && trim($images) === '')) {
      return null;
    }

    if (is_array($images)) {
      return json_encode($images, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
    }

    try {
      $decoded = json_decode((string) $images, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
      throw new InvalidArgumentException('Images must contain valid JSON.');
    }

    return json_encode($decoded, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
  }

  private function skuExists(string $sku, ?int $excludeId = null): bool
  {
    $sql = 'SELECT id FROM products WHERE sku = :sku';
    $params = ['sku' => $sku];

    if ($excludeId !== null) {
      $sql .= ' AND id != :id';
      $params['id'] = $excludeId;
    }

    return DB::one($sql . ' LIMIT 1', $params) !== null;
  }

  private function find(int $id): ?array
  {
    return DB::one(
      'SELECT id, sku, name, description, images, status, created_at, updated_at
       FROM products
       WHERE id = :id AND status != "DELETED"',
      ['id' => $id]
    );
  }

  private function validationError(string $message): JsonResponse
  {
    return (new JsonResponse(['success' => false, 'error' => $message]))->status(422);
  }

  private function errorResponse(\Throwable $e): JsonResponse
  {
    error_log((string) $e);

    return (new JsonResponse([
      'success' => false,
      'error' => 'Internal server error.',
    ]))->status(500);
  }
}
