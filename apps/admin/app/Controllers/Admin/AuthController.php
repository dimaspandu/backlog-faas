<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth\AdminAuth;
use App\Core\Renderer;

final class AuthController
{
  /**
   * GET /admin/login
   * Show login form
   */
  public function showLogin(): void
  {
    if (AdminAuth::check()) {
      header('Location: /admin');
      exit;
    }

    $this->renderLoginPage(error: null);
  }

  /**
   * POST /admin/login
   */
  public function login(): void
  {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
      $this->renderLoginPage(error: 'Username and password are required.');
      return;
    }

    if (AdminAuth::attempt($username, $password)) {
      header('Location: /admin');
      exit;
    }

    $this->renderLoginPage(error: 'Invalid username or password.');
  }

  /**
   * POST /admin/logout
   */
  public function logout(): void
  {
    AdminAuth::logout();
    header('Location: /admin/login');
    exit;
  }

  private function renderLoginPage(?string $error): void
  {
    Renderer::start();

    Renderer::chunk('<!DOCTYPE html><html lang="en">');
    Renderer::chunk('<head>');
    Renderer::view(dirname(__DIR__, 2) . '/Views/admin/partials/head.php', ['title' => 'Login']);
    Renderer::chunk('</head>');
    Renderer::chunk('<body class="admin-page"><div class="admin-container">');

    // Pure view file (presentation layer only)
    Renderer::view(dirname(__DIR__, 2) . '/Views/admin/login.php', [
      'error' => $error
    ]);

    Renderer::view(dirname(__DIR__, 2) . '/Views/admin/partials/footer.php');
    Renderer::chunk('</div></body></html>');

    Renderer::end();
  }
}
