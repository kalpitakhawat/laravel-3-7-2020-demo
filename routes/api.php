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

Route::post('/signup', 'AuthController@create')->name('signup');
Route::post('/login', 'AuthController@login')->name('login');
Route::middleware(['auth:api'])->group(function () {
    Route::get('logout', 'AuthController@logout')->name("logout");
    Route::get('/user', 'UserController@getUser')->name('getUser');
    Route::middleware(['check.client'])->group(function () {
        Route::prefix('post')->group(function () {
            Route::post('/create', 'ProjectController@create');
        });
    });
    Route::middleware(['check.designer'])->group(function () {
        Route::post('/bid', 'ProjectController@bid');
        Route::get('/post', 'ProjectController@index');
    });
});
