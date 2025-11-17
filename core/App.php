<?php

/**
 * Enterprise Router for your MVC system
 * Supports: App::run("ProjectController@index")
 */

class App
{
    public static function run($controllerRequest)
    {
        // Must be in "Controller@method" format
        if (!str_contains($controllerRequest, '@')) {
            app_log("Invalid route format: $controllerRequest", "error");
            throw new Exception("Invalid route format. Use Controller@method");
        }

        list($controllerName, $method) = explode('@', $controllerRequest);

        // Build controller path
        $controllerFile = ROOT_PATH . "app/Controllers/" . $controllerName . ".php";

        // Check if file exists
        if (!file_exists($controllerFile)) {
            app_log("Controller file missing: $controllerFile", "error");
            throw new Exception("Controller file not found: " . $controllerName);
        }

        require_once $controllerFile;

        // Check class exists
        if (!class_exists($controllerName)) {
            app_log("Controller class missing: $controllerName", "error");
            throw new Exception("Controller class not found: " . $controllerName);
        }

        // Create controller object
        $controller = new $controllerName();

        // Inheritance check (not required but recommended)
        if (!is_subclass_of($controller, 'Controller')) {
            app_log("$controllerName does not extend Controller base class", "warning");
        }

        // Check method exists
        if (!method_exists($controller, $method)) {
            app_log("Missing controller method: {$controllerName}::{$method}", "error");
            throw new Exception("Controller method not found: {$controllerName}@{$method}");
        }

        // Execute and return data
        try {
            return $controller->$method();
        } catch (Throwable $e) {
            app_log("Controller execution error", "error", [
                "controller" => $controllerName,
                "method"     => $method,
                "error"      => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
