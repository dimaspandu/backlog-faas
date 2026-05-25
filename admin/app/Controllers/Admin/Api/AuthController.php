<?php

declare(strict_types=1);

namespace App\Controllers\Admin\Api;

use App\Core\Auth\AdminAuth;
use App\Core\Http\JsonResponse;

final class AuthController
{
  /**
   * POST /api/admin/login
   * JSON body: { "username": "...", "password": "..." }
   */
  public function login(): JsonResponse
  {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];

    $username = trim($input['username'] ?? $_POST['username'] ?? '');
    $password = $input['password'] ?? $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
      return new JsonResponse([
        'success' => false,
        'error'   => 'Username and password are required.'
      ], 400);
    }

    try {
      if (AdminAuth::attempt($username, $password)) {
        return new JsonResponse([
          'success'  => true,
          'message'  => 'Login successful',
          'redirect' => '/admin'
        ]);
      }

      return new JsonResponse([
        'success' => false,
        'error'   => 'Invalid username or password.'
      ], 401);

    } catch (\Throwable $e) {
      // Never return HTML 500 for API endpoints
      if (env('APP_DEBUG', false)) {
        return new JsonResponse([
          'success' => false,
          'error'   => 'Server error: ' . $e->getMessage(),
          'trace'   => $e->getTraceAsString()
        ], 500);
      }

      return new JsonResponse([
        'success' => false,
        'error'   => 'Internal server error. Check logs or database connection.'
      ], 500);
    }
  }

  /**
   * POST /api/admin/logout
   */
  public function logout(): JsonResponse
  {
    AdminAuth::logout();

    return new JsonResponse([
      'success'  => true,
      'message'  => 'Logged out',
      'redirect' => '/admin/login'
    ]);
  }
}
