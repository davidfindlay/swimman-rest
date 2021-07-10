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
    $router->post('reset/{emailAddress}', ['uses' => 'AuthController@resetPassword']);
    $router->post('resetToken/{token}', ['uses' => 'AuthController@verifyResetPasswordToken']);
    $router->post('resetPassword/{token}', ['uses' => 'AuthController@resetPasswordToken']);
    $router->post('refresh', ['uses' => 'AuthController@refresh']);
    $router->get('generateRandomPassword', ['uses' => 'AuthController@generateSimplePassword']);
    $router->post('changePassword/{userId}', ['middleware' => 'auth:api', 'uses' => 'AuthController@changePassword']);

	$router->get('meets',  ['uses' => 'MeetController@showCurrentMeets']);
    $router->get('meets/all',  ['uses' => 'MeetController@getAllMeets']);
	$router->get('meets/{id}', ['uses' => 'MeetController@showOneMeet']);
	$router->post('meets/{id}/access', ['middleware' => 'auth:api', 'uses' => 'MeetController@addAccess']);
    $router->delete('meets/{id}/access/{memberId}', ['middleware' => 'auth:api', 'uses' => 'MeetController@removeAccess']);
	$router->get('meets/{id}/events', ['middleware' => 'auth:api', 'uses' => 'MeetController@getEvents']);
    $router->post('meets/{id}/events/{eventId}/configure', ['middleware' => 'auth:api', 'uses' => 'MeetController@updateEvent']);
	$router->post('meets', ['middleware' => 'auth:api', 'uses' => 'MeetController@createMeet']);
	$router->put('meets/{id}', ['middleware' => 'auth:api', 'uses' => 'MeetController@updateMeet']);
    $router->post('meets_publish/{id}', ['middleware' => 'auth:api', 'uses' => 'MeetController@publishMeet']);
    $router->post('meets_payment_method/{id}', ['middleware' => 'auth:api', 'uses' => 'MeetController@addPaymentMethod']);
    $router->post('meets_payment_method_remove/{id}', ['middleware' => 'auth:api', 'uses' => 'MeetController@removePaymentMethod']);

	$router->get('clubs', ['uses' => 'ClubController@getClubs']);
	$router->get('club/{id}/members', ['middleware' => 'auth:api', 'uses' => 'ClubController@getMembers']);
    $router->get('club/{id}/entries', ['middleware' => 'auth:api', 'uses' => 'ClubController@getEntries']);

    $router->get('club/{id}/relay_teams', ['middleware' => 'auth:api', 'uses' => 'RelayTeamController@getRelayTeams']);

    $router->post('relay', ['middleware' => 'auth:api', 'uses' => 'RelayTeamController@createRelay']);
    $router->put('relay/{id}', ['middleware' => 'auth:api', 'uses' => 'RelayTeamController@editRelay']);
    $router->delete('relay/{id}', ['middleware' => 'auth:api', 'uses' => 'RelayTeamController@deleteRelay']);
    $router->post('relay_payment/{club_id}/{meet_id}', ['middleware' => 'auth:api', 'uses' => 'RelayTeamController@receivePayment']);
    $router->post('relay_payment_guest/{meet_id}', ['uses' => 'RelayTeamController@receiveGuestPayment']);

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
    $router->post('meet_entry/{id}/resendConfirmation', ['middleware' => 'auth:api', 'uses' => 'MeetEntryController@sendConfirmationEmail']);
    $router->post('meet_entry/{id}/paymentLink', ['middleware' => 'auth:api', 'uses' => 'MeetEntryController@sendPaymentEmailMeetEntry']);
    $router->post('pending_entry/{id}/resendConfirmation', ['middleware' => 'auth:api', 'uses' => 'MeetEntryController@sendPendingConfirmationEmail']);
    $router->post('meet_entry/{id}/applyPayment', ['middleware' => 'auth:api', 'uses' => 'MeetEntryController@applyPayment']);
    $router->get('meet_entry_by_code/{id}', ['uses' => 'MeetEntryController@getMeetEntryByCode']);

    $router->get('meet_entries_by_member_number/{number}', ['middleware' => 'auth:api', 'uses' => 'MeetEntryController@getSubmittedEntriesByMemberNumber']);

    $router->get('meet_entries/{meetId}', ['middleware' => 'auth:api', 'uses' => 'MeetEntryController@getSubmittedEntriesByMeet']);
    $router->get('pending_entries/{meetId}', ['middleware' => 'auth:api', 'uses' => 'MeetEntryController@getPendingEntriesByMeet']);
    $router->post('approve_pending/{pendingId}', ['middleware' => 'auth:api', 'uses' => 'MeetEntryController@approve_pending']);

    $router->post('create_payment_entry/{id}', ['uses' => 'PaypalController@createPaymentEntryById']);
    $router->post('create_payment_incomplete/{id}', ['uses' => 'PaypalController@createPaymentIncompleteEntryById']);
    $router->post('create_payment_entry', ['uses' => 'PaypalController@createPaymentEntry']);
    $router->post('finalise_payment', ['uses' => 'PaypalController@finalisePayment']);

    $router->get('users', ['middleware' => 'auth:api', 'uses' => 'UserController@userList']);
    $router->get('users/{userId}', ['middleware' => 'auth:api', 'uses' => 'UserController@getUser']);
    $router->put('users/{userId}', ['middleware' => 'auth:api', 'uses' => 'UserController@update']);
    $router->post('users/register', ['uses' => 'UserController@register']);
    $router->post('users/link_member/{memberNumber}', ['middleware' => 'auth:api', 'uses' => 'UserController@linkMember']);

    $router->post('members/create', ['middleware' => 'auth:api', 'uses' => 'MemberController@createMember']);

    $router->post('merchandise/create', ['middleware' => 'auth:api', 'uses' => 'MeetMerchandiseController@createMerchandiseItem']);
    $router->post('merchandise/{merchandiseId}/addImage', ['middleware' => 'auth:api', 'uses' => 'MeetMerchandiseController@addMerchandiseImage']);
    $router->delete('merchandise/images/{merchandiseImageId}', ['middleware' => 'auth:api', 'uses' => 'MeetMerchandiseController@deleteMerchandiseImage']);

    $router->get('merchandise/{merchandiseId}', ['uses' => 'MeetMerchandiseController@getMerchandiseItem']);
    $router->put('merchandise/{merchandiseId}', ['middleware' => 'auth:api', 'uses' => 'MeetMerchandiseController@updateMerchandiseItem']);

    $router->delete('merchandise/{merchandiseId}', ['middleware' => 'auth:api', 'uses' => 'MeetMerchandiseController@deleteMerchandiseItem']);

    $router->get('meet_entry_orders/{meetId}', ['middleware' => 'auth:api', 'uses' => 'MeetMerchandiseController@getOrders']);

    $router->post('members/search', ['middleware' => 'auth:api', 'uses' => 'MemberController@findMember']);

});
