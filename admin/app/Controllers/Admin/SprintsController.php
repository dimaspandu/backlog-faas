<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth\AdminAuth;
use App\Core\Database\DB;
use App\Core\Renderer;

final class SprintsController
{
  /**
   * GET /admin/sprints
   * List all sprints
   */
  public function index(): void
  {
    AdminAuth::startSession();

    // Data is now loaded via JavaScript from /api/admin/sprints
    Renderer::start();
    Renderer::chunk('<!DOCTYPE html><html lang="en">');
    Renderer::chunk('<head>');
    Renderer::view(dirname(__DIR__, 2) . '/Views/admin/partials/head.php', ['title' => 'Sprints']);
    Renderer::chunk('</head>');
    Renderer::chunk('<body class="admin-page">');
    Renderer::chunk('<div class="admin-container">');

    Renderer::view(__DIR__ . '/../../Views/admin/partials/nav.php');

    // Pure view (API driven)
    Renderer::view(dirname(__DIR__, 2) . '/Views/admin/sprints/index.php');

    Renderer::view(dirname(__DIR__, 2) . '/Views/admin/partials/footer.php');
    Renderer::chunk('</div></body></html>');
    Renderer::end();
  }

  /**
   * GET /admin/sprints/new
   */
  public function createForm(): void
  {
    AdminAuth::startSession();

    Renderer::start();
    Renderer::chunk('<!DOCTYPE html><html><head>');
    Renderer::view(dirname(__DIR__, 2) . '/Views/admin/partials/head.php', ['title' => 'New Sprint']);
    Renderer::chunk('</head>');
    Renderer::chunk('<body class="admin-page"><div class="admin-container">');

    Renderer::view(__DIR__ . '/../../Views/admin/partials/nav.php');

    // Pure view
    Renderer::view(dirname(__DIR__, 2) . '/Views/admin/sprints/create.php');

    Renderer::view(dirname(__DIR__, 2) . '/Views/admin/partials/footer.php');
    Renderer::chunk('</div></body></html>');
    Renderer::end();
  }

  /**
   * GET /admin/sprints/:id
   * Detail page
   */
  public function show(array $params): void
  {
    AdminAuth::startSession();

    $id = (int) ($params['id'] ?? 0);

    // Direct DB query for detail page
    $sprint = \App\Core\Database\DB::one(
      'SELECT * FROM sprints WHERE id = :id AND status != "DELETED"',
      ['id' => $id]
    );

    if (!$sprint) {
      http_response_code(404);
      echo "Sprint not found";
      return;
    }

    Renderer::start();
    Renderer::chunk('<!DOCTYPE html><html lang="en">');
    Renderer::chunk('<head>');
    Renderer::view(dirname(__DIR__, 2) . '/Views/admin/partials/head.php', ['title' => 'Sprint Detail']);
    Renderer::chunk('</head>');
    Renderer::chunk('<body class="admin-page"><div class="admin-container">');

    Renderer::view(__DIR__ . '/../../Views/admin/partials/nav.php');

    Renderer::view(dirname(__DIR__, 2) . '/Views/admin/sprints/show.php', ['sprint' => $sprint]);

    Renderer::view(dirname(__DIR__, 2) . '/Views/admin/partials/footer.php');
    Renderer::chunk('</div></body></html>');
    Renderer::end();
  }
}
