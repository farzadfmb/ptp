<?php

class Router
{
    private $routes = [];
    private $basePath = '';
    private $projectRoot = '';

    public function __construct()
    {
        $this->basePath = $this->getBasePath();
        $this->projectRoot = dirname(__DIR__); // Get project root directory
    }

    /**
     * Add a route
     */
    public function addRoute($uri, $controller, $method, $httpMethod = 'GET')
    {
        $this->routes[] = [
            'uri' => $uri,
            'controller' => $controller,
            'method' => $method,
            'http_method' => $httpMethod
        ];
    }

    /**
     * Handle the current request
     */
    public function dispatch()
    {
        $requestUri = $this->getRequestUri();
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        
        // Create route key
        $routeKey = $httpMethod . ':' . $requestUri;
        
        // Debug information
        // echo "Request URI: $requestUri<br>";
        // echo "HTTP Method: $httpMethod<br>";
        // echo "Route Key: $routeKey<br>";
        // print_r($this->routes);
        
        if (isset($this->routes[$routeKey])) {
            $route = $this->routes[$routeKey];
            return $this->callController($route['controller'], $route['method']);
        }

        // 404 Not Found
        $this->notFound();
    }

    /**
     * Get the current request URI
     */
    private function getRequestUri()
    {
        $uri = $_SERVER['REQUEST_URI'];
        $uri = parse_url($uri, PHP_URL_PATH);
        
        // Remove base path
        if ($this->basePath !== '/') {
            $uri = str_replace($this->basePath, '', $uri);
        }
        
        // Remove trailing slash
        $uri = rtrim($uri, '/');
        if ($uri === '') {
            $uri = '/';
        }
        
        return $uri;
    }

    /**
     * Get base path
     */
    private function getBasePath()
    {
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $basePath = dirname($scriptName);
        
        // Normalize path separators
        if ($basePath === '\\' || $basePath === '.') {
            $basePath = '/';
        }
        
        // Remove trailing slash unless it's root
        if ($basePath !== '/' && substr($basePath, -1) === '/') {
            $basePath = rtrim($basePath, '/');
        }
        
        return $basePath;
    }

    /**
     * Check if route matches
     */
    private function matchRoute($routeUri, $requestUri)
    {
        // Simple exact match for now
        return $routeUri === $requestUri;
    }

    /**
     * Call the controller method
     */
    private function callController($controllerName, $methodName)
    {
        $controllerFile = $this->projectRoot . "/app/Controllers/{$controllerName}.php";
        
        if (!file_exists($controllerFile)) {
            die("Controller not found: {$controllerName}. Looking for: {$controllerFile}");
        }

        require_once $controllerFile;

        if (!class_exists($controllerName)) {
            die("Controller class not found: {$controllerName}");
        }

        $controller = new $controllerName();

        if (!method_exists($controller, $methodName)) {
            die("Method not found: {$controllerName}::{$methodName}");
        }

        if (method_exists($controller, 'guardActionPermissions')) {
            $controller->guardActionPermissions($methodName);
        }

        return $controller->$methodName();
    }

    /**
     * Handle 404 Not Found
     */
    private function notFound()
    {
        http_response_code(404);
        echo "<!DOCTYPE html>
<html lang='fa' dir='rtl'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>صفحه یافت نشد</title>
    <style>
        body { font-family: 'Tahoma', sans-serif; text-align: center; padding: 50px; }
        h1 { color: #e74c3c; }
        p { color: #7f8c8d; }
        a { color: #3498db; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h1>404 - صفحه یافت نشد</h1>
    <p>صفحه مورد نظر شما وجود ندارد.</p>
    <a href='/ptp'>بازگشت به صفحه اصلی</a>
</body>
</html>";
    }

    /**
     * Load routes from array
     */
    public function loadRoutes($routes)
    {
        $this->routes = $routes;
    }
}