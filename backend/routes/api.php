<?php

use App\Http\Controllers\Api\SpreadReadingController;
use App\Http\Controllers\Api\SuitController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\TarotCardController;
use Illuminate\Support\Facades\Route;

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

Route::prefix('spread-readings')->group(function () {
    Route::get('/', [SpreadReadingController::class, 'index']);
    Route::post('/', [SpreadReadingController::class, 'store']);
    Route::get('/{id}', [SpreadReadingController::class, 'show']);
    Route::post('/{id}/cards', [SpreadReadingController::class, 'addCard']);
});


