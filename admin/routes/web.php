<?php

declare(strict_types=1);

use App\Core\Router;
use App\Controllers\Admin\AuthController;
use App\Controllers\ErrorController;
use App\Core\Middleware\AuthMiddleware;

/** @var Router $router */

/*
|-------------------------------------------------
| Admin Login (public)
|-------------------------------------------------
*/
$router->get('/admin/login', [AuthController::class, 'showLogin']);
$router->post('/admin/logout', [AuthController::class, 'logout']);

/*
|-------------------------------------------------
| Protected Admin Area
|-------------------------------------------------
| All routes under /admin require valid session.
*/
$router->get('/admin', function () {
  AuthMiddleware::handle();
  (new \App\Controllers\Admin\DashboardController())->index();
});

$router->get('/admin/sprints', function () {
  AuthMiddleware::handle();
  (new \App\Controllers\Admin\SprintsController())->index();
});

$router->get('/admin/sprints/new', function () {
  AuthMiddleware::handle();
  (new \App\Controllers\Admin\SprintsController())->createForm();
});

$router->get('/admin/sprints/:id', function (array $params) {
  AuthMiddleware::handle();
  (new \App\Controllers\Admin\SprintsController())->show($params);
});

$router->get('/admin/products', function () {
  AuthMiddleware::handle();
  (new \App\Controllers\Admin\ProductsController())->index();
});

$router->get('/admin/sprint-products', function () {
  AuthMiddleware::handle();
  (new \App\Controllers\Admin\SprintProductsController())->index();
});

/*
|-------------------------------------------------
| Legacy / root redirect for convenience
|-------------------------------------------------
*/
$router->get('/', function () {
  header('Location: /admin/login');
  exit;
});

/*
|-------------------------------------------------
| Development debug routes
|-------------------------------------------------
*/
if (env('APP_ENV') === 'development' && env('APP_DEBUG') === true) {
  $router->get('/_debug/500', function () {
    throw new Exception('Forced 500 error');
  });
}

/*
|-------------------------------------------------
| Global error handlers
|-------------------------------------------------
*/
$router->setNotFoundHandler([ErrorController::class, 'notFound']);
$router->setErrorHandler([ErrorController::class, 'serverError']);
