<?php

declare(strict_types=1);

namespace App;

use Chubbyphp\Framework\Application;
use Chubbyphp\Framework\Middleware\ExceptionMiddleware;
use Chubbyphp\Framework\Middleware\RouterMiddleware;
use Chubbyphp\Framework\RequestHandler\CallbackRequestHandler;
use Chubbyphp\Framework\Router\FastRoute\Router;
use Chubbyphp\Framework\Router\Route;
use Chubbyphp\SwooleRequestHandler\OnRequest;
use Chubbyphp\SwooleRequestHandler\PsrRequestFactory;
use Chubbyphp\SwooleRequestHandler\SwooleResponseEmitter;
use Psr\Http\Message\ServerRequestInterface;
use Sunrise\Http\Message\ResponseFactory;
use Sunrise\Http\ServerRequest\ServerRequestFactory;
use Sunrise\Http\ServerRequest\UploadedFileFactory;
use Sunrise\Stream\StreamFactory;
use Swoole\Http\Server;

$loader = require __DIR__ . '/../vendor/autoload.php';

$responseFactory = new ResponseFactory();

$app = new Application([
    new ExceptionMiddleware($responseFactory, true),
    new RouterMiddleware(new Router([
        Route::get('/', 'home', new CallbackRequestHandler(
            static function () use ($responseFactory) {
                $response = $responseFactory->createResponse();
                $response->getBody()->write('');

                return $response;
            }
        )),
        Route::get('/user/{id}', 'user_view', new CallbackRequestHandler(
            static function (ServerRequestInterface $request) use ($responseFactory) {
                $response = $responseFactory->createResponse();
                $response->getBody()->write($request->getAttribute('id'));

                return $response;
            }
        )),
        Route::post('/user', 'user_list', new CallbackRequestHandler(
            static function () use ($responseFactory) {
                $response = $responseFactory->createResponse();
                $response->getBody()->write('');

                return $response;
            }
        ))
    ]), $responseFactory),
]);

$server = new Server('localhost', 3000);

$server->set([
    'worker_num' => swoole_cpu_num() * 2,
    'enable_coroutine' => false,
]);

$server->on('request', new OnRequest(
    new PsrRequestFactory(
        new ServerRequestFactory(),
        new StreamFactory(),
        new UploadedFileFactory()
    ),
    new SwooleResponseEmitter(),
    $app
));

$server->start();
