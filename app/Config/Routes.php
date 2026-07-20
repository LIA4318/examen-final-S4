<?php

namespace Config;

$routes = Services::routes();

if (is_file(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(true);

// Route par défaut
$routes->get('/', 'Home::index');

// ============================================
// ROUTES OPÉRATEUR
// ============================================
$routes->group('operateur', function($routes) {
    // Dashboard
    $routes->get('/', 'OperateurController::index');
    $routes->get('dashboard', 'OperateurController::index');
    
    // Préfixes
    $routes->get('prefixes', 'OperateurController::prefixes');
    $routes->post('update-prefixes', 'OperateurController::updatePrefixes');
    
    // Types d'opérations
    $routes->get('types-operations', 'OperateurController::typesOperations');
    $routes->get('type/create', 'OperateurController::createType');
    $routes->post('type/store', 'OperateurController::storeType');
    $routes->get('type/edit/(:num)', 'OperateurController::editType/$1');
    $routes->post('type/update/(:num)', 'OperateurController::updateType/$1');
    $routes->get('type/delete/(:num)', 'OperateurController::deleteType/$1');
    
    // Barèmes de frais
    $routes->get('baremes', 'OperateurController::baremes');
    $routes->get('bareme/create', 'OperateurController::createBareme');
    $routes->post('bareme/store', 'OperateurController::storeBareme');
    $routes->get('bareme/edit/(:num)', 'OperateurController::editBareme/$1');
    $routes->post('bareme/update/(:num)', 'OperateurController::updateBareme/$1');
    $routes->get('bareme/delete/(:num)', 'OperateurController::deleteBareme/$1');
    
    // Clients
    $routes->get('clients', 'OperateurController::clients');
    $routes->get('client/(:num)', 'OperateurController::clientDetail/$1');
    
    // Statistiques
    $routes->get('statistiques', 'OperateurController::statistiques');
});

// ============================================
// ROUTES CLIENT
// ============================================
$routes->group('client', function($routes) {
    $routes->get('login', 'ClientController::login');
    $routes->post('doLogin', 'ClientController::doLogin');
    $routes->get('dashboard', 'ClientController::dashboard');
    $routes->get('depot', 'ClientController::depot');
    $routes->post('doDepot', 'ClientController::doDepot');
    $routes->get('retrait', 'ClientController::retrait');
    $routes->post('doRetrait', 'ClientController::doRetrait');
    $routes->get('transfert', 'ClientController::transfert');
    $routes->post('doTransfert', 'ClientController::doTransfert');
    $routes->get('historique', 'ClientController::historique');
    $routes->get('logout', 'ClientController::logout');
    $routes->get('getSolde', 'ClientController::getSolde');
});

// ============================================
// ROUTES API
// ============================================
$routes->group('api', function($routes) {
    $routes->get('stats', 'ApiOperateur::getStats');
    $routes->get('client/(:any)', 'ApiOperateur::getClient/$1');
    $routes->post('calculer-frais', 'ApiOperateur::calculerFrais');
});

// Route de test
$routes->get('api-test', 'ApiTestController::index');