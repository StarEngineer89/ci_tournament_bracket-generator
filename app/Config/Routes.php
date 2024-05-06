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
        $routes->get('/', 'Api\BracketsController::getBrackets');
        $routes->post('save-list', 'Api\BracketsController::createBrackets');
        $routes->put('update/(:num)', 'Api\BracketsController::updateBracket/$1');
        $routes->delete('delete/(:num)', 'Api\BracketsController::deleteBracket/$1');
        $routes->get('clear', 'Api\BracketsController::clearBrackets');
        $routes->post('generate', 'Api\BracketsController::generateBrackets');
        $routes->post('switch', 'Api\BracketsController::switchBrackets');
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
