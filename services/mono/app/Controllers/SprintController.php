<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database\DB;
use App\Core\Http\JsonResponse;

/**
 * Handles sprint-related endpoints.
 */
final class SprintController
{
  public function list(): JsonResponse
  {
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $perPage = isset($_GET['perPage']) ? min(100, max(1, (int)$_GET['perPage'])) : 10;

    $countRow = DB::one('
      SELECT COUNT(*) AS cnt
      FROM sprints
      WHERE
        is_visible = 1
          AND
        (status = \'ACTIVE\' OR status = \'CLOSED\')
    ');
    $total = (int)($countRow['cnt'] ?? 0);

    if ($total === 0) {
      return ResponseFactory::notFound('No sprints found');
    }

    $offset = ($page - 1) * $perPage;

    $rows = DB::query('
      SELECT
        token,
        name,
        description,
        (
          SELECT COUNT(*)
          FROM sprint_product_offerings spo
          WHERE spo.sprint_token = token
        ) AS totalProducts,
        is_open as isOpen,
        status
      FROM sprints
      WHERE
        is_visible = 1
          AND
        (status = \'ACTIVE\' OR status = \'CLOSED\')
      ORDER BY
        id DESC
      LIMIT ?
      OFFSET ?
    ', [$perPage, $offset]);

    $totalPages = (int)ceil($total / $perPage);

    return ResponseFactory::successWithMeta($rows, $page, $perPage, $total, $totalPages);
  }

  public function show(array $params): JsonResponse
  {
    $token = $params['token'];

    $rows = DB::query('
      SELECT
        p.id,
        p.sku,
        p.product_slug,
        spo.is_available,
        p.name,
        p.description,
        p.image_urls,
        p.selling_price_cents,
        spo.offer_price_cents
      FROM sprint_product_offerings spo
      JOIN products p ON spo.product_id = p.id
      WHERE spo.sprint_token = ?
      ORDER BY spo.offer_price_cents ASC
      LIMIT 100
    ', [$token]);

    $grouped = [];
    foreach (array_reverse($rows) as $row) {
      $slug = $row['product_slug'];
      $grouped[$slug][] = [
        'id'                => (int)$row['id'],
        'sku'               => $row['sku'],
        'isAvailable'       => (int)$row['is_available'],
        'name'              => $row['name'],
        'description'       => $row['description'],
        'images'            => $row['image_urls'],
        'sellingPriceCents' => (int)$row['selling_price_cents'],
        'offerPriceCents'   => (int)$row['offer_price_cents'],
      ];
    }

    if (count($grouped) > 0) {
      return ResponseFactory::success(['sprint' => $params['sprint'], 'products' => $grouped]);
    }
    return ResponseFactory::success(['sprint' => $params['sprint'], 'products' => (object)[]]);
  }
}
