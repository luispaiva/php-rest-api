<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use App\Controllers\BookController;
use App\Middleware\AddJsonResponseHeader;

require __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->add(new AddJsonResponseHeader());

$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write("REST API is running!");
    return $response;
});

$app->get('/books', [BookController::class, 'index']);
$app->get('/books/{id}', [BookController::class, 'show']);
$app->post('/books', [BookController::class, 'create']);
$app->put('/books/{id}', [BookController::class, 'update']);
$app->delete('/books/{id}', [BookController::class, 'delete']);

$app->run();
