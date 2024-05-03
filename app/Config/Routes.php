<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index', ['filter' => 'checkbrackets']);
$routes->get('/participants', 'Home::participants');
$routes->get('/brackets', 'Home::brackets', ['filter' => 'checkbrackets']);

$routes->group('api', static function ($routes) {
    $routes->group('brackets', static function ($routes) {
        $routes->get('/', 'ApiController::getBrackets');
        $routes->get('get/(:num)', 'ApiController::getBracket/$1');
        $routes->post('new', 'ApiController::createBracket');
        $routes->post('save-list', 'ApiController::createBrackets');
        $routes->put('update/(:num)', 'ApiController::updateBracket/$1');
        $routes->delete('delete/(:num)', 'ApiController::deleteBracket/$1');
        $routes->get('clear', 'ApiController::clearBrackets');
    });

    $routes->group('participants', static function ($routes) {
        $routes->get('/', 'Api\ParticipantsController::getParticipants');
        $routes->post('new', 'Api\ParticipantsController::addParticipant');
        $routes->post('updateList', 'Api\ParticipantsController::updateParticipants');
        $routes->delete('delete/(:num)', 'Api\ParticipantsController::deleteParticipant/$1');
    });    
});

/** Shield routs for authentication */
service('auth')->routes($routes);
