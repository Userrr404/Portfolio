<?php
require_once __DIR__ . '/../config/paths.php';
require_once BOOTSTRAP_FILE;

$data = App::run("ContactController@index");

require_once CONTACT_VIEW_FILE  // app/views/pages/about.php
?>