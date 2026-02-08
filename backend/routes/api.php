<?php

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
    Route::get('/', [TarotCardController::class, 'index']);
    Route::get('/{id}', [TarotCardController::class, 'show']);
});


