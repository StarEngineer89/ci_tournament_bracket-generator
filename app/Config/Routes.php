<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/participants', 'Home::participants');
$routes->get('/brackets', 'Home::brackets');
$routes->match(['get', 'post'], '/player/(:any)', 'RenderAudioController::index/$1');

$routes->group('tournaments', static function ($routes) {
    $routes->get('/', 'TournamentController::index');
    $routes->get('create', 'TournamentController::create');
    $routes->get('(:num)/view', 'TournamentController::view/$1');
    $routes->get('shared/(:segment)', 'TournamentController::viewShared/$1');
});

$routes->group('api', static function ($routes) {
    $routes->group('brackets', static function ($routes) {
        $routes->post('save-list', 'Api\BracketsController::createBrackets');
        $routes->put('update/(:num)', 'Api\BracketsController::updateBracket/$1');
        $routes->delete('delete/(:num)', 'Api\BracketsController::deleteBracket/$1');
        $routes->post('generate', 'Api\BracketsController::generateBrackets');
        $routes->post('switch', 'Api\BracketsController::switchBrackets');
    });

    $routes->group('participants', static function ($routes) {
        $routes->get('/', 'Api\ParticipantsController::getParticipants');
        $routes->post('new', 'Api\ParticipantsController::addParticipant');
        $routes->post('update/(:num)', 'Api\ParticipantsController::updateParticipant/$1');
        $routes->delete('delete/(:num)', 'Api\ParticipantsController::deleteParticipant/$1');
        $routes->post('import', 'Api\ParticipantsController::importParticipants');
        $routes->post('removeDuplicates', 'Api\ParticipantsController::removeDuplicates');
        $routes->get('clear', 'Api\ParticipantsController::clearParticipants');
    });

    $routes->group('tournaments', static function ($routes) {
        $routes->post('save', 'Api\TournamentController::save');
        $routes->get('(:num)/brackets', 'Api\BracketsController::getBrackets/$1');
        $routes->post('(:num)/update', 'Api\TournamentController::update/$1');
        $routes->post('(:num)/update-music', 'Api\TournamentController::updateMusic/$1');
        $routes->post('upload', 'Api\TournamentController::upload');
        $routes->get('(:num)/music-settings', 'Api\TournamentController::getMusicSettings/$1');
        $routes->get('(:num)/clear', 'Api\BracketsController::clearBrackets/$1');
        $routes->get('(:num)/delete', 'Api\TournamentController::delete/$1');
        $routes->post('(:num)/share', 'Api\TournamentController::share/$1');
        $routes->get('(:num)/share', 'Api\TournamentController::fetchShareSettings/$1');
        $routes->get('purge-share/(:num)', 'Api\TournamentController::purgechShareSettings/$1');
        $routes->get('(:num)/getActionHistory', 'Api\TournamentController::getActionHistory/$1');
        $routes->get('fetchUsersList', 'Api\TournamentController::fetchUsersList');
        $routes->get('fetchShareSetting/(:num)', 'Api\TournamentController::fetchShareSetting/$1');
        $routes->post('bulkDelete', 'Api\TournamentController::bulkDelete');
        $routes->post('bulkReset', 'Api\TournamentController::bulkReset');
        $routes->post('bulkUpdate', 'Api\TournamentController::bulkUpdate');
    });
});

/** API to fetch the data of shared tournaments */
$routes->group('api/shared', static function ($routes) {
    $routes->group('tournaments', static function ($routes) {
        $routes->get('(:num)/brackets', 'Api\BracketsController::getBrackets/$1');
        $routes->post('(:num)/update', 'Api\TournamentController::update/$1');
        $routes->get('(:num)/delete', 'Api\TournamentController::delete/$1');
    });

    $routes->group('brackets', static function ($routes) {
        $routes->post('save-list', 'Api\BracketsController::createBrackets');
        $routes->put('update/(:num)', 'Api\BracketsController::updateBracket/$1');
        $routes->delete('delete/(:num)', 'Api\BracketsController::deleteBracket/$1');
    });
    
    $routes->group('participants', static function ($routes) {
        $routes->get('/', 'Api\ParticipantsController::getParticipants');
        $routes->post('new', 'Api\ParticipantsController::addParticipant');
        $routes->post('update/(:num)', 'Api\ParticipantsController::updateParticipant/$1');
    });

});

/** Shield routs for authentication */
service('auth')->routes($routes);