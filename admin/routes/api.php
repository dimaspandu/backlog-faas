<?php

declare(strict_types=1);

use App\Core\Router;
use App\Controllers\ErrorController;

use App\Controllers\Admin\Api\AuthController;
use App\Controllers\Admin\Api\ProductVariantsController;
use App\Controllers\Admin\Api\ProductsController;
use App\Controllers\Admin\Api\SprintProductsController;
use App\Controllers\Admin\Api\SprintsController;

/** @var Router $router */

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
|--------------------------------------------------------------------------
| Global Error Handlers
|--------------------------------------------------------------------------
| These handlers centralize API error responses
| and ensure consistent JSON output.
*/
$router->setNotFoundHandler([ErrorController::class, 'notFound']);
$router->setErrorHandler([ErrorController::class, 'serverError']);
