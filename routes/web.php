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
    $router->get('meets/all',  ['uses' => 'MeetController@getAllMeets']);
	$router->get('meets/{id}', ['uses' => 'MeetController@showOneMeet']);
	$router->get('meets/{id}/events', ['uses' => 'MeetController@getEvents']);

	$router->get('clubs', ['uses' => 'ClubController@getClubs']);
	$router->get('club/{id}/members', ['middleware' => 'auth:api', 'uses' => 'ClubController@getMembers']);

	$router->get('member/{id}', ['middleware' => 'auth:api', 'uses' => 'MemberController@showOneMember']);
    $router->get('member_by_number/{id}', ['middleware' => 'auth:api', 'uses' => 'MemberController@showOneMemberByNumber']);

	$router->get('sports_tg_members', ['uses' => 'SportsTGController@getMembers']);

	$router->post('entry_incomplete', ['uses' => 'MeetEntryController@createIncompleteEntry']);
    $router->post('entry_finalise/{code}', ['uses' => 'MeetEntryController@finaliseIncompleteEntry']);
    $router->put('entry_finalise/{code}', ['uses' => 'MeetEntryController@finaliseIncompleteEntry']);
    $router->put('entry_incomplete/{code}', ['uses' => 'MeetEntryController@updateIncompleteEntry']);
    $router->post('entry_incomplete_processed/{id}', ['uses' => 'MeetEntryController@processed_pending']);

    $router->delete('entry_incomplete/{id}', ['middleware' => 'auth:api', 'uses' => 'MeetEntryController@deleteIncompleteEntry']);
    $router->get('entry_incomplete/{code}', ['uses' => 'MeetEntryController@getIncompleteEntry']);
    $router->get('entry_incomplete', ['middleware' => 'auth:api', 'uses' => 'MeetEntryController@index']);

    $router->get('meet_entry_status_list', ['uses' => 'MeetEntryStatusController@getAll']);

    $router->get('meet_entries', ['middleware' => 'auth:api', 'uses' => 'MeetEntryController@getSubmittedEntries']);
    $router->get('meet_entry/{id}', ['middleware' => 'auth:api', 'uses' => 'MeetEntryController@getMeetEntry']);
    $router->get('meet_entry_by_code/{id}', ['uses' => 'MeetEntryController@getMeetEntryByCode']);

    $router->get('meet_entries_by_member_number/{number}', ['middleware' => 'auth:api', 'uses' => 'MeetEntryController@getSubmittedEntriesByMemberNumber']);

    $router->get('meet_entries/{meetId}', ['middleware' => 'auth:api', 'uses' => 'MeetEntryController@getSubmittedEntriesByMeet']);
    $router->get('pending_entries/{meetId}', ['middleware' => 'auth:api', 'uses' => 'MeetEntryController@getPendingEntriesByMeet']);
    $router->post('approve_pending/{pendingId}', ['middleware' => 'auth:api', 'uses' => 'MeetEntryController@approve_pending']);

    $router->post('create_payment_entry/{id}', ['uses' => 'PaypalController@createPaymentEntryById']);
    $router->post('create_payment_incomplete/{id}', ['uses' => 'PaypalController@createPaymentIncompleteEntryById']);
    $router->post('create_payment_entry', ['uses' => 'PaypalController@createPaymentEntry']);
    $router->post('finalise_payment', ['uses' => 'PaypalController@finalisePayment']);

    $router->post('users/register', ['uses' => 'UserController@register']);
    $router->post('users/link_member/{memberNumber}', ['middleware' => 'auth:api', 'uses' => 'UserController@linkMember']);

    $router->post('members/create', ['middleware' => 'auth:api', 'uses' => 'MemberController@createMember']);

    $router->post('merchandise/create', ['middleware' => 'auth:api', 'uses' => 'MeetMerchandiseController@createMerchandiseItem']);
    $router->post('merchandise/{merchandiseId}/addImage', ['middleware' => 'auth:api', 'uses' => 'MeetMerchandiseController@addMerchandiseImage']);

    $router->get('merchandise/{merchandiseId}', ['uses' => 'MeetMerchandiseController@getMerchandiseItem']);
    $router->put('merchandise/{merchandiseId}', ['middleware' => 'auth:api', 'uses' => 'MeetMerchandiseController@updateMerchandiseItem']);

    $router->delete('merchandise/{merchandiseId}', ['middleware' => 'auth:api', 'uses' => 'MeetMerchandiseController@deleteMerchandiseItem']);

});
