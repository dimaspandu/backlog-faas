<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth\AdminAuth;
use App\Core\Database\DB;
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

  public function show(array $params): void
  {
    AdminAuth::startSession();

    $sprintProduct = DB::one(
      'SELECT sp.*, s.name AS sprint_name, p.name AS product_name, pv.name AS variant_name
       FROM sprint_products sp
       JOIN sprints s ON s.id = sp.sprint_id
       JOIN products p ON p.id = sp.product_id
       LEFT JOIN product_variants pv ON pv.id = sp.product_variant_id
       WHERE sp.id = :id AND sp.status != "DELETED"',
      ['id' => (int) ($params['id'] ?? 0)]
    );

    if (!$sprintProduct) {
      http_response_code(404);
      echo 'Sprint product not found';
      return;
    }

    Renderer::start();
    Renderer::chunk('<!DOCTYPE html><html lang="en"><head>');
    Renderer::view(dirname(__DIR__, 2) . '/Views/admin/partials/head.php', ['title' => 'Sprint Product Detail']);
    Renderer::chunk('</head><body class="admin-page"><div class="admin-container">');
    Renderer::view(__DIR__ . '/../../Views/admin/partials/nav.php');
    Renderer::view(dirname(__DIR__, 2) . '/Views/admin/sprint-products/show.php', ['sprintProduct' => $sprintProduct]);
    Renderer::view(dirname(__DIR__, 2) . '/Views/admin/partials/footer.php');
    Renderer::chunk('</div></body></html>');
    Renderer::end();
  }
}
