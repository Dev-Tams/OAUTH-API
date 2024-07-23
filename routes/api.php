<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PasswordController;
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

Route::prefix("v1")->group(function () {
    Route::post("register", [AuthController::class, "register"]);
    Route::post("user", [AuthController::class, "store"]);
    Route::post("passwords/reset/link", [PasswordController::class, "passlink"]);
    Route::post('passwords/resets', [PasswordController::class, 'resetPassword']);

    Route::middleware('auth:sanctum')->group(function (){
        Route::delete('logout', [AuthController::class, "logout"]);
        Route::put('passwords/update', [PasswordController::class, "updatePassword"]);
      
    });
});
