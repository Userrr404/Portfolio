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
        $this->routes['GET'][$this->normalize($uri)]  = $action;
        $this->routes['POST'][$this->normalize($uri)] = $action;
    }

    private function normalize(string $uri): string
    {
        $uri = parse_url($uri, PHP_URL_PATH);
        $basePath = dirname($_SERVER['SCRIPT_NAME']);

        if ($basePath !== '/' && strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }

        return $uri === '' ? '/' : $uri;
    }

    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path   = $this->normalize($_SERVER['REQUEST_URI']);

        /* ---------- 1. Exact match ---------- */
        if (isset($this->routes[$method][$path])) {
            return $this->runAction($this->routes[$method][$path]);
        }

        /* ---------- 2. Dynamic routes ---------- */
        foreach ($this->routes[$method] ?? [] as $route => $action) {
            $pattern = preg_replace('#\{[a-zA-Z_]+\}#', '([^/]+)', $route);
            if (preg_match('#^' . $pattern . '$#', $path, $matches)) {
                array_shift($matches);
                return $this->runAction($action, $matches);
            }
        }

        return $this->abort(404, "Page not found");
    }

    private function runAction($action, array $params = [])
    {
        if (is_callable($action)) {
            return call_user_func_array($action, $params);
        }

        if (is_string($action) && str_contains($action, '@')) {
            [$controller, $method] = explode('@', $action);
            $class = $this->baseNamespace . $controller;

            if (!class_exists($class)) {
                return $this->abort(404, "Controller not found");
            }

            $instance = new $class();

            if (!method_exists($instance, $method)) {
                return $this->abort(404, "Method not found");
            }

            return call_user_func_array([$instance, $method], $params);
        }
    }

    public function abort(int $code, string $message = "")
    {
        http_response_code($code);
        echo "<h1>Error {$code}</h1><p>{$message}</p>";
        exit;
    }
}
