<?php

declare(strict_types=1);

namespace App\Controllers\Admin\Api;

use App\Core\Database\DB;
use App\Core\Http\JsonResponse;
use InvalidArgumentException;
use JsonException;

final class ProductVariantsController
{
  public function index(array $params): JsonResponse
  {
    try {
      $productId = (int) ($params['id'] ?? 0);
      if (!$this->productExists($productId)) {
        return $this->notFound('Product not found.');
      }

      $variants = DB::query(
        'SELECT id, product_id, sku, name, attributes, status, created_at, updated_at
         FROM product_variants
         WHERE product_id = :product_id AND status != "DELETED"
         ORDER BY created_at DESC, id DESC',
        ['product_id' => $productId]
      );

      return new JsonResponse(['success' => true, 'data' => $variants]);
    } catch (\Throwable $e) {
      return $this->errorResponse($e);
    }
  }

  public function store(array $params): JsonResponse
  {
    try {
      $productId = (int) ($params['id'] ?? 0);
      if (!$this->productExists($productId)) {
        return $this->notFound('Product not found.');
      }

      $validated = $this->validate($this->input());
      if ($validated instanceof JsonResponse) {
        return $validated;
      }

      $validated['product_id'] = $productId;
      DB::exec(
        'INSERT INTO product_variants (product_id, sku, name, attributes)
         VALUES (:product_id, :sku, :name, :attributes)',
        $validated
      );

      return (new JsonResponse([
        'success' => true,
        'message' => 'Product variant created successfully.',
      ]))->status(201);
    } catch (\Throwable $e) {
      return $this->errorResponse($e);
    }
  }

  public function show(array $params): JsonResponse
  {
    try {
      $variant = $this->find(
        (int) ($params['id'] ?? 0),
        (int) ($params['variantId'] ?? 0)
      );

      if (!$variant) {
        return $this->notFound('Product variant not found.');
      }

      return new JsonResponse(['success' => true, 'data' => $variant]);
    } catch (\Throwable $e) {
      return $this->errorResponse($e);
    }
  }

  public function update(array $params): JsonResponse
  {
    try {
      $productId = (int) ($params['id'] ?? 0);
      $id = (int) ($params['variantId'] ?? 0);
      if (!$this->find($productId, $id)) {
        return $this->notFound('Product variant not found.');
      }

      $validated = $this->validate($this->input());
      if ($validated instanceof JsonResponse) {
        return $validated;
      }

      $validated['id'] = $id;
      $validated['product_id'] = $productId;
      DB::exec(
        'UPDATE product_variants
         SET sku = :sku, name = :name, attributes = :attributes
         WHERE id = :id AND product_id = :product_id AND status != "DELETED"',
        $validated
      );

      return new JsonResponse([
        'success' => true,
        'message' => 'Product variant updated successfully.',
      ]);
    } catch (\Throwable $e) {
      return $this->errorResponse($e);
    }
  }

  public function destroy(array $params): JsonResponse
  {
    try {
      $productId = (int) ($params['id'] ?? 0);
      $id = (int) ($params['variantId'] ?? 0);
      if (!$this->find($productId, $id)) {
        return $this->notFound('Product variant not found.');
      }

      DB::exec(
        'UPDATE product_variants
         SET status = "DELETED"
         WHERE id = :id AND product_id = :product_id AND status != "DELETED"',
        ['id' => $id, 'product_id' => $productId]
      );

      return new JsonResponse([
        'success' => true,
        'message' => 'Product variant deleted successfully (soft delete via status).',
      ]);
    } catch (\Throwable $e) {
      return $this->errorResponse($e);
    }
  }

  private function validate(array $input): array|JsonResponse
  {
    $sku = trim($input['sku'] ?? '');
    if ($sku === '') {
      return $this->validationError('Variant SKU is required.');
    }

    $name = trim($input['name'] ?? '');
    if ($name === '') {
      return $this->validationError('Variant name is required.');
    }

    try {
      $attributes = $this->normalizeAttributes($input['attributes'] ?? null);
    } catch (InvalidArgumentException $e) {
      return $this->validationError($e->getMessage());
    }

    return [
      'sku' => $sku,
      'name' => $name,
      'attributes' => $attributes,
    ];
  }

  private function normalizeAttributes(mixed $attributes): ?string
  {
    if ($attributes === null || (is_string($attributes) && trim($attributes) === '')) {
      return null;
    }

    if (is_array($attributes)) {
      return json_encode($attributes, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
    }

    try {
      $decoded = json_decode((string) $attributes, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
      throw new InvalidArgumentException('Attributes must contain valid JSON.');
    }

    return json_encode($decoded, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
  }

  private function input(): array
  {
    return json_decode(file_get_contents('php://input'), true) ?? $_POST;
  }

  private function productExists(int $id): bool
  {
    return DB::one(
      'SELECT id FROM products WHERE id = :id AND status != "DELETED"',
      ['id' => $id]
    ) !== null;
  }

  private function find(int $productId, int $id): ?array
  {
    if (!$this->productExists($productId)) {
      return null;
    }

    return DB::one(
      'SELECT id, product_id, sku, name, attributes, status, created_at, updated_at
       FROM product_variants
       WHERE id = :id AND product_id = :product_id AND status != "DELETED"',
      ['id' => $id, 'product_id' => $productId]
    );
  }

  private function notFound(string $message): JsonResponse
  {
    return (new JsonResponse(['success' => false, 'error' => $message]))->status(404);
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
