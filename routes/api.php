<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\EvaluationController;
use App\Http\Controllers\Api\V1\FlagController;
use App\Http\Controllers\Api\V1\GroupController;
use App\Http\Controllers\Api\V1\TargetingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

Route::prefix('v1')->group(function () {
    // Public auth routes
    Route::post('/auth/register', [AuthController::class, 'register'])
        ->middleware('throttle:60,1');
    
    Route::post('/auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:60,1');

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // Auth routes
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        // Flag routes
        Route::apiResource('flags', FlagController::class);
        Route::patch('/flags/{id}/toggle', [FlagController::class, 'toggle']);

        // Group routes
        Route::apiResource('groups', GroupController::class);

        // Targeting routes
        Route::post('/targeting', [TargetingController::class, 'store']);
        Route::get('/flags/{flagId}/targeting', [TargetingController::class, 'index']);
        Route::put('/flags/{flagId}/targeting', [TargetingController::class, 'replace']);
        Route::delete('/flags/{flagId}/targeting/{groupId}', [TargetingController::class, 'destroy']);

        // Evaluation routes (high rate limit for production use)
        Route::middleware('throttle:1000,1')->group(function () {
            Route::post('/evaluate', [EvaluationController::class, 'evaluate']);
            Route::post('/evaluate/batch', [EvaluationController::class, 'evaluateBatch']);
        });
    });
});
