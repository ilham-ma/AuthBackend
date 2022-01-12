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

Route::middleware('auth:sanctum')->group(function(){
    Route::delete("logout", [UserController::class, 'logout']);
    Route::get("profile", [UserController::class, 'getProfile']);
    Route::get("profile/{id}", [UserController::class, 'getOnceProfile']);
    Route::post("profile/update", [UserController::class, 'updateProfile']);
    Route::post("profile/change-password", [UserController::class, 'updatePassword']);
    Route::post("profile/change-photo", [UserController::class, 'updatePhoto']);

});

Route::post("login", [UserController::class, 'login']);
Route::post("register", [UserController::class, 'register']);
