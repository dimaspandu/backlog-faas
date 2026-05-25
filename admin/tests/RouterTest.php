<?php

use App\Core\Router;

echo "Running RouterTest...\n";

// Test 1: Basic GET route
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
assertTrue($called, 'Router should dispatch basic GET route');

// Test 2: Route with parameter extraction
$router2 = new Router();
$capturedParams = null;
$router2->get('/admin/sprints/:id', function (array $params) use (&$capturedParams) {
    $capturedParams = $params;
});
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/admin/sprints/42';
ob_start();
$router2->dispatch();
ob_end_clean();
assertTrue($capturedParams !== null, 'Router should capture params');
assertEquals('42', $capturedParams['id'] ?? null, 'Router should extract :id param correctly');

// Test 3: POST route
$router3 = new Router();
$postCalled = false;
$router3->post('/api/admin/products', function () use (&$postCalled) {
    $postCalled = true;
});
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = '/api/admin/products';
ob_start();
$router3->dispatch();
ob_end_clean();
assertTrue($postCalled, 'Router should dispatch POST route');

// Test 4: PUT and DELETE support
$router4 = new Router();
$methodsCalled = [];
$router4->put('/api/admin/sprints/:id', function () use (&$methodsCalled) { $methodsCalled[] = 'PUT'; });
$router4->delete('/api/admin/sprints/:id', function () use (&$methodsCalled) { $methodsCalled[] = 'DELETE'; });

$_SERVER['REQUEST_METHOD'] = 'PUT';
$_SERVER['REQUEST_URI'] = '/api/admin/sprints/7';
ob_start(); $router4->dispatch(); ob_end_clean();

$_SERVER['REQUEST_METHOD'] = 'DELETE';
$_SERVER['REQUEST_URI'] = '/api/admin/sprints/7';
ob_start(); $router4->dispatch(); ob_end_clean();

assertTrue(in_array('PUT', $methodsCalled), 'Router should support PUT');
assertTrue(in_array('DELETE', $methodsCalled), 'Router should support DELETE');

echo "RouterTest passed\n";
