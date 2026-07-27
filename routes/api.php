<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\FavoriteController;
use App\Http\Controllers\Api\V1\GenreController;
use App\Http\Controllers\Api\V1\MovieController;
use App\Http\Controllers\Api\V1\ReviewController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register'])->middleware('throttle:5,1');
        Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1');

        Route::middleware('auth:api')->group(function () {
            Route::post('refresh', [AuthController::class, 'refresh']);
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('me', [AuthController::class, 'me']);
        });
    });

    Route::get('genres', [GenreController::class, 'index']);

    Route::get('movies', [MovieController::class, 'index']);
    Route::get('movies/{slug}', [MovieController::class, 'show']);
    Route::get('movies/{movie}/reviews', [ReviewController::class, 'index']);

    Route::middleware('auth:api')->group(function () {
        Route::post('movies', [MovieController::class, 'store']);
        Route::put('movies/{movie}', [MovieController::class, 'update']);
        Route::delete('movies/{movie}', [MovieController::class, 'destroy']);

        Route::post('movies/{movie}/reviews', [ReviewController::class, 'store'])->middleware('throttle:10,1');
        Route::put('reviews/{review}', [ReviewController::class, 'update']);
        Route::delete('reviews/{review}', [ReviewController::class, 'destroy']);

        Route::post('movies/{movie}/favorite', [FavoriteController::class, 'store']);
        Route::delete('movies/{movie}/favorite', [FavoriteController::class, 'destroy']);
        Route::get('favorites', [FavoriteController::class, 'index']);
    });
});
