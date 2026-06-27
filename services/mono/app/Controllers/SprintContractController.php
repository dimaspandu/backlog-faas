<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database\DB;
use App\Core\Database\Connection;
use App\Core\Http\JsonResponse;

/**
 * Handles sprint contract endpoints.
 */
final class SprintContractController
{
  public function create(array $params): JsonResponse
  {
    $sprintMeta = $params['sprint'];
    $token = $sprintMeta['token'];

    if ((int)$sprintMeta['isOpen'] !== 1 || $sprintMeta['status'] !== 'ACTIVE') {
      return ResponseFactory::forbidden('Sprint is closed or inactive');
    }

    if ($sprintMeta['endAt'] !== null) {
      $endAt = strtotime($sprintMeta['endAt']);
      if ($endAt === false || time() >= $endAt) {
        return ResponseFactory::forbidden('Sprint has ended');
      }
    }

    $payload = json_decode(file_get_contents('php://input'), true);

    if (!is_array($payload)) {
      return ResponseFactory::badRequest('Invalid request body');
    }

    if (!isset($payload['products']) || !is_array($payload['products']) || count($payload['products']) === 0) {
      return ResponseFactory::badRequest('Products cannot be empty');
    }

    $productIds = [];
    $uniqueProductIds = [];
    foreach ($payload['products'] as $product) {
      if (!is_array($product)) {
        return ResponseFactory::badRequest('Product id must be valid');
      }

      $productId = (int)($product['id'] ?? 0);
      if ($productId <= 0) {
        return ResponseFactory::badRequest('Product id must be valid');
      }

      $productIds[] = $productId;
      $uniqueProductIds[$productId] = true;
    }

    $customerName = self::sanitizeString((string)($payload['customerName'] ?? ''), 255);
    $customerContact = self::sanitizeContact((string)($payload['customerContact'] ?? ''));
    $customerAuthProvider = isset($payload['customerAuthProvider']) ? (string)$payload['customerAuthProvider'] : 'GUEST';
    $customerExternalId = isset($payload['customerExternalId']) ? trim((string)$payload['customerExternalId']) : '';
    $notes = isset($payload['notes']) ? self::sanitizeString((string)$payload['notes'], 1000) : '';

    if ($customerName === '') {
      return ResponseFactory::badRequest('Customer name cannot be empty');
    }

    if (strlen($customerName) < 2) {
      return ResponseFactory::badRequest('Customer name must be at least 2 characters');
    }

    if (strlen($customerName) > 255) {
      return ResponseFactory::badRequest('Customer name must not exceed 255 characters');
    }

    if ($customerContact === '') {
      return ResponseFactory::badRequest('Customer contact cannot be empty');
    }

    if (strlen($notes) > 1000) {
      return ResponseFactory::badRequest('Notes must not exceed 1000 characters');
    }

    $isEmail = self::detectEmail($customerContact);
    $isPhone = self::detectPhone($customerContact);
    if (!$isEmail && !$isPhone) {
      return ResponseFactory::badRequest('Customer contact must be a valid email or phone number');
    }

    $email = $isEmail ? $customerContact : null;
    $phone = $isPhone ? $customerContact : null;

    $args = [$token, ...array_keys($uniqueProductIds)];
    $placeholders = implode(',', array_fill(0, count($uniqueProductIds), '?'));

    $priceRow = DB::one("
      SELECT
        COUNT(*) AS cnt,
        COALESCE(SUM(offer_price_cents), 0) AS total
      FROM sprint_product_offerings
      WHERE sprint_token = ? AND product_id IN ($placeholders)
    ", $args);

    $totalProducts = (int)($priceRow['cnt'] ?? 0);
    if ($totalProducts !== count($uniqueProductIds)) {
      return ResponseFactory::badRequest('One or more products are not available in this sprint');
    }

    $totalPriceCents = (int)($priceRow['total'] ?? 0);
    $contractNumber = sprintf('ORD-%d-%03d', (int)(microtime(true) * 1000), random_int(100, 999));

    $errorMessage = 'Internal Server Error';
    try {
      DB::transaction(function () use (
        $token,
        $contractNumber,
        $customerName,
        $customerContact,
        $notes,
        $totalPriceCents,
        $productIds,
        $payload,
        $email,
        $phone,
        $customerAuthProvider,
        $customerExternalId,
        &$errorMessage
      ): void {
        $errorMessage = 'Failed to create customer';
        DB::exec('
          INSERT INTO customers
            (name, email, phone, auth_provider, external_id)
          SELECT ?, ?, ?, ?, ?
            WHERE NOT EXISTS (
              SELECT 1 FROM customers WHERE email = ? AND phone = ?
            )
        ',
        [
          $customerName,
          $email,
          $phone,
          $customerAuthProvider,
          $customerExternalId,
          $email,
          $phone,
        ]);

        $errorMessage = 'Failed to create contract';
        DB::exec('
          INSERT INTO sprint_contracts (
            contract_number,
            sprint_token,
            customer_name,
            customer_contact,
            notes,
            total_price_cents,
            request_status,
            payment_status
          ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ', [
          $contractNumber,
          $token,
          $customerName,
          $customerContact,
          $notes,
          $totalPriceCents,
          'PENDING',
          'UNPAID'
        ]);

        $contractId = (int)Connection::get()->lastInsertId();
        if ($contractId <= 0) {
          throw new \RuntimeException('Failed to create contract');
        }

        $orderValues = [];
        $orderArgs = [];
        foreach ($productIds as $productId) {
          $sugarLevel = strtoupper((string)($payload['products'][array_search($productId, $productIds, true)]['sugarLevel'] ?? ''));
          if ($sugarLevel === '') {
            $sugarLevel = 'NONE';
          }

          $orderValues[] = '(?, ?, ?)';
          $orderArgs[] = $contractId;
          $orderArgs[] = $productId;
          $orderArgs[] = $sugarLevel;
        }

        $errorMessage = 'Failed to create contract orders';
        DB::exec('
          INSERT INTO sprint_contract_orders (
            sprint_contract_id,
            product_id,
            sugar_level
          ) VALUES ' . implode(',', $orderValues)
        , $orderArgs);
      });
    } catch (\Throwable) {
      return ResponseFactory::serverError($errorMessage);
    }

    return ResponseFactory::success(null);
  }

  public function list(array $params): JsonResponse
  {
    $token = $params['token'];

    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $perPage = isset($_GET['perPage']) ? min(100, max(1, (int)$_GET['perPage'])) : 10;

    $countRow = DB::one('
      SELECT COUNT(*) AS cnt
      FROM sprint_contracts
      WHERE sprint_token = ?
    ', [$token]);
    $total = (int)($countRow['cnt'] ?? 0);

    if ($total === 0) {
      return ResponseFactory::notFound('No sprint contracts found');
    }

    $offset = ($page - 1) * $perPage;

    $rows = DB::query('
      SELECT
        sc.contract_number as contractNumber,
        sc.customer_name as customerName,
        sc.customer_contact as customerContact,
        sc.notes,
          (
            SELECT COUNT(*)
            FROM sprint_contract_orders sco
            WHERE sco.sprint_contract_id = sc.id
          ) AS totalItems,
        sc.total_price_cents as totalPriceCents,
        sc.request_status as requestStatus,
        sc.payment_status as paymentStatus,
        sc.created_at as createdAt
      FROM sprint_contracts sc
      WHERE
        sc.sprint_token = ?
      ORDER BY
        sc.created_at DESC
      LIMIT ?
      OFFSET ?
    ', [$token, (int)$perPage, (int)$offset]);

    $masked = [];
    foreach ($rows as $row) {
      $row['customerContact'] = self::maskContact($row['customerContact']);
      $masked[] = $row;
    }

    $totalPages = (int)ceil($total / $perPage);

    return ResponseFactory::successWithMeta($masked, $page, $perPage, $total, $totalPages);
  }

  public function show(array $params): JsonResponse
  {
    $token = $params['token'];

    $contractNumber = $params['contractNumber'];

    $row = DB::one('
      SELECT
        sc.contract_number as contractNumber,
        sc.customer_name as customerName,
        sc.customer_contact as customerContact,
        sc.notes,
        (
          SELECT COUNT(*)
          FROM sprint_contract_orders sco
          WHERE sco.sprint_contract_id = sc.id
        ) AS totalItems,
        sc.total_price_cents as totalPriceCents,
        sc.request_status as requestStatus,
        sc.payment_status as paymentStatus,
        sc.created_at as createdAt
      FROM sprint_contracts sc
      WHERE
        sc.contract_number = ?
        AND sc.sprint_token = ?
      LIMIT 1
    ', [$contractNumber, $token]);

    if ($row === null) {
      return ResponseFactory::notFound('No sprint contract found');
    }

    $row['customerContact'] = self::maskContact($row['customerContact']);

    $items = DB::query('
      SELECT
        p.sku as productSku,
        p.name as productName,
        p.selling_price_cents as sellingPriceCents,
        spo.offer_price_cents as offerPriceCents,
        sco.sugar_level as sugarLevel
      FROM sprint_contract_orders sco
      JOIN sprint_contracts sc
        ON sco.sprint_contract_id = sc.id
      JOIN sprint_product_offerings spo
          ON spo.product_id = sco.product_id
        AND spo.sprint_token = sc.sprint_token
      JOIN products p
        ON p.id = sco.product_id
      WHERE
        sc.contract_number = ?
        AND sc.sprint_token = ?
      ORDER BY sco.id ASC
    ', [$contractNumber, $token]);

    return ResponseFactory::success(['contract' => $row, 'items' => $items]);
  }

  private static function sanitizeString(string $value, int $maxLength = 0): string
  {
    $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $value = preg_replace('/<[^>]*>/', '', $value) ?? $value;
    $value = preg_replace('/(?i)(on\w+\s*=|javascript\s*:|data\s*:|vbscript\s*:)/', '', $value) ?? $value;
    $value = trim($value);

    if ($maxLength > 0 && strlen($value) > $maxLength) {
      return substr($value, 0, $maxLength);
    }

    return $value;
  }

  private static function sanitizeContact(string $value): string
  {
    $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $value = preg_replace('/<[^>]*>/', '', $value) ?? $value;
    $value = preg_replace('/(?i)(on\w+\s*=|javascript\s*:|data\s*:|vbscript\s*:)/', '', $value) ?? $value;
    return trim($value);
  }

  private static function detectEmail(string $s): bool
  {
    return preg_match('/^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/', $s) === 1;
  }

  private static function detectPhone(string $s): bool
  {
    return preg_match('/^\+?[0-9]{8,15}$/', $s) === 1;
  }

  private static function maskContact(string $s): string
  {
    if (self::detectEmail($s)) {
      return self::maskEmail($s);
    }
    if (self::detectPhone($s)) {
      return self::maskPhone($s);
    }
    return $s;
  }

  private static function maskEmail(string $email): string
  {
    $parts = explode('@', $email, 2);
    if (count($parts) !== 2) {
      return $email;
    }
    $local = $parts[0];
    $domain = $parts[1];
    if (mb_strlen($local) <= 2) {
      return '**@' . $domain;
    }
    return mb_substr($local, 0, 2) . '****@' . $domain;
  }

  private static function maskPhone(string $phone): string
  {
    if (mb_strlen($phone) <= 4) {
      return '****';
    }
    return mb_substr($phone, 0, 2) . '****' . mb_substr($phone, -2);
  }
}
