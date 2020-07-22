<?php
require_once '../vendor/autoload.php';

use SitePoint\Rauth;
use FastRoute\RouteCollector;
use FastRoute\Dispatcher;
use DI\ContainerBuilder;
use App\Controllers\TaskController;
use App\Controllers\AuthController;

session_start();

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$containerBuilder = new ContainerBuilder;
$containerBuilder->addDefinitions([

]);

$container = $containerBuilder->build();

$uri = $_SERVER['REQUEST_URI'];

if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}

$uri = rawurldecode($uri);

$routeList = [
    ['POST', '/login', [AuthController::class, 'loginAction']],
    ['GET','/logout', [AuthController::class, 'logoutAction']],
    ['GET','/login', [AuthController::class, 'loginPage']],

    ['GET', '/', [TaskController::class, 'homeAction']],
    ['GET', '/{page:\d+}', [TaskController::class, 'homeAction']],
    ['POST', '/create', [TaskController::class, 'createAction']],
    ['GET', '/create', [TaskController::class, 'createPage']],
    ['GET', '/task/{id:\d+}', [TaskController::class, 'editPage']],
    ['POST', '/task/{id:\d+}', [TaskController::class, 'editAction']],
];

$dispatcher = FastRoute\simpleDispatcher(
    function (RouteCollector $r) use ($routeList) {
        foreach ($routeList as $routeDef) {
            $r->addRoute($routeDef[0], $routeDef[1], $routeDef[2]);
        }
    }
);

$route = $dispatcher->dispatch(
    $_SERVER['REQUEST_METHOD'],
    $uri
);

switch ($route[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        die('404 not found!');
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        die('405 method not allowed');
    case FastRoute\Dispatcher::FOUND:
        $controller = $route[1];
        $parameters = $route[2];

        $container->call($controller, $parameters);
        break;
}