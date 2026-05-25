<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Renderer;

/**
 * Class DistController
 *
 * Serves bundled frontend files (HTML / JS inline)
 * with optional gzip compression.
 */
class DistController
{
  /**
   * Serve a bundled HTML file from dist/.
   *
   * Example:
   *   GET /dist/app  → dist/app.html
   *
   * @param array<string, string> $params
   */
  public function serve(array $params): void
  {
    $name = $params['name'] ?? 'app';

    $path = dirname(__DIR__, 2) . '/dist/' . $name . '.html';

    Renderer::serve($path, [
      'gzip'       => true,
      'contentType'=> 'text/html; charset=UTF-8',
    ]);
  }
}
