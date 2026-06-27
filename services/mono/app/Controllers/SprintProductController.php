<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database\DB;
use App\Core\Http\JsonResponse;

/**
 * Handles sprint-product-related endpoints.
 */
final class SprintProductController
{
  public function activeSprintList(array $params): JsonResponse
  {
    $productID = (int)$params['id'];

    $rows = DB::query('
      SELECT
        spo.sprint_token AS token,
        s.name,
        s.description,
        COUNT(spo_all.product_id) AS totalProducts,
        s.is_open as isOpen,
        s.status
      FROM sprint_product_offerings AS spo
      JOIN sprints AS s
        ON s.token = spo.sprint_token
      JOIN sprint_product_offerings AS spo_all
        ON spo_all.sprint_token = s.token
        AND spo_all.is_available = 1
      WHERE
        spo.product_id = ?
        AND spo.is_available = 1
        AND s.is_visible = 1
        AND s.is_open = 1
        AND s.status = \'ACTIVE\'
        AND s.start_at <= NOW()
        AND (s.end_at IS NULL OR s.end_at >= NOW())
      GROUP BY
        s.id,
        spo.sprint_token,
        s.name,
        s.description,
        s.is_open,
        s.status
      ORDER BY
        s.id DESC
      LIMIT 3
    ', [$productID]);

    return ResponseFactory::success($rows);
  }
}