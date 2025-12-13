<?php

$router->get('/', 'HomeController@index');

$router->get('/about', 'AboutController@index');

$router->get('/projects', 'ProjectController@index');
$router->get('/projects/{slug}', 'ProjectController@show');

$router->get('/notes', 'NotesController@index');

$router->get('/contact', 'ContactController@index');

// API-like routs
$router->post('/contact/send', 'ContactController@sendMessage');

$router->get('/downloadcv', 'DownloadController@cvdownload');