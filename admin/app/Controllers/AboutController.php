<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Renderer;

/**
 * Class AboutController
 *
 * Demonstrates file-based view rendering.
 */
final class AboutController
{
  public function index(): void
  {
    Renderer::start();

    Renderer::view(
      dirname(__DIR__) . '/Views/about.php',
      [
        'title' => 'About Piedpi',
        'version' => '1.0.0',
      ]
    );

    Renderer::end();
  }
}
