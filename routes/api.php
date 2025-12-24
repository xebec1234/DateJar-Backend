<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SavingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PartnerController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/google-login', [AuthController::class, 'googleLogin']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    //partner
    Route::get('/partners', [PartnerController::class, 'index']);  // view current partner
    Route::post('/partners', [PartnerController::class, 'store']); // connect partner
    Route::delete('/partners/{id}', [PartnerController::class, 'destroy']);

    //savings
        Route::apiResource('savings', SavingController::class);
});
