<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth\AdminAuth;
use App\Core\Renderer;

final class DashboardController
{
  public function index(): void
  {
    AdminAuth::startSession();

    Renderer::start();

    Renderer::chunk('<!DOCTYPE html>');
    Renderer::chunk('<html lang="en">');
    Renderer::chunk('<head>');
    Renderer::view(dirname(__DIR__, 2) . '/Views/admin/partials/head.php', ['title' => 'Dashboard']);
    Renderer::chunk('</head>');
    Renderer::chunk('<body class="admin-page">');
    Renderer::chunk('<div class="admin-container">');

    Renderer::view(__DIR__ . '/../../Views/admin/partials/nav.php');

    // Pure presentation view
    Renderer::view(dirname(__DIR__, 2) . '/Views/admin/dashboard.php', [
      'username' => AdminAuth::username()
    ]);

    Renderer::view(dirname(__DIR__, 2) . '/Views/admin/partials/footer.php');
    Renderer::chunk('</div></body></html>');
    Renderer::end();
  }
}
