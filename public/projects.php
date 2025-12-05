<?php
require_once __DIR__ . '/../config/paths.php';
require_once BOOTSTRAP_FILE;

$data = App::run("ProjectController@index");

require_once PROJECT_VIEW_FILE; 
?>