<?php

declare(strict_types=1);

namespace App\Core\View;

/**
 * Simple file-based template cache.
 *
 * Designed for:
 * - Small HTML fragments
 * - Full-page static responses
 * - MVP-level performance improvements
 *
 * This cache is intentionally naive:
 * - No TTL
 * - No invalidation strategy
 * - No dependency on external services
 */
final class TemplateCache
{
  /**
   * Directory where cached templates are stored.
   */
  private static string $dir = __DIR__ . '/../../../storage/cache';

  /**
   * Retrieve cached content by key.
   *
   * @return string|null Cached content or null if not found
   */
  public static function get(string $key): ?string
  {
    $file = self::$dir . '/' . md5($key);
    return is_file($file) ? file_get_contents($file) : null;
  }

  /**
   * Store rendered content in the cache.
   *
   * Cache keys are hashed to avoid filesystem issues.
   */
  public static function put(string $key, string $content): void
  {
    if (!is_dir(self::$dir)) {
      mkdir(self::$dir, 0777, true);
    }

    file_put_contents(self::$dir . '/' . md5($key), $content);
  }
}
