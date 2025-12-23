<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SavingController;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('savings', SavingController::class);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
