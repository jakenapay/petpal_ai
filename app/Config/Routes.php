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

    // DB
    $routes->get('db', 'Api\V1\Users\Settings::db');

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
    $routes->get('users/get-subscription', 'Api\V1\Users\GetUserSubscription::index');
    $routes->get('users/balance',          'Api\V1\Users\GetUserBalance::index');
    $routes->get('users/inventory',        'Api\V1\Users\GetUserInventory::index');
    $routes->get('users/inventory/category/(:num)',        'Api\V1\Users\GetUserInventory::getCategorizedItemsFromInventory/$1');
    $routes->get('users/level',            'Api\V1\Users\GetUserLevel::index');
    
    
    // Pets
    $routes->get('pets',                        'Api\V1\Pets\ListPets::index');
    $routes->post('pets',                       'Api\V1\Pets\CreatePet::index');
    $routes->get('pets/(:num)',                 'Api\V1\Pets\ViewPet::show/$1');
    $routes->put('pets/(:num)',                 'Api\V1\Pets\UpdatePet::update/$1');
    $routes->get('pets/(:num)/status',          'Api\V1\Pets\GetPetStatus::index/$1');
    $routes->put('pets/(:num)/status-update',   'Api\V1\Pets\UpdatePetStatus::index/$1');
    $routes->get('pets/(:num)/get-interactions',   'Api\V1\Pets\GetPetInteractionHistory::index/$1');
    $routes->post('pets/(:num)/interactions',   'Api\V1\Pets\ProcessPetInteraction::index/$1');
    // $routes->get('pets/(:num)/quests/daily', 'Api\V1\Pets\PetDailyQuestStatus::index/$1');
    $routes->get('pets/(:num)/achievements', 'Api\V1\Pets\GetPetAchievements::index/$1');
    

    // Store
    $routes->get('store/categories',       'Api\V1\Store\GetItemCategories::index');
    $routes->get('store/(:num)/items',            'Api\V1\Store\Items::getItemsbyCategory/$1');
    $routes->get('store/items/search',            'Api\V1\Store\Items::search');
    $routes->get('store/items/featured',           'Api\V1\Store\Items::getFeaturedItems');
    $routes->get('store/coin-packages',            'Api\V1\Store\CoinPackages::index');
    $routes->get('store/diamond-packages',         'Api\V1\Store\DiamondPackages::index');
    $routes->get('store/gacha/types',                    'Api\V1\Store\Gacha::gachaTypes');
    $routes->get('store/gacha/pools',            'Api\V1\Store\Gacha::gachaPool');
    $routes->get('store/gacha/pools/(:segment)', 'Api\V1\Store\Gacha::gachaPool/$1');
    $routes->get('store/gacha/pools/pull-options/(:segment)', 'Api\V1\Store\Gacha::pullOptions/$1');
    $routes->post('store/purchase' ,             'Api\V1\Store\Purchase::purchaseItem');
    $routes->post('store/purchase/coins',       'Api\V1\Store\PurchaseCoins::index');
    $routes->post('store/purchase/diamonds',    'Api\V1\Store\PurchaseDiamonds::index');
    $routes->post('store/gacha/pull' ,             'Api\V1\Store\Gacha::gachaPull');
    $routes->get('store/gacha/history',            'Api\V1\Store\Gacha::history');
    
    // $routes->get('store/products',         'Api\V1\Store\Products::index');
    // $routes->get('store/products/(:num)',  'Api\V1\Store\ProductDetails::show/$1');
    // $routes->get('store/cart',             'Api\V1\Store\Cart::index');
    // $routes->post('store/cart',            'Api\V1\Store\AddToCart::index');
    // $routes->get('store/orders',           'Api\V1\Store\Orders::index');
    // $routes->post('store/orders',          'Api\V1\Store\CreateOrder::index');
    // $routes->get('store/inventory',        'Api\V1\Store\Inventory::index');

    // // Payments
    // $routes->post('payments/process',       'Api\V1\Payments\Process::index');
    // $routes->post('payments/verify',        'Api\V1\Payments\Verify::index');
    // $routes->match(['get', 'post'], 'payments/subscriptions', 'Api\V1\Payments\Subscriptions::index');
    
    // Pet Adoption
    $routes->get('pets/adoption/species',   'Api\V1\Pets\PetAdoption::showAllSpecies');
    $routes->get('pets/adoption/dogbreeds',   'Api\V1\Pets\PetAdoption::showAllDogBreeds');
    $routes->get('pets/adoption/dogpersonalities',   'Api\V1\Pets\PetAdoption::showAllDogPersonalities');
    $routes->get('pets/adoption/catbreeds',   'Api\V1\Pets\PetAdoption::showAllCatBreeds');
    $routes->get('pets/adoption/catpersonalities',   'Api\V1\Pets\PetAdoption::showAllCatPersonalities');
    $routes->get('pets/adoption/EyeColor',  'Api\V1\Pets\PetAdoption::getEyeColors');
    $routes->get('pets/adoption/FurColor',  'Api\V1\Pets\PetAdoption::getFurColors');
    $routes->get('pets/adoption/catpatterns', 'Api\V1\Pets\PetAdoption::getCatPatterns');
    $routes->get('pets/adoption/dogpatterns', 'Api\V1\Pets\PetAdoption::getDogPatterns');
    $routes->get('pets/adoption/dogcolors', 'Api\V1\Pets\PetAdoption::getDogColors');
    $routes->get('pets/adoption/catcolors', 'Api\V1\Pets\PetAdoption::getCatColors');
    $routes->get('pets/adoption/dogeyecolors', 'Api\V1\Pets\PetAdoption::getDogEyeColors');
    $routes->get('pets/adoption/cateyecolors', 'Api\V1\Pets\PetAdoption::getCatEyeColors');

    $routes->get('pets/adoption/generateName', 'Api\V1\Pets\PetAdoption::generateName');


    // Constants
    $routes->get('constants/interaction-types', 'Api\V1\Constants\GetInteractions::index');
    $routes->get('constants/interaction-types/(:num)', 'Api\V1\Constants\GetInteractions::CategorizedInteractions/$1');
    $routes->get('constants/interaction-types/(:num)/(:segment)', 'Api\V1\Constants\GetInteractions::CategorizedInteractions/$1/$2');
    $routes->get('constants/interaction-categories', 'Api\V1\Constants\GetInteractions::InteractionCategories');

    // Item
    $routes->get('items', 'Api\V1\Items\ListItems::index');
    $routes->get('items/categories', 'Api\V1\Items\ListItemsCategories::index');


    //Quests (DAILY x WEEKLY x MONTHLY)
    $routes->get('quests/daily-quests', 'Api\V1\Quest\Quests::dailyQuestStatus');
    $routes->get('quests/weekly-quests', 'Api\V1\Quest\Quests::weeklyQuestStatus');
    $routes->get('quests/daily-quests/daily-extra-rewards', 'Api\V1\Quest\Quests::dailyQuestExtraRewards');
    $routes->get('quests/weekly-quests/weekly-extra-rewards', 'Api\V1\Quest\Quests::weeklyQuestExtraRewards');
    $routes->put('quests/daily-quests/complete-daily-quest', 'Api\V1\Quest\Quests::updateDailyQuest');
    $routes->put('quests/weekly-quests/complete-weekly-quest', 'Api\V1\Quest\Quests::updateWeeklyQuest');
    $routes->post('quests/claim-extra-reward', 'Api\V1\Quest\Quests::claimExtraReward');
});
