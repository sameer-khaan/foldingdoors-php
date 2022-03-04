<?php
declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use Slim\Exception\HttpNotFoundException;

return function (App $app) {
	$app->options('/{routes:.+}', function ($request, $response, $args) {
	    return $response;
	});

	$app->add(function ($request, $handler) {
	    $response = $handler->handle($request);
	    return $response
	            ->withHeader('Access-Control-Allow-Origin', '*')
	            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
	            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
	});

	$app->group('', function(RouteCollectorProxy $group){

		$group->get('/', function (Request $request, Response $response) {
			$response->getBody()->write('All is Good!');
			return $response;
		});

		$group->get('/get_order/{orderid}','App\Controllers\API\ControllerOrder:getOrder');
		$group->post('/add_order','App\Controllers\API\ControllerOrder:addOrder');
	});

	/**
	 * Catch-all route to serve a 404 Not Found page if none of the routes match
	 * NOTE: make sure this route is defined last
	 */
	$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
	    throw new HttpNotFoundException($request);
	});
};