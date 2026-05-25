<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Global Exception Boundary (EARLY)
|--------------------------------------------------------------------------
| This ensures any uncaught exception is handled consistently
| before the application is fully bootstrapped.
*/
set_exception_handler(function (Throwable $e): void {
  if (class_exists(\App\Core\ErrorHandler::class)) {
    \App\Core\ErrorHandler::handle($e);
    return;
  }

  http_response_code(500);
  error_log((string) $e);
  echo '<h1>500 - Server Error</h1>';
  exit;
});

/*
|--------------------------------------------------------------------------
| Bootstrap Application
|--------------------------------------------------------------------------
| Loads environment variables, configuration, and autoloaders.
*/
require dirname(__DIR__) . '/bootstrap.php';

use App\Core\Auth\AdminAuth;
use App\Core\Router;
use App\Core\Middleware\CorsMiddleware;

// Start session early for the entire admin application (needed for login)
AdminAuth::startSession();

/*
|--------------------------------------------------------------------------
| CORS Handling (EARLY HTTP CONCERN)
|--------------------------------------------------------------------------
| Must run before routing and controller execution.
*/
CorsMiddleware::handle();

/*
|--------------------------------------------------------------------------
| Register Routes
|--------------------------------------------------------------------------
*/
$router = new Router();

require dirname(__DIR__) . '/routes/web.php';
require dirname(__DIR__) . '/routes/api.php';

/*
|--------------------------------------------------------------------------
| Dispatch
|--------------------------------------------------------------------------
*/
$router->dispatch();
