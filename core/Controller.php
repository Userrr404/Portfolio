<?php

class Controller {

    /**
     * Load a view file from /views folder
     * Automatically extracts variables passed via $data
     */
    public function view($view, $data = []) {

        $path = ROOT_PATH . "views/" . $view . ".php";

        if (!file_exists($path)) {
            throw new Exception("View file not found: " . $path);
        }

        // Extract array keys as PHP variables
        if (!empty($data)) extract($data);

        require $path;
    }
}
