<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index', ['filter' => 'auth']);

// Views
$routes->get('register', 'Auth::register');
$routes->get('login', 'Auth::login');
$routes->get('profile', 'Users::profile', ['filter' => 'auth']);

$routes->post('register', 'Auth::registerUser');
$routes->post('login', 'Auth::loginUser');
$routes->get('logout', 'Auth::logout', ['filter' => 'auth']);

$routes->post('editProfile', 'Users::editProfile', ['filter' => 'auth']);

// API
$routes->post('handleLogin', 'API::handleLogin');