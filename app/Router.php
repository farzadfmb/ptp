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
    <title>صفحه یافت نشد - 404</title>
    <link rel='preconnect' href='https://fonts.googleapis.com'>
    <link rel='preconnect' href='https://fonts.gstatic.com' crossorigin>
    <link href='https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700&display=swap' rel='stylesheet'>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Vazirmatn', 'Tahoma', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }
        
        /* Animated background particles */
        .particles {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }
        
        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 15s infinite ease-in-out;
        }
        
        .particle:nth-child(1) {
            width: 80px;
            height: 80px;
            left: 10%;
            top: 20%;
            animation-delay: 0s;
            animation-duration: 20s;
        }
        
        .particle:nth-child(2) {
            width: 60px;
            height: 60px;
            left: 80%;
            top: 60%;
            animation-delay: 2s;
            animation-duration: 18s;
        }
        
        .particle:nth-child(3) {
            width: 100px;
            height: 100px;
            left: 50%;
            top: 10%;
            animation-delay: 4s;
            animation-duration: 22s;
        }
        
        .particle:nth-child(4) {
            width: 70px;
            height: 70px;
            left: 20%;
            top: 70%;
            animation-delay: 1s;
            animation-duration: 19s;
        }
        
        .particle:nth-child(5) {
            width: 90px;
            height: 90px;
            left: 70%;
            top: 30%;
            animation-delay: 3s;
            animation-duration: 21s;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
                opacity: 0.3;
            }
            50% {
                transform: translateY(-100px) rotate(180deg);
                opacity: 0.6;
            }
        }
        
        .container {
            position: relative;
            z-index: 2;
            text-align: center;
            color: white;
            max-width: 600px;
            padding: 40px 20px;
            animation: fadeIn 0.8s ease-out;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .error-code {
            font-size: 180px;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 20px;
            text-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            animation: pulse 2s ease-in-out infinite;
            background: linear-gradient(45deg, #fff, #f0f0f0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }
        
        .error-title {
            font-size: 32px;
            font-weight: 600;
            margin-bottom: 16px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        
        .error-message {
            font-size: 18px;
            font-weight: 400;
            margin-bottom: 40px;
            opacity: 0.95;
            line-height: 1.6;
            text-shadow: 0 1px 5px rgba(0, 0, 0, 0.15);
        }
        
        .icon-container {
            margin-bottom: 30px;
            animation: bounce 2s ease-in-out infinite;
        }
        
        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-20px);
            }
        }
        
        .icon {
            width: 120px;
            height: 120px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }
        
        .icon svg {
            width: 60px;
            height: 60px;
            fill: white;
        }
        
        .suggestions {
            margin-top: 50px;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .suggestions h3 {
            font-size: 20px;
            font-weight: 500;
            margin-bottom: 20px;
            opacity: 0.9;
        }
        
        .suggestions-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .suggestions-list li {
            font-size: 16px;
            margin-bottom: 12px;
            opacity: 0.85;
            transition: opacity 0.3s ease;
        }
        
        .suggestions-list li:hover {
            opacity: 1;
        }
        
        .suggestions-list li:before {
            content: '◆';
            margin-left: 10px;
            opacity: 0.6;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .error-code {
                font-size: 120px;
            }
            
            .error-title {
                font-size: 24px;
            }
            
            .error-message {
                font-size: 16px;
            }
            
            .icon {
                width: 100px;
                height: 100px;
            }
            
            .icon svg {
                width: 50px;
                height: 50px;
            }
        }
        
        @media (max-width: 480px) {
            .error-code {
                font-size: 90px;
            }
            
            .error-title {
                font-size: 20px;
            }
            
            .error-message {
                font-size: 14px;
            }
            
            .container {
                padding: 30px 15px;
            }
        }
    </style>
</head>
<body>
    <div class='particles'>
        <div class='particle'></div>
        <div class='particle'></div>
        <div class='particle'></div>
        <div class='particle'></div>
        <div class='particle'></div>
    </div>
    
    <div class='container'>
        <div class='icon-container'>
            <div class='icon'>
                <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'>
                    <path d='M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z'/>
                </svg>
            </div>
        </div>
        
        <div class='error-code'>404</div>
        <h1 class='error-title'>صفحه مورد نظر یافت نشد</h1>
        <p class='error-message'>
            متأسفانه صفحه‌ای که به دنبال آن هستید وجود ندارد یا ممکن است حذف شده باشد.
        </p>
        
        <div class='suggestions'>
            <h3>پیشنهادات:</h3>
            <ul class='suggestions-list'>
                <li>آدرس وارد شده را بررسی کنید</li>
                <li>از نوار جستجوی مرورگر استفاده کنید</li>
                <li>به صفحه قبل بازگردید</li>
            </ul>
        </div>
    </div>
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