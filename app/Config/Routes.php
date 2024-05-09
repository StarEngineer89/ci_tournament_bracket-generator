<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index', ['filter' => 'checkbrackets']);
$routes->get('/participants', 'Home::participants');
$routes->get('/brackets', 'Home::brackets', ['filter' => 'checkbrackets']);
$routes->match(['get', 'post'], '/player/(:any)', 'RenderAudioController::index/$1');

$routes->group('tournaments', static function ($routes) {
    $routes->get('/', 'TournamentController::index');
    $routes->get('create', 'TournamentController::create');
    $routes->get('(:num)/view', 'TournamentController::view/$1');
});

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
    
    $routes->group('tournaments', static function ($routes) {
        $routes->post('save', 'Api\TournamentController::save');
        $routes->post('(:num)/update', 'Api\TournamentController::update/$1');
        $routes->post('upload', 'Api\TournamentController::upload');
    });
});

/** Shield routs for authentication */
service('auth')->routes($routes);
