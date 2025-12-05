<?php
require_once __DIR__ . '/../config/paths.php';
require_once BOOTSTRAP_FILE;

$data = App::run("AboutController@index");

require_once ABOUT_VIEW_FILE;  // app/views/pages/about.php
?>