<?php
declare(strict_types=1);

use App\Application\Handlers\HttpErrorHandler;
use App\Application\Handlers\ShutdownHandler;
use App\Application\ResponseEmitter\ResponseEmitter;
use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/config.php';

// Instantiate PHP-DI ContainerBuilder
$containerBuilder = new ContainerBuilder();

if (false) { // Should be set to true in production
	$containerBuilder->enableCompilation(dirname(__DIR__) . '/var/cache');
}

// Set up settings
$settings = require dirname(__DIR__) . '/libs/settings.php';
$settings($containerBuilder);

// Set up dependencies
$dependencies = require dirname(__DIR__) . '/libs/dependencies.php';
$dependencies($containerBuilder);

// Build PHP-DI Container instance
$container = $containerBuilder->build();

// Instantiate the app
AppFactory::setContainer($container);
$app = AppFactory::create();
$callableResolver = $app->getCallableResolver();

// Register middleware
$middleware = require dirname(__DIR__) . '/libs/middleware.php';
$middleware($app);

// Register routes
$routes = require dirname(__DIR__) . '/libs/routes.php';
$routes($app);

function my_autoloader($className) {
    $arr = preg_split('/(?=[A-Z])/', $className);
    $arr = array_slice($arr, 1);
    $path = false;
    if (stristr($className, 'Controller')) {
        $path = dirname(__DIR__) . "/app/controllers/" . $className . ".php";
    } else {
        $path = dirname(__DIR__) . "/app/models/" . $className . ".php";
    }
    if (file_exists($path)) {
        require_once $path;
    }
}

spl_autoload_register("my_autoloader");

/** @var bool $displayErrorDetails */
$displayErrorDetails = $container->get('settings')['displayErrorDetails'];

// Create Request object from globals
$serverRequestCreator = ServerRequestCreatorFactory::create();
$request = $serverRequestCreator->createServerRequestFromGlobals();

// Create Error Handler
$responseFactory = $app->getResponseFactory();
$errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);

// Create Shutdown Handler
// $shutdownHandler = new ShutdownHandler($request, $errorHandler, $displayErrorDetails);
// register_shutdown_function($shutdownHandler);

// Add Routing Middleware
$app->addRoutingMiddleware();

// Add Error Middleware
// $errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, false, false);
// $errorMiddleware->setDefaultErrorHandler($errorHandler);

$app->run();
// // Run App & Emit Response
// $response = $app->handle($request);
// $responseEmitter = new ResponseEmitter();
// $responseEmitter->emit($response);
