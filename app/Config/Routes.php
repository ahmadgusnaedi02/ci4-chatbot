<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/ppdb', 'Ppdb::index');
$routes->get('/admin', 'Admin::index');
$routes->post('/chatbot', 'Chatbot::chat');