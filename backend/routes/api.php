<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AnalysisController;
use App\Http\Controllers\Api\SpreadReadingController;
use App\Http\Controllers\Api\SuitController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\TarotCardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// 認證相關（公開）
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/google', [AuthController::class, 'redirectToGoogle']);
    Route::get('/google/callback', [AuthController::class, 'handleGoogleCallback']);
});

// 需登入的認證
Route::prefix('auth')->middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});

// 塔羅牌、標籤、牌組（公開讀取）
Route::prefix('tarot-cards')->group(function () {
    Route::get('/random', [TarotCardController::class, 'random']);
    Route::get('/', [TarotCardController::class, 'index']);
    Route::get('/{id}', [TarotCardController::class, 'show']);
    
    // 標籤管理相關路由
    Route::get('/{id}/tags/active', [TarotCardController::class, 'getActiveTags']);
    Route::get('/{id}/tags/default', [TarotCardController::class, 'getDefaultTags']);
    Route::get('/{id}/tags/custom', [TarotCardController::class, 'getCustomTags']);
    Route::post('/{id}/tags/custom', [TarotCardController::class, 'setCustomTags']);
    Route::delete('/{id}/tags/custom', [TarotCardController::class, 'deleteCustomTags']);
    Route::post('/{id}/tags/reset', [TarotCardController::class, 'resetToDefaultTags']);
});

Route::prefix('tags')->group(function () {
    Route::get('/', [TagController::class, 'index']);
    Route::post('/', [TagController::class, 'store']);
    Route::put('/{id}', [TagController::class, 'update']);
    Route::delete('/{id}', [TagController::class, 'destroy']);
});

Route::prefix('suits')->group(function () {
    Route::get('/', [SuitController::class, 'index']);
});

// 牌陣、分析（需登入）
Route::prefix('spread-readings')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [SpreadReadingController::class, 'index']);
    Route::post('/', [SpreadReadingController::class, 'store']);
    Route::get('/{id}', [SpreadReadingController::class, 'show']);
    Route::post('/{id}/cards', [SpreadReadingController::class, 'addCard']);
    Route::post('/{id}/cards/positions/{position}/tags', [SpreadReadingController::class, 'toggleTag']);
});

Route::prefix('analysis')->middleware('auth:sanctum')->group(function () {
    Route::get('/top-keywords', [AnalysisController::class, 'topKeywords']);
});


