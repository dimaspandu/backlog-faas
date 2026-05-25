<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database\DB;
use App\Core\Http\JsonResponse;

/**
 * DbTestController
 *
 * Used to verify database connectivity and queries.
 * Uses the static DB facade instead of raw PDO.
 */
final class DbTestController
{
  /**
   * GET /_debug/db/items
   *
   * Fetch items from database using the safe DB wrapper.
   */
  public function items(): JsonResponse
  {
    $items = DB::query(
      'SELECT id, name, created_at FROM items'
    );

    return new JsonResponse([
      'status' => 'ok',
      'items'  => $items,
    ]);
  }
}
