<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Http\JsonResponse;
use App\Core\Renderer;
use App\Core\View\TemplateCache;

/**
 * Class HomeController
 *
 * Handles the home page.
 */
class HomeController
{
  public function index(): void
  {
    Renderer::start();

    Renderer::chunk('<!DOCTYPE html>');
    Renderer::chunk('<html lang="en">');

    Renderer::chunk(
      widget('head', null, [
        widget('title', null, 'Home')
      ])
    );

    Renderer::chunk('<body>');

    Renderer::chunk(
      widget('h1', ['arrival-time' => date('d-m-y H:i:s')], 'Streaming Content (arrive at '.date('H:i:s').')')
    );

    // Simulate network / rendering delay
    usleep(500_000);

    Renderer::chunk('<p arrival-time='.date('d-m-y H:i:s').'>Step 2 (arrive at '.date('H:i:s').')</p>');
    
    // Simulate network / rendering delay
    usleep(550_000);

    Renderer::chunk('<p arrival-time='.date('d-m-y H:i:s').'>Step 3 (arrive at '.date('H:i:s').')</p>');
    
    // Simulate network / rendering delay
    usleep(600_000);

    Renderer::view(
      dirname(__DIR__) . '/Views/partial.php'
    );

    Renderer::chunk('</body>');
    Renderer::chunk('</html>');

    Renderer::end();
  }

  public function hello(): void
  {
    $key = 'home_page';

    if ($cached = TemplateCache::get($key)) {
      echo $cached;
      return;
    }

    ob_start();

    Renderer::start();
    Renderer::chunk('<h1>Hello Cached</h1>');
    Renderer::end();

    $html = ob_get_clean();

    TemplateCache::put($key, $html);
    echo $html;
  }

  public function api(): JsonResponse
  {
    return new JsonResponse([
      'status' => 'ok',
      'time' => time(),
    ]);
  }
}
