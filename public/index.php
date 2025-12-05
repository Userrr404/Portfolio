<?php
/**
 * PUBLIC INDEX.PHP
 * Front Controller (entry point of your application)
 */

require_once __DIR__ . '/../config/paths.php';
require_once BOOTSTRAP_FILE;

// RUN HOME CONTROLLER
$data = App::run("HomeController@index");

// RENDER HOME PAGE VIEW
require_once HOME_VIEW_FILE;
?> 