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




// API v1 base
$routes->group('api/v1', function($routes) {

    // Auth
    $routes->post('auth/login',        'Api\v1\auth\Login::index');
    $routes->post('auth/register',     'Api\v1\auth\Register::index');
    $routes->post('auth/logout',       'Api\v1\auth\Logout::index');
    $routes->post('auth/refresh-token','Api\v1\auth\RefreshToken::index');

    // Users
    $routes->get('users/profile',      'Api\v1\users\Profile::index');
    $routes->put('users/profile/',      'Api\v1\users\Profile::update');
    $routes->put('users/settings',     'Api\v1\users\Settings::update');

    // Pets
    $routes->get('pets',                   'Api\v1\pets\ListPets::index');
    $routes->post('pets',                  'Api\v1\pets\CreatePet::index');
    $routes->get('pets/(:num)',            'Api\v1\pets\ViewPet::show/$1');
    $routes->put('pets/(:num)',            'Api\v1\pets\UpdatePet::update/$1');
    $routes->get('pets/(:num)/state',      'Api\v1\pets\GetPetState::show/$1');
    $routes->put('pets/(:num)/state',      'Api\v1\pets\UpdatePetState::update/$1');
    $routes->post('pets/(:num)/interactions', 'Api\v1\pets\LogInteraction::index/$1');

    // Store
    $routes->get('store/products',         'Api\v1\store\Products::index');
    $routes->get('store/products/(:num)',  'Api\v1\store\ProductDetails::show/$1');
    $routes->get('store/cart',             'Api\v1\store\Cart::index');
    $routes->post('store/cart',            'Api\v1\store\AddToCart::index');
    $routes->get('store/orders',           'Api\v1\store\Orders::index');
    $routes->post('store/orders',          'Api\v1\store\CreateOrder::index');
    $routes->get('store/inventory',        'Api\v1\store\Inventory::index');

    // Payments
    $routes->post('payments/process',       'Api\v1\payments\Process::index');
    $routes->post('payments/verify',        'Api\v1\payments\Verify::index');
    $routes->match(['get', 'post'], 'payments/subscriptions', 'Api\v1\payments\Subscriptions::index');
});
