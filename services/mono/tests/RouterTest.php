<?php

use App\Core\Router;

$router = new Router();
$called = false;

$router->get('/test', function () use (&$called) {
  $called = true;
});

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/test';

ob_start();
$router->dispatch();
ob_end_clean();

assertTrue($called, 'Router should dispatch GET route');

echo "RouterTest passed\n";
