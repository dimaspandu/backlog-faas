<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Global Exception Boundary (EARLY)
|--------------------------------------------------------------------------
| Ensures all uncaught exceptions are handled consistently
| before the application is fully initialized.
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
require __DIR__ . '/bootstrap.php';

use App\Core\Router;
use App\Core\Middleware\CorsMiddleware;

/*
|--------------------------------------------------------------------------
| CORS Handling (EARLY HTTP CONCERN)
|--------------------------------------------------------------------------
| Must run before routing and controller execution.
*/
CorsMiddleware::handle();

/*
|--------------------------------------------------------------------------
| Register API Routes
|--------------------------------------------------------------------------
*/

$router = new Router();

require __DIR__ . '/routes/api.php';

/*
|--------------------------------------------------------------------------
| Dispatch Incoming Request
|--------------------------------------------------------------------------
*/
$router->dispatch();
