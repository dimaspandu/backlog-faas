<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth\AdminAuth;
use App\Core\Renderer;

final class SprintProductsController
{
  public function index(): void
  {
    AdminAuth::startSession();

    Renderer::start();
    Renderer::chunk('<!DOCTYPE html><html lang="en">');
    Renderer::chunk('<head>');
    Renderer::view(dirname(__DIR__, 2) . '/Views/admin/partials/head.php', ['title' => 'Sprint Products']);
    Renderer::chunk('</head>');
    Renderer::chunk('<body class="admin-page"><div class="admin-container">');

    Renderer::view(__DIR__ . '/../../Views/admin/partials/nav.php');

    Renderer::view(dirname(__DIR__, 2) . '/Views/admin/sprint-products/index.php');

    Renderer::view(dirname(__DIR__, 2) . '/Views/admin/partials/footer.php');
    Renderer::chunk('</div></body></html>');
    Renderer::end();
  }
}
