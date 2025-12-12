<?php

class Router
{
    private array $routes = [];
    private string $baseNamespace = "app\\Controllers\\";

    public function get(string $uri, $action)
    {
        $this->routes['GET'][$this->normalize($uri)] = $action;
    }

    public function post(string $uri, $action)
    {
        $this->routes['POST'][$this->normalize($uri)] = $action;
    }

    public function any(string $uri, $action)
    {
        $this->routes['GET'][$this->normalize($uri)] = $action;
        $this->routes['POST'][$this->normalize($uri)] = $action;
    }

    private function normalize(string $uri): string
    {
        $uri = parse_url($uri, PHP_URL_PATH);

        $basePath = dirname($_SERVER['SCRIPT_NAME']);

        if($basePath !== '/' && strpos($uri, $basePath) === 0){
            $uri = substr($uri, strlen($basePath));
        }

        if($uri === '' || $uri === false){
            return '/';
        }
        // return '/' . trim($uri, '/');
        return $uri;
    }

    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        $normalizedPath = $this->normalize($path);

        // Check for exact match if exists
        if(!isset($this->routes[$method][$normalizedPath])){
            $this->abort(404, "Page not found");
            return;
        }

        $action = $this->routes[$method][$normalizedPath];

        if(is_callable($action)){
            return call_user_func($action);
        }

        // Controller@method format
        if(is_string($action) && str_contains($action, '@')){
            list($controller, $method) = explode('@', $action);

            $controllerClass = $this->baseNamespace . $controller;

            if (!class_exists($controllerClass)) {
                app_log("Missing controller: $controllerClass", "error");
                return $this->abort(404, "Page not found");
            }


            $instance = new $controllerClass();

            if(!method_exists($instance, $method)){
                app_log("Missing method: $method in controller $controllerClass", "error");
                return $this->abort(404, "Page not found");
            }

            try {
                return $instance->$method();
            } catch (Throwable $e) {
                app_log("Controller execution error: " . $e->getMessage(), "error");
                return $this->abort(404, "Page not found");
            }
        }
    }

    public function abort(int $code, string $message = "")
    {
        http_response_code($code);
        echo "<h1>Error $code</h1><p>$message</p>";
        exit;
    }
}