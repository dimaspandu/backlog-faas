<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database\DB;
use App\Core\Http\JsonResponse;

/**
 * Handles product-related endpoints.
 */
final class ProductController
{
  public function list(): JsonResponse
  {
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $perPage = isset($_GET['perPage']) ? min(100, max(1, (int)$_GET['perPage'])) : 10;

    $countRow = DB::one('
      SELECT COUNT(*) AS cnt
      FROM products
      WHERE status = \'ACTIVE\'
    ');
    $total = (int)($countRow['cnt'] ?? 0);

    if ($total === 0) {
      return ResponseFactory::notFound('No products found');
    }

    $offset = ($page - 1) * $perPage;

    $rows = DB::query('
      SELECT
        id,
        sku,
        product_slug as slug,
        name,
        description,
        category,
        image_urls as images,
        selling_price_cents as sellingPriceCents
      FROM products
      WHERE
        is_available = 1
        AND
        status = \'ACTIVE\'
      ORDER BY
        id DESC
      LIMIT ?
      OFFSET ?
    ', [$perPage, $offset]);

    $totalPages = (int)ceil($total / $perPage);

    return ResponseFactory::successWithMeta($rows, $page, $perPage, $total, $totalPages);
  }
}