<?php

// Load helpers
require_once './app/Helpers/autoload.php';

if (class_exists('ErrorHandlerHelper')) {
	ErrorHandlerHelper::register();
}

if (class_exists('TrafficHelper')) {
	TrafficHelper::capture();
}

// Load Router
require_once './app/Router.php';

// Load routes
$routes = require_once './routes/web.php';

// Create router instance
$router = new Router();
$router->loadRoutes($routes);

// Dispatch the request
$router->dispatch();

