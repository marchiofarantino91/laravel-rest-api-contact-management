<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\ApiAuthMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::group(['prefix' => 'users'], function () {
    Route::post('register', [UserController::class, 'register']);
    Route::post('login', [UserController::class, 'login']);
});
Route::middleware(ApiAuthMiddleware::class)->group(function () {
    Route::group(['prefix' => 'users'], function () {
        Route::get('profile', [UserController::class, 'profile']);
        Route::post('update', [UserController::class, 'update']);
        Route::get('logout', [UserController::class, 'logout']);
    });
    Route::group(['prefix' => 'contacts'], function () {
        Route::get('/', [ContactController::class, 'search']);
        Route::post('createStored', [ContactController::class, 'createStored']);
        Route::post('updateStored', [ContactController::class, 'updateStored']);
        Route::post('deleteStored', [ContactController::class, 'deleteStored']);
        Route::get('{id}', [ContactController::class, 'get'])->where('id', '[0-9]+');

        Route::group(['prefix' => 'address'], function () {
            Route::get('list', [AddressController::class, 'list']);
            Route::get('detail', [AddressController::class, 'getDetail']);
            Route::post('createStored', [AddressController::class, 'createStored']);
            Route::post('deleteStored', [AddressController::class, 'deleteStored']);
            Route::post('updateStored', [AddressController::class, 'updateStored']);
        });
    });
});
