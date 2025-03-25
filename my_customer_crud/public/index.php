<?php
require __DIR__ . '/../vendor/autoload.php';

require __DIR__ . '/../src/controllers/ClientController.php';



use Slim\Factory\AppFactory;

$app = AppFactory::create();

$controller = new ClientController($pdo, $redis);

$app->get('/clients', function ($request, $response, $args) use ($controller) {
    $response->getBody()->write(json_encode($controller->getAllClients()));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->post('/client', function ($request, $response, $args) use ($controller) {
    $data = $request->getParsedBody();
    $result = $controller->createClient($data);
    $response->getBody()->write(json_encode(["message" => $result]));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();


?>
