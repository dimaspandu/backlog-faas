<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Renderer;

class ErrorController
{
  public function notFound(): void
  {
    Renderer::start();
    Renderer::chunk('<h1>404</h1>');
    Renderer::chunk('<p>The page you requested was not found.</p>');
    Renderer::end();
  }

  public function serverError(array $params): void
  {
    Renderer::start();
    Renderer::chunk('<h1>500</h1>');
    Renderer::chunk('<p>An unexpected error occurred.</p>');
    Renderer::end();
  }
}
