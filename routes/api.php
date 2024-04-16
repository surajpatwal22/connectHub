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

Route::middleware('auth:sanctum')->group(function () {
    Route::post('change_password', [UserController::class,'changePassword'])->name('change_password');
    Route::get('get_profile', [UserController::class,'getProfile'])->name('get_profile');
    Route::post('update_profile', [UserController::class,'updateProfile'])->name('update_profile');
    Route::post('logout', [UserController::class,'logout'])->name('logout');

    Route::middleware('admin')->group(function () {
        
       
    });
});

Route::post('signup', [UserController::class,'signup'])->name('signup');
Route::post('signin', [UserController::class,'signin'])->name('signin');
Route::post('forgot_password', [UserController::class,'forgotPassword'])->name('forgot_password');
Route::post('verify_otp', [UserController::class,'verifyOtp'])->name('verify_otp');
