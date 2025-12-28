<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SavingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\GoalController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/google-login', [AuthController::class, 'googleLogin']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    //partner
    Route::get('/partners/search', [PartnerController::class, 'search']);
    Route::get('/partners', [PartnerController::class, 'index']);  // view current partner
    Route::post('/partners', [PartnerController::class, 'store']); // connect partner
    Route::delete('/partners/{id}', [PartnerController::class, 'destroy']);

    //savings
    Route::apiResource('savings', SavingController::class);

    // goals
    Route::get('/goals', [GoalController::class, 'index']);        // list all goals
    Route::post('/goals', [GoalController::class, 'store']);       // create new goal
    Route::delete('/goals/{id}', [GoalController::class, 'destroy']);
});
