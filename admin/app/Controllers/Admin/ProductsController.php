<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth\AdminAuth;
use App\Core\Database\DB;
use App\Core\Renderer;

final class ProductsController
{
  public function index(): void
  {
    AdminAuth::startSession();

    Renderer::start();
    Renderer::chunk('<!DOCTYPE html><html lang="en">');
    Renderer::chunk('<head>');
    Renderer::view(dirname(__DIR__, 2) . '/Views/admin/partials/head.php', ['title' => 'Products']);
    Renderer::chunk('</head>');
    Renderer::chunk('<body class="admin-page"><div class="admin-container">');

    Renderer::view(__DIR__ . '/../../Views/admin/partials/nav.php');

    Renderer::view(dirname(__DIR__, 2) . '/Views/admin/products/index.php');

    Renderer::view(dirname(__DIR__, 2) . '/Views/admin/partials/footer.php');
    Renderer::chunk('</div></body></html>');
    Renderer::end();
  }

  public function createForm(): void
  {
    AdminAuth::startSession();

    Renderer::start();
    Renderer::chunk('<!DOCTYPE html><html lang="en"><head>');
    Renderer::view(dirname(__DIR__, 2) . '/Views/admin/partials/head.php', ['title' => 'New Product']);
    Renderer::chunk('</head><body class="admin-page"><div class="admin-container">');

    Renderer::view(__DIR__ . '/../../Views/admin/partials/nav.php');
    Renderer::view(dirname(__DIR__, 2) . '/Views/admin/products/create.php');
    Renderer::view(dirname(__DIR__, 2) . '/Views/admin/partials/footer.php');

    Renderer::chunk('</div></body></html>');
    Renderer::end();
  }

  public function show(array $params): void
  {
    AdminAuth::startSession();

    $product = DB::one(
      'SELECT id, sku, name, description, images, status, created_at, updated_at
       FROM products
       WHERE id = :id AND status != "DELETED"',
      ['id' => (int) ($params['id'] ?? 0)]
    );

    if (!$product) {
      http_response_code(404);
      echo 'Product not found';
      return;
    }

    Renderer::start();
    Renderer::chunk('<!DOCTYPE html><html lang="en"><head>');
    Renderer::view(dirname(__DIR__, 2) . '/Views/admin/partials/head.php', ['title' => 'Product Detail']);
    Renderer::chunk('</head><body class="admin-page"><div class="admin-container">');

    Renderer::view(__DIR__ . '/../../Views/admin/partials/nav.php');
    Renderer::view(dirname(__DIR__, 2) . '/Views/admin/products/show.php', ['product' => $product]);
    Renderer::view(dirname(__DIR__, 2) . '/Views/admin/partials/footer.php');

    Renderer::chunk('</div></body></html>');
    Renderer::end();
  }
}
