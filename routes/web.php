<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'api'], function () use ($router) {

	$router->post('login', ['uses' => 'AuthController@login']);
    $router->post('logout', ['uses' => 'AuthController@logout']);
    $router->post('refresh', ['uses' => 'AuthController@refresh']);

	$router->get('meets',  ['uses' => 'MeetController@showCurrentMeets']);
	$router->get('meets/{id}', ['uses' => 'MeetController@showOneMeet']);
	$router->get('meets/{id}/events', ['uses' => 'MeetController@getEvents']);

	$router->get('clubs', ['uses' => 'ClubController@getClubs']);

	$router->get('member/{id}', ['middleware' => 'auth:api', 'uses' => 'MemberController@showOneMember']);

	$router->get('sports_tg_members', ['uses' => 'SportsTGController@getMembers']);

});
