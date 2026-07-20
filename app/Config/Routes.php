<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (is_file(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(true); // ← AJOUTEZ CETTE LIGNE POUR ACTIVER L'AUTO-ROUTING

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'Home::index');

// Routes pour l'opérateur
$routes->group('operateur', function($routes) {
    $routes->get('/', 'OperateurController::index');
    $routes->get('dashboard', 'OperateurController::index');
    $routes->get('prefixes', 'OperateurController::prefixes');
    $routes->post('update-prefixes', 'OperateurController::updatePrefixes');
    $routes->get('types-operations', 'OperateurController::typesOperations');
    $routes->get('type/create', 'OperateurController::createType');
    $routes->post('type/store', 'OperateurController::storeType');
    $routes->get('type/edit/(:num)', 'OperateurController::editType/$1');
    $routes->post('type/update/(:num)', 'OperateurController::updateType/$1');
    $routes->get('type/delete/(:num)', 'OperateurController::deleteType/$1');
    $routes->get('baremes', 'OperateurController::baremes');
    $routes->get('bareme/create', 'OperateurController::createBareme');
    $routes->post('bareme/store', 'OperateurController::storeBareme');
    $routes->get('bareme/edit/(:num)', 'OperateurController::editBareme/$1');
    $routes->post('bareme/update/(:num)', 'OperateurController::updateBareme/$1');
    $routes->get('bareme/delete/(:num)', 'OperateurController::deleteBareme/$1');
    $routes->get('clients', 'OperateurController::clients');
    $routes->get('client/(:num)', 'OperateurController::clientDetail/$1');
    $routes->get('statistiques', 'OperateurController::statistiques');
    
});
// Routes Client
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
// Routes pour l'API
$routes->group('api', function($routes) {
    $routes->get('stats', 'ApiOperateur::getStats');
    $routes->get('client/(:any)', 'ApiOperateur::getClient/$1');
    $routes->post('calculer-frais', 'ApiOperateur::calculerFrais');
});

// Route de test
$routes->get('api-test', 'ApiTestController::index');

// Route pour le test des modèles
$routes->get('test', 'TestController::index');

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}