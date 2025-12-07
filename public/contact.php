<?php
require_once __DIR__ . '/../config/paths.php';
require_once BOOTSTRAP_FILE;
require_once __DIR__ . '/../vendor/autoload.php';  

$data = App::run("ContactController@index");

require_once CONTACT_VIEW_FILE;   // correct file, with semicolon
?>