<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('auth/register', 'Auth\RegisterController@store');
Route::post('auth/login', 'Auth\AuthController@login');
Route::post('auth/forgot', 'Auth\PasswordResetController@forgot');
Route::post('auth/reset/{token}', 'Auth\PasswordResetController@reset');
Route::get('companies', 'CompanyController@index');
Route::post('tasks', 'TaskController@store')->name('tasks.book');

Route::group(['middleware' => 'auth:api'], function () {
    Route::group(['prefix' => 'auth', 'namespace' => 'Auth'], function () {
        Route::post('logout', 'AuthController@logout');
        Route::post('refresh', 'AuthController@refresh');
        Route::get('me', 'AuthController@me');
        Route::patch('me/company', 'AuthController@company');
        Route::patch('me', 'AuthController@update');
        Route::patch('password', 'AuthController@password');
        Route::resource('roles', 'RoleController');
    });

    Route::group(['prefix' => 'companies', 'namespace' => 'Company'], function () {
        Route::get('subscriptions', 'SubscriptionController@index')->name('subscriptions.index');
        Route::post('subscriptions', 'SubscriptionController@store')->name('subscriptions.store');
        Route::patch('subscriptions', 'SubscriptionController@update')->name('subscriptions.update');
        Route::get('tasks/search', 'TaskController@search')->name('tasks.search');

        Route::resources([
            'users' => 'UserController',
            'tasks' => 'TaskController',
        ]);

        Route::get('drivers', 'UserController@drivers')->name('users.drivers');
        Route::get('users/{user}/tasks', 'UserController@tasks')->name('users.tasks');
        Route::post('users/{user}/roles', 'UserController@changeRoles')->name('users.roles.change');
        Route::post('messages', 'MessageController@store')->name('messages.store');
    });

    Route::group(['prefix' => 'drivers', 'namespace' => 'Driver'], function () {
        Route::post('tasks/{task}/cancellations', 'TaskController@cancel')->name('task.cancel');
        Route::get('tasks/details', 'TaskDetailController@index')->name('taskdetails.index');
        Route::patch('tasks/details/{detail}', 'TaskDetailController@complete')->name('taskdetails.complete');
    });
});
