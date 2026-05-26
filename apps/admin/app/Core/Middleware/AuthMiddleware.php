<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Auth\AdminAuth;

/**
 * AuthMiddleware (Admin)
 *
 * Session-based guard for the internal admin area.
 * Redirects unauthenticated users to /admin/login.
 */
final class AuthMiddleware
{
  public static function handle(): void
  {
    AdminAuth::requireLogin();
  }
}
