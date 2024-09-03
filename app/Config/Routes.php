<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/gallery', 'Home::gallery');
$routes->get('/participants', 'Home::participants');
$routes->get('/brackets', 'Home::brackets');
// $routes->match(['get', 'post'], '/player/(:any)', 'RenderAudioController::index/$1');

$routes->get('profile', 'ProfileController::index');
$routes->get('profile/change-email', 'ProfileController::changeEmail', ['as' => 'profile.change-email']);
$routes->post('profile/update-email', 'ProfileController::updateEmail');
$routes->get('profile/change-password', 'ProfileController::changePassword', ['as' => 'profile.change-password']);
$routes->post('profile/update-password', 'ProfileController::updatePassword');


$routes->post('login', '\App\Controllers\Auth\LoginController::loginAction');
$routes->get('auth/google', 'GoogleAuthController::login');
$routes->get('auth/google/callback', 'GoogleAuthController::callback');

$routes->get('gallery/(:num)/view', 'TournamentController::view/$1');
$routes->get('gallery/export', 'Home::export');
$routes->group('tournaments', static function ($routes) {
    $routes->get('/', 'TournamentController::index');
    $routes->get('create', 'TournamentController::create');
    $routes->get('(:num)/view', 'TournamentController::view/$1');
    $routes->get('shared/(:segment)', 'TournamentController::viewShared/$1');
    $routes->get('export', 'TournamentController::export');
    $routes->get('apply', 'TournamentController::apply');
    $routes->post('save-apply', 'TournamentController::saveApply');
});

$routes->post('consent', 'CookieConsent::consent');

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
        $routes->get('clear', 'Api\ParticipantsController::clearParticipants');
        $routes->post('deletes', 'Api\ParticipantsController::deleteParticipants');
    });

    $routes->group('tournaments', static function ($routes) {
        $routes->post('save', 'Api\TournamentController::save');
        $routes->get('(:num)/brackets', 'Api\BracketsController::getBrackets/$1');
        $routes->post('(:num)/update', 'Api\TournamentController::update/$1');
        $routes->post('upload', 'Api\TournamentController::upload');
        $routes->get('(:num)/fetch-settings', 'Api\TournamentController::getSettings/$1');
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
        $routes->post('get-list', 'Api\TournamentController::fetch');
        $routes->post('reuse-participants', 'Api\TournamentController::reuseParticipants');
        $routes->get('(:num)/get-participants', 'Api\TournamentController::getParticipants/$1');
    });

    $routes->group('notifications', static function ($routes) {
        $routes->put('mark-as-read/(:num)', 'Api\NotificationsController::markAsRead/$1');
        $routes->delete('delete/(:num)', 'Api\NotificationsController::delete/$1');
    });

    $routes->group('usersettings', static function ($routes) {
        $routes->post('list', 'Api\UserSettingsController::index');
        $routes->post('save', 'Api\UserSettingsController::save');
    });
});

/** API to fetch the data of shared tournaments */
$routes->group('api/shared', static function ($routes) {
    $routes->group('tournaments', static function ($routes) {
        $routes->get('(:num)/brackets', 'Api\BracketsController::getBrackets/$1');
        $routes->post('(:num)/update', 'Api\TournamentController::update/$1');
        $routes->get('(:num)/delete', 'Api\TournamentController::delete/$1');
        $routes->get('(:num)/get-participants', 'Api\TournamentController::getParticipants/$1');
    });

    $routes->group('brackets', static function ($routes) {
        $routes->post('save-list', 'Api\BracketsController::createBrackets');
        $routes->put('update/(:num)', 'Api\BracketsController::updateBracket/$1');
        $routes->delete('delete/(:num)', 'Api\BracketsController::deleteBracket/$1');
    });
    
    $routes->group('participants', static function ($routes) {
        $routes->post('new', 'Api\ParticipantsController::addParticipant');
        $routes->post('update/(:num)', 'Api\ParticipantsController::updateParticipant/$1');
    });

});

/** Shield routs for authentication */
service('auth')->routes($routes);