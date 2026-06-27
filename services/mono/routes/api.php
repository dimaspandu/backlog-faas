<?php

declare(strict_types=1);

use App\Controllers\SprintController;
use App\Controllers\ErrorController;
use App\Controllers\HealthController;
use App\Controllers\ProductController;
use App\Controllers\SprintContractController;
use App\Controllers\SprintProductController;
use App\Middleware\SprintTokenMiddleware;

$router->get('/', [HealthController::class, 'check']);
$router->get('/products', [ProductController::class, 'list']);
$router->get('/products/:id/active-sprints', [SprintProductController::class, 'activeSprintList']);
$router->get('/sprints', [SprintController::class, 'list']);
$router->get('/sprints/:token', SprintTokenMiddleware::with([SprintController::class, 'show']));
$router->post('/sprints/:token/contracts', SprintTokenMiddleware::with([SprintContractController::class, 'create']));
$router->get('/sprints/:token/contracts', SprintTokenMiddleware::with([SprintContractController::class, 'list']));
$router->get('/sprints/:token/contracts/:contractNumber', SprintTokenMiddleware::with([SprintContractController::class, 'show']));

$router->setNotFoundHandler([ErrorController::class, 'notFound']);
$router->setErrorHandler([ErrorController::class, 'serverError']);
