<?php
require_once __DIR__ . '/../config/paths.php';
require_once BOOTSTRAP_FILE;

$data = App::run("NotesController@index");

require_once NOTES_VIEW_FILE;
?>