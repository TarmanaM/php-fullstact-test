<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/controllers/ClientController.php';
require __DIR__ . '/../config/database.php';

use Slim\Factory\AppFactory;

$app = AppFactory::create();

// Middleware untuk menangani routing
$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$controller = new ClientController($pdo, $redis);

// Route test untuk memastikan Slim berjalan
$app->get('/test', function ($request, $response, $args) {
    $response->getBody()->write("Hello, Slim!");
    return $response;
});

// Route untuk mendapatkan semua client
$app->get('/clients', function ($request, $response, $args) use ($controller) {
    $data = $controller->getAllClients();
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

// Route untuk menambah client baru
$app->post('/client', function ($request, $response, $args) use ($controller) {
    $data = $request->getParsedBody();
    $result = $controller->createClient($data);
    $response->getBody()->write(json_encode($result));
    return $response->withHeader('Content-Type', 'application/json');
});

// Route untuk update client berdasarkan slug
$app->put('/client/{slug}', function ($request, $response, $args) use ($controller) {
    $slug = $args['slug'];
    $data = $request->getParsedBody();
    $result = $controller->updateClient($slug, $data);
    $response->getBody()->write(json_encode($result));
    return $response->withHeader('Content-Type', 'application/json');
});

// Route untuk menghapus client berdasarkan slug
$app->delete('/client/{slug}', function ($request, $response, $args) use ($controller) {
    $slug = $args['slug'];
    $result = $controller->deleteClient($slug);
    $response->getBody()->write(json_encode($result));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
?>
