<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CurrencyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'throttle:currency-api'])->group(function () {
    Route::get('/auth/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::middleware('throttle:currency-convert')->group(function () {
        Route::post('/currency/convert', [CurrencyController::class, 'convert']);
    });
    
    Route::get('/currency', [CurrencyController::class, 'currencies']);
});
