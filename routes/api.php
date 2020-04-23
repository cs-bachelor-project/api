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

Route::post('/register', 'RegisterController@store');
Route::post('auth/login', 'AuthController@login');

Route::group(['middleware' => 'auth:api'], function () {
    Route::group(['prefix' => 'auth'], function () {
        Route::post('logout', 'AuthController@logout');
        Route::post('refresh', 'AuthController@refresh');
        Route::get('me', 'AuthController@me');
        Route::patch('me/company', 'AuthController@company');
        Route::patch('me', 'AuthController@update');
        Route::patch('password', 'AuthController@password');
    });

    Route::get('tasks/search', 'TaskController@search')->name('tasks.search');

    Route::resources([
        'companies' => 'CompanyController',
        'users' => 'UserController',
        'tasks' => 'TaskController',
        'roles' => 'RoleController',
    ]);

    Route::get('drivers', 'UserController@drivers')->name('users.drivers');
    Route::get('users/{user}/tasks', 'UserController@tasks')->name('users.tasks');
    Route::post('users/{user}/roles', 'UserController@changeRoles')->name('users.roles.change');
});
