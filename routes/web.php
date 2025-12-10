<?php

$router->get('/', 'HomeController@index');
$router->get('/about', 'AboutController@about');
$router->get('/projects', 'ProjectController@index');
$router->get('/notes', 'NotesController@index');
$router->get('/contact', 'ContactController@contact');

// API-like routs
$router->post('/contact/send', 'ContactController@sendMessage');

$router->get('/downloadcv', 'DownloadController@cvdownload');