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
    $routes->post('auth/login',        'Api\V1\Auth\Login::index');
    $routes->post('auth/register',     'Api\V1\Auth\Register::index');
    $routes->post('auth/logout',       'Api\V1\Auth\Logout::index');
    $routes->post('auth/refresh-token','Api\V1\Auth\RefreshToken::index');
    $routes->post('auth/resend',       'Api\V1\Auth\Register::resendVerificationCode');
    $routes->post('auth/verify',       'Api\V1\Auth\Register::verifyEmail');

    // Users
    $routes->get('users/profile',      'Api\V1\Users\Profile::index');
    $routes->put('users/profile/',      'Api\V1\Users\Profile::update');
    $routes->put('users/settings',     'Api\V1\Users\Settings::update');

    // Pets
    $routes->get('pets',                        'Api\V1\Pets\ListPets::index');
    $routes->post('pets',                       'Api\V1\Pets\CreatePet::index');
    $routes->get('pets/(:num)',                 'Api\V1\Pets\ViewPet::show/$1');
    $routes->put('pets/(:num)',                 'Api\V1\Pets\UpdatePet::update/$1');
    $routes->get('pets/(:num)/status',          'Api\V1\Pets\GetPetStatus::index/$1');
    $routes->put('pets/(:num)/status-update',   'Api\V1\Pets\UpdatePetStatus::index/$1');
    $routes->post('pets/(:num)/interactions',   'Api\V1\Pets\LogInteraction::index/$1');

    // Store
    $routes->get('store/products',         'Api\V1\Store\Products::index');
    $routes->get('store/products/(:num)',  'Api\V1\Store\ProductDetails::show/$1');
    $routes->get('store/cart',             'Api\V1\Store\Cart::index');
    $routes->post('store/cart',            'Api\V1\Store\AddToCart::index');
    $routes->get('store/orders',           'Api\V1\Store\Orders::index');
    $routes->post('store/orders',          'Api\V1\Store\CreateOrder::index');
    $routes->get('store/inventory',        'Api\V1\Store\Inventory::index');

    // Payments
    $routes->post('payments/process',       'Api\V1\Payments\Process::index');
    $routes->post('payments/verify',        'Api\V1\Payments\Verify::index');
    $routes->match(['get', 'post'], 'payments/subscriptions', 'Api\V1\Payments\Subscriptions::index');
    
    // Pet Adoption
    $routes->get('pets/adoption/species',   'Api\V1\Pets\PetAdoption::showAllSpecies');
    $routes->get('pets/adoption/dogbreeds',   'Api\V1\Pets\PetAdoption::showAllDogBreeds');
    $routes->get('pets/adoption/dogpersonalities',   'Api\V1\Pets\PetAdoption::showAllDogPersonalities');
    $routes->get('pets/adoption/catbreeds',   'Api\V1\Pets\PetAdoption::showAllCatBreeds');
    $routes->get('pets/adoption/catpersonalities',   'Api\V1\Pets\PetAdoption::showAllCatPersonalities');
    $routes->get('pets/adoption/EyeColor',  'Api\V1\Pets\PetAdoption::getEyeColors');
    $routes->get('pets/adoption/FurColor',  'Api\V1\Pets\PetAdoption::getFurColors');

    $routes->get('pets/adoption/generateName', 'Api\V1\Pets\PetAdoption::generateName');
});
