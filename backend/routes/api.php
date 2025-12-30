<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\MessageController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware(['auth:api', 'verified'])->group(function () {

    Route::get('/me', [AuthController::class, 'me']);

    Route::apiResource('trips', TripController::class);
    Route::apiResource('packages', PackageController::class);

    Route::post('/payments', [PaymentController::class, 'pay']);

    Route::post('/ratings', [RatingController::class, 'store']);

    Route::get('/messages/{userId}', [MessageController::class, 'index']);
    Route::post('/messages', [MessageController::class, 'store']);
});
