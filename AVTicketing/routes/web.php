<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

/*Route::get('/', function () {
    return view('welcome');
});*/

Route::get('/{all?}', function () {
    return view('index');
});

/** @var Dingo\Api\Routing\Router $api */
/*$api = app('Dingo\Api\Routing\Router');
$api->version('v1', function (\Dingo\Api\Routing\Router $api){
	$api->get('users', 'App\Http\Controllers\AuthenticateController@login');
});
$api->get('newPeriod', 'App\Http\Controllers\ScheduleController@insertPeriod');*/
