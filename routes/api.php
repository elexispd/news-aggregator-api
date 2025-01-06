<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\UserPreferenceController;
use App\Http\Controllers\SourceController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/articles', [ArticleController::class, 'index']);
    Route::get('/articles/{article}', [ArticleController::class, 'show']);
    Route::get('/articles/search', [ArticleController::class, 'search']);

    Route::get('/fetchArticle', [ArticleController::class, 'fetchArticlesFromAPI'])->middleware('throttle:30,1');;

    Route::get('/preferences', [UserPreferenceController::class, 'index']);
    Route::post('/preferences', [UserPreferenceController::class, 'store']);
    Route::get('/personalized-feed', [UserPreferenceController::class, 'personalizedFeed']);

    Route::get('/sources', [SourceController::class, 'index']);
    Route::post('/sources', [SourceController::class, 'store']);
});


