<?php

declare(strict_types=1);

use App\Core\Router;
use App\Controllers\HomeController;
use App\Controllers\HealthController;
use App\Controllers\ItemController;
use App\Controllers\DbTestController;
use App\Controllers\ErrorController;

use App\Controllers\Admin\Api\AuthController;
use App\Controllers\Admin\Api\ProductVariantsController;
use App\Controllers\Admin\Api\ProductsController;
use App\Controllers\Admin\Api\SprintProductsController;
use App\Controllers\Admin\Api\SprintsController;

use App\Core\Middleware\CsrfMiddleware;

/** @var Router $router */

$router->post('/api/test', [HomeController::class, 'api']);
$router->get('/health', [HealthController::class, 'check']);

/*
|-------------------------------------------------
| Admin API (JSON)
|-------------------------------------------------
*/
$router->post('/api/admin/login', [AuthController::class, 'login']);
$router->post('/api/admin/logout', [AuthController::class, 'logout']);

/*
|-------------------------------------------------
| Admin Sprints API
|-------------------------------------------------
*/
$router->get('/api/admin/sprints', [SprintsController::class, 'index']);
$router->post('/api/admin/sprints', [SprintsController::class, 'store']);

$router->get('/api/admin/sprints/:id', [SprintsController::class, 'show']);
$router->put('/api/admin/sprints/:id', [SprintsController::class, 'update']);
$router->delete('/api/admin/sprints/:id', [SprintsController::class, 'destroy']);

/*
|-------------------------------------------------
| Admin Products API
|-------------------------------------------------
*/
$router->get('/api/admin/products', [ProductsController::class, 'index']);
$router->post('/api/admin/products', [ProductsController::class, 'store']);

$router->get('/api/admin/products/:id', [ProductsController::class, 'show']);
$router->put('/api/admin/products/:id', [ProductsController::class, 'update']);
$router->delete('/api/admin/products/:id', [ProductsController::class, 'destroy']);

/*
|-------------------------------------------------
| Admin Product Variants API
|-------------------------------------------------
*/
$router->get('/api/admin/products/:id/variants', [ProductVariantsController::class, 'index']);
$router->post('/api/admin/products/:id/variants', [ProductVariantsController::class, 'store']);
$router->get('/api/admin/products/:id/variants/:variantId', [ProductVariantsController::class, 'show']);
$router->put('/api/admin/products/:id/variants/:variantId', [ProductVariantsController::class, 'update']);
$router->delete('/api/admin/products/:id/variants/:variantId', [ProductVariantsController::class, 'destroy']);

/*
|-------------------------------------------------
| Admin Sprint Products API
|-------------------------------------------------
*/
$router->get('/api/admin/sprint-products', [SprintProductsController::class, 'index']);
$router->post('/api/admin/sprint-products', [SprintProductsController::class, 'store']);
$router->get('/api/admin/sprint-products/:id', [SprintProductsController::class, 'show']);
$router->put('/api/admin/sprint-products/:id', [SprintProductsController::class, 'update']);
$router->delete('/api/admin/sprint-products/:id', [SprintProductsController::class, 'destroy']);

/*
|----------------------------------------------------------
| Example REST-style endpoints
|----------------------------------------------------------
*/
$router->get('/api/items', [ItemController::class, 'index']);

/*
|-------------------------------------------------
| Example: Middleware usage on mutating routes
|-------------------------------------------------
| CsrfMiddleware is called before the controller for POST/PUT/DELETE.
| This demonstrates protecting state-changing endpoints.
*/
$router->post('/api/items', function () {
    CsrfMiddleware::handle();
    (new ItemController())->store();
});

$router->put('/api/items/:id', function (array $params) {
    CsrfMiddleware::handle();
    (new ItemController())->update($params);
});

$router->delete('/api/items/:id', function (array $params) {
    CsrfMiddleware::handle();
    (new ItemController())->destroy($params);
});

/*
|--------------------------------------------------------------------------
| Database Test Routes (Development Only)
|--------------------------------------------------------------------------
*/
if (
  env('APP_ENV') === 'development' &&
  env('APP_DEBUG') === true
) {
  $router->get('/_debug/db/items', [DbTestController::class, 'items']);
}

/*
|--------------------------------------------------------------------------
| Global Error Handlers
|--------------------------------------------------------------------------
| These handlers centralize API error responses
| and ensure consistent JSON output.
*/
$router->setNotFoundHandler([ErrorController::class, 'notFound']);
$router->setErrorHandler([ErrorController::class, 'serverError']);
