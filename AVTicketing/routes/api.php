<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::group(['middleware' => ['jwt.auth']], function(){

    //Can only get all tickets if user can read all tickets
    Route::group(['middleware' => ['can:read-all-tickets']], function () {
        Route::get('getUnassignedTickets', 'TicketController@getUnassignedTickets');
        Route::get('getSoonTickets', 'TicketController@getSoonTickets');
    });
	
	Route::post('uploadSchedule', 'ScheduleController@uploadSchedule');
	Route::get('exportSchedule', 'ScheduleController@exportSchedule');

	//Requires Admin group
	Route::group(['middleware' => ['can:read-users']], function () {
		Route::get('roles', 'UserController@getRoleEnum');
		Route::get('user/roles', 'UserController@getRoles');
		Route::get('user/getSettings', 'UserController@getSettings');
		Route::get('user/{id}', 'UserController@getUserData');
		Route::get('user/{id}/schedule/{startDate}', 'ScheduleController@getScheduleFromAPI');
		Route::get('searchMembers/{searchText}', 'UserController@searchAvMembers');
	});
	Route::group(['middleware' => ['can:write-users']], function () {
		Route::post('user/{userID}/roles/{roleID}', 'UserController@addRole');
		Route::post('user/{userID}/tickets/{roleID}', 'TicketController@addUser');
		Route::delete('user/{userID}/roles/{roleID}', 'UserController@removeRole');
		Route::delete('user/{userID}/tickets/{ticketID}', 'TicketController@removeUser');
		Route::post('register', 'AuthenticateController@register');
		Route::get('syncWithSlack', 'UserController@syncWithSlack');
	});
	Route::post('user/updateSettings', 'UserController@updateSettings');
	Route::post('user/update', 'UserController@updateUserInfo');


    //Requires permission - either being assigned or admin
	Route::group(['middleware' => ['can:read-own-tickets']], function () {
		Route::get('ticket/{id}', 'TicketController@getTicketData')->middleware('can:view,id');
	});
	Route::group(['middleware' => ['can:write-own-tickets']], function () {
		Route::post('ticket/{ticket}/updateAttribute', 'TicketController@updateAttribute')->middleware('can:update,ticket');
		Route::post('ticket/{ticket}/status/{statusID}', 'TicketController@setStatus')->middleware('can:update,ticket');
		Route::post('ticket/{ticket}/user/status', 'TicketController@setUserStatus')->middleware('can:update,ticket');
		Route::post('ticket/{ticket}/message', 'TicketController@sendNewMessage')->middleware('can:update,ticket');
		Route::post('ticket/{ticket}/updateFreshdesk', 'TicketController@updateFreshdesk')->middleware('can:update,ticket');
		Route::post('ticket/{ticket}/toIT', 'TicketController@sendToIT')->middleware('can:update,ticket');
	});
	Route::get('getStatusArray', 'TicketController@getStatusArray');
	Route::get('getMyTickets', 'TicketController@getCurrentUserTickets');

	Route::post('password/change', 'UserController@updatePassword');
});


//Uses same Authorization: Bearer header, but references API_TOKEN which is permanent
//
Route::group(['middleware'=>['auth:api']], function (){
    Route::group(['prefix' => 'tickets'], function () {

        Route::post('new', 'TicketController@newWebHookTicket');
        Route::post('{freshdeskID}/update', 'TicketController@updateWebHookTicket');
        Route::post('{freshdeskID}/syncMessages', 'TicketController@getNewMessages');
    });
    Route::post('apiAI', 'BotManController@handle');
});


Route::post('password/sendResetEmail', 'Auth\ForgotPasswordController@sendResetLinkEmail');
Route::post('password/validateToken', 'Auth\ResetPasswordController@validateToken');
Route::post('password/reset', 'Auth\ResetPasswordController@reset');
Route::post('authenticate', 'AuthenticateController@authenticate');
Route::get('test', 'UserController@test');