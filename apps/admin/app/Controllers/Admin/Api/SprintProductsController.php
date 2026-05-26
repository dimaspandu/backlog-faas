<?php

declare(strict_types=1);

namespace App\Controllers\Admin\Api;

use App\Core\Database\DB;
use App\Core\Http\JsonResponse;

final class SprintProductsController
{
  public function index(): JsonResponse
  {
    try {
      $search = trim($_GET['search'] ?? '');
      $sprintId = (int) ($_GET['sprint_id'] ?? 0);
      $page = max(1, (int) ($_GET['page'] ?? 1));
      $perPage = min(100, max(5, (int) ($_GET['per_page'] ?? 25)));
      $offset = ($page - 1) * $perPage;
      $where = 'WHERE sp.status != "DELETED"';
      $params = [];

      if ($sprintId > 0) {
        $where .= ' AND sp.sprint_id = :sprint_id';
        $params['sprint_id'] = $sprintId;
      }

      if ($search !== '') {
        $where .= ' AND (sp.sku LIKE :search_sku OR p.name LIKE :search_product OR s.name LIKE :search_sprint OR pv.name LIKE :search_variant)';
        $params['search_sku'] = '%' . $search . '%';
        $params['search_product'] = '%' . $search . '%';
        $params['search_sprint'] = '%' . $search . '%';
        $params['search_variant'] = '%' . $search . '%';
      }

      $from = 'FROM sprint_products sp
        JOIN sprints s ON s.id = sp.sprint_id
        JOIN products p ON p.id = sp.product_id
        LEFT JOIN product_variants pv ON pv.id = sp.product_variant_id';

      $total = DB::one("SELECT COUNT(*) AS total $from $where", $params)['total'] ?? 0;
      $data = DB::query(
        "SELECT sp.id, sp.sprint_id, s.name AS sprint_name, sp.product_id, p.name AS product_name,
          sp.product_variant_id, pv.name AS variant_name, sp.sku, sp.price_cents, sp.list_price_cents,
          sp.discount_cents, sp.stock, sp.reserved_quantity, sp.stock_sold, sp.variant, sp.status,
          sp.created_at, sp.updated_at
         $from
         $where
         ORDER BY sp.created_at DESC, sp.id DESC
         LIMIT $perPage OFFSET $offset",
        $params
      );

      return new JsonResponse([
        'success' => true,
        'data' => $data,
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
      $values = $this->values($this->input());
      if ($values instanceof JsonResponse) {
        return $values;
      }

      $existing = $this->offering($values['sprint_id'], $values['product_id'], $values['sku']);
      if ($existing && $existing['status'] !== 'DELETED') {
        return $this->validationError('This product variant is already configured for the selected sprint.');
      }

      if ($existing) {
        $values['id'] = (int) $existing['id'];
        DB::exec(
          'UPDATE sprint_products SET
            product_variant_id = :product_variant_id, price_cents = :price_cents,
            list_price_cents = :list_price_cents, discount_cents = :discount_cents,
            stock = :stock, variant = :variant, status = :status
           WHERE id = :id AND sprint_id = :sprint_id AND product_id = :product_id AND sku = :sku',
          $values
        );
      } else {
        DB::exec(
          'INSERT INTO sprint_products
            (sprint_id, product_id, product_variant_id, sku, price_cents, list_price_cents, discount_cents, stock, variant, status)
           VALUES
            (:sprint_id, :product_id, :product_variant_id, :sku, :price_cents, :list_price_cents, :discount_cents, :stock, :variant, :status)',
          $values
        );
      }

      return (new JsonResponse([
        'success' => true,
        'message' => $existing
          ? 'Sprint product restored successfully.'
          : 'Sprint product created successfully.',
      ]))->status(201);
    } catch (\Throwable $e) {
      return $this->errorResponse($e);
    }
  }

  public function show(array $params): JsonResponse
  {
    try {
      $row = $this->find((int) ($params['id'] ?? 0));
      if (!$row) {
        return $this->notFound();
      }

      return new JsonResponse(['success' => true, 'data' => $row]);
    } catch (\Throwable $e) {
      return $this->errorResponse($e);
    }
  }

  public function update(array $params): JsonResponse
  {
    try {
      $id = (int) ($params['id'] ?? 0);
      if (!$this->find($id)) {
        return $this->notFound();
      }

      $values = $this->values($this->input());
      if ($values instanceof JsonResponse) {
        return $values;
      }

      if ($this->offeringExists($values['sprint_id'], $values['product_id'], $values['sku'], $id)) {
        return $this->validationError('This product variant is already configured for the selected sprint.');
      }

      $values['id'] = $id;
      DB::exec(
        'UPDATE sprint_products SET
          sprint_id = :sprint_id, product_id = :product_id, product_variant_id = :product_variant_id,
          sku = :sku, price_cents = :price_cents, list_price_cents = :list_price_cents,
          discount_cents = :discount_cents, stock = :stock, variant = :variant, status = :status
         WHERE id = :id AND status != "DELETED"',
        $values
      );

      return new JsonResponse([
        'success' => true,
        'message' => 'Sprint product updated successfully.',
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
        return $this->notFound();
      }

      DB::exec(
        'UPDATE sprint_products SET status = "DELETED" WHERE id = :id AND status != "DELETED"',
        ['id' => $id]
      );

      return new JsonResponse([
        'success' => true,
        'message' => 'Sprint product deleted successfully (soft delete via status).',
      ]);
    } catch (\Throwable $e) {
      return $this->errorResponse($e);
    }
  }

  private function values(array $input): array|JsonResponse
  {
    $sprintId = (int) ($input['sprint_id'] ?? 0);
    $productId = (int) ($input['product_id'] ?? 0);
    $variantId = !empty($input['product_variant_id']) ? (int) $input['product_variant_id'] : null;
    $sprint = DB::one(
      'SELECT id FROM sprints WHERE id = :id AND status != "DELETED"',
      ['id' => $sprintId]
    );
    $product = DB::one(
      'SELECT id, sku FROM products WHERE id = :id AND status != "DELETED"',
      ['id' => $productId]
    );

    if (!$sprint) {
      return $this->validationError('A valid sprint is required.');
    }

    if (!$product) {
      return $this->validationError('A valid product is required.');
    }

    $variant = null;
    if ($variantId !== null) {
      $variant = DB::one(
        'SELECT id, sku, name, attributes
         FROM product_variants
         WHERE id = :id AND product_id = :product_id AND status != "DELETED"',
        ['id' => $variantId, 'product_id' => $productId]
      );

      if (!$variant) {
        return $this->validationError('The selected variant is not available for this product.');
      }
    }

    $status = strtoupper(trim($input['status'] ?? 'ACTIVE'));
    if (!in_array($status, ['ACTIVE', 'INACTIVE'], true)) {
      return $this->validationError('Status must be ACTIVE or INACTIVE.');
    }

    foreach (['price_cents', 'list_price_cents', 'discount_cents', 'stock'] as $field) {
      if ((int) ($input[$field] ?? 0) < 0) {
        return $this->validationError('Price, discount and stock values cannot be negative.');
      }
    }

    $sku = trim($input['sku'] ?? '');
    if ($sku === '') {
      $sku = trim($variant['sku'] ?? '') ?: $product['sku'];
    }

    return [
      'sprint_id' => $sprintId,
      'product_id' => $productId,
      'product_variant_id' => $variantId,
      'sku' => $sku,
      'price_cents' => (int) ($input['price_cents'] ?? 0),
      'list_price_cents' => (int) ($input['list_price_cents'] ?? 0),
      'discount_cents' => (int) ($input['discount_cents'] ?? 0),
      'stock' => (int) ($input['stock'] ?? 0),
      'variant' => $variant ? $this->snapshot($variant) : null,
      'status' => $status,
    ];
  }

  private function snapshot(array $variant): string
  {
    return json_encode([
      'id' => (int) $variant['id'],
      'sku' => $variant['sku'],
      'name' => $variant['name'],
      'attributes' => json_decode($variant['attributes'] ?? '[]', true) ?? [],
    ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
  }

  private function offeringExists(int $sprintId, int $productId, string $sku, ?int $excludeId = null): bool
  {
    $sql = 'SELECT id FROM sprint_products WHERE sprint_id = :sprint_id AND product_id = :product_id AND sku = :sku';
    $params = ['sprint_id' => $sprintId, 'product_id' => $productId, 'sku' => $sku];

    if ($excludeId !== null) {
      $sql .= ' AND id != :id';
      $params['id'] = $excludeId;
    }

    return DB::one($sql . ' LIMIT 1', $params) !== null;
  }

  private function offering(int $sprintId, int $productId, string $sku): ?array
  {
    return DB::one(
      'SELECT id, status FROM sprint_products
       WHERE sprint_id = :sprint_id AND product_id = :product_id AND sku = :sku
       LIMIT 1',
      ['sprint_id' => $sprintId, 'product_id' => $productId, 'sku' => $sku]
    );
  }

  private function find(int $id): ?array
  {
    return DB::one(
      'SELECT sp.id, sp.sprint_id, s.name AS sprint_name, sp.product_id, p.name AS product_name,
        sp.product_variant_id, pv.name AS variant_name, sp.sku, sp.price_cents, sp.list_price_cents,
        sp.discount_cents, sp.stock, sp.reserved_quantity, sp.stock_sold, sp.variant, sp.status,
        sp.created_at, sp.updated_at
       FROM sprint_products sp
       JOIN sprints s ON s.id = sp.sprint_id
       JOIN products p ON p.id = sp.product_id
       LEFT JOIN product_variants pv ON pv.id = sp.product_variant_id
       WHERE sp.id = :id AND sp.status != "DELETED"',
      ['id' => $id]
    );
  }

  private function input(): array
  {
    return json_decode(file_get_contents('php://input'), true) ?? $_POST;
  }

  private function notFound(): JsonResponse
  {
    return (new JsonResponse(['success' => false, 'error' => 'Sprint product not found.']))->status(404);
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
