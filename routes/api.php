<?php

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

Route::get('companies', 'CompanyController@index');


Route::group(['prefix' => 'auth', 'namespace' => 'Auth'], function () {
    Route::post('register', 'RegisterController@store');
    Route::post('login', 'AuthController@login');
    Route::post('forgot', 'PasswordResetController@forgot');
    Route::post('reset/{token}', 'PasswordResetController@reset');

    Route::group(['middleware' => 'auth:api'], function () {
        Route::post('logout', 'AuthController@logout');
        Route::post('refresh', 'AuthController@refresh');
        Route::get('me', 'AuthController@me');
        Route::patch('me/company', 'AuthController@company');
        Route::patch('me', 'AuthController@update');
        Route::patch('password', 'AuthController@password');
        Route::get('roles', 'RoleController@index')->name('roles.index');
        Route::post('roles', 'RoleController@store')->name('roles.store');
        Route::get('roles/{role}', 'RoleController@show')->name('roles.show');
    });
});

Route::group(['prefix' => 'companies', 'namespace' => 'User', 'middleware' => 'auth:api'], function () {
    Route::get('users', 'UserController@index')->name('users.index');
    Route::post('users', 'UserController@store')->name('users.store');
    Route::get('users/{user}', 'UserController@show')->name('users.show');
    Route::patch('users/{user}', 'UserController@update')->name('users.update');
    Route::delete('users/{user}', 'UserController@destroy')->name('users.destroy');
    Route::get('drivers', 'UserController@drivers')->name('users.drivers');
    Route::get('users/{user}/tasks', 'UserController@tasks')->name('users.tasks');
    Route::post('users/{user}/roles', 'UserController@changeRoles')->name('users.roles.change');
});

Route::group(['prefix' => 'companies', 'namespace' => 'Subscription', 'middleware' => 'auth:api'], function () {
    Route::get('subscriptions', 'SubscriptionController@index')->name('subscriptions.index');
    Route::post('subscriptions', 'SubscriptionController@store')->name('subscriptions.store');
    Route::patch('subscriptions', 'SubscriptionController@update')->name('subscriptions.update');
});

Route::group(['namespace' => 'Task'], function () {
    Route::post('tasks', 'CustomerTaskController@store')->name('tasks.customer.store');

    Route::group(['prefix' => 'drivers', 'middleware' => 'auth:api'], function () {
        Route::post('tasks/{task}/cancellations', 'DriverTaskController@cancel')->name('task.driver.cancel');
        Route::get('tasks/details', 'DriverTaskController@index')->name('task.driver.index');
        Route::patch('tasks/details/{detail}', 'DriverTaskController@complete')->name('task.driver.complete');
    });

    Route::group(['prefix' => 'companies', 'middleware' => 'auth:api'], function () {
        Route::get('tasks/search', 'CompanyTaskController@search')->name('tasks.company.search');
        Route::get('tasks', 'CompanyTaskController@index')->name('tasks.company.index');
        Route::post('tasks', 'CompanyTaskController@store')->name('tasks.company.store');
        Route::get('tasks/{task}', 'CompanyTaskController@show')->name('tasks.company.show');
        Route::patch('tasks/{task}', 'CompanyTaskController@update')->name('tasks.company.update');
        Route::delete('tasks/{task}', 'CompanyTaskController@destroy')->name('tasks.company.destroy');
    });
});

Route::group(['prefix' => 'companies', 'namespace' => 'Statistics', 'middleware' => 'auth:api'], function () {
    Route::get('statistics/tasks', 'TaskStatisticsController@index')->name('statistics.tasks');
});

Route::group(['prefix' => 'companies', 'namespace' => 'Message', 'middleware' => 'auth:api'], function () {
    Route::post('messages', 'MessageController@store')->name('messages.store');
});
