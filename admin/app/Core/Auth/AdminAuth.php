<?php

declare(strict_types=1);

namespace App\Core\Auth;

/**
 * AdminAuth
 *
 * Simple session-based authentication for the internal admin area.
 * Explicit, no magic, follows Piedpi style.
 */
final class AdminAuth
{
  private const SESSION_KEY = 'admin_user_id';
  private const SESSION_USERNAME = 'admin_username';

  /**
   * Start the session safely if not already started.
   */
  public static function startSession(): void
  {
    if (session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
    }
  }

  /**
   * Attempt login with username + password.
   * Returns true on success.
   */
  public static function attempt(string $username, string $password): bool
  {
    self::startSession();

    $user = \App\Core\Database\DB::one(
      'SELECT id, username, password_hash FROM admin_users WHERE username = :username LIMIT 1',
      ['username' => $username]
    );

    if (!$user) {
      return false;
    }

    if (!password_verify($password, $user['password_hash'])) {
      return false;
    }

    $_SESSION[self::SESSION_KEY] = (int) $user['id'];
    $_SESSION[self::SESSION_USERNAME] = $user['username'];

    // Regenerate session id for safety after login
    session_regenerate_id(true);

    return true;
  }

  /**
   * Check if current user is authenticated.
   */
  public static function check(): bool
  {
    self::startSession();
    return isset($_SESSION[self::SESSION_KEY]);
  }

  /**
   * Get current logged in admin user id.
   */
  public static function id(): ?int
  {
    self::startSession();
    return $_SESSION[self::SESSION_KEY] ?? null;
  }

  /**
   * Get current logged in admin username.
   */
  public static function username(): ?string
  {
    self::startSession();
    return $_SESSION[self::SESSION_USERNAME] ?? null;
  }

  /**
   * Logout and destroy session.
   */
  public static function logout(): void
  {
    self::startSession();

    unset($_SESSION[self::SESSION_KEY]);
    unset($_SESSION[self::SESSION_USERNAME]);

    if (session_status() === PHP_SESSION_ACTIVE) {
      session_destroy();
    }
  }

  /**
   * Require login or redirect to login page.
   * Use in controllers or middleware.
   */
  public static function requireLogin(): void
  {
    if (!self::check()) {
      header('Location: ' . (defined('APP_BASE_URL') ? APP_BASE_URL : '') . '/admin/login');
      exit;
    }
  }
}
