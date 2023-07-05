<?php

use App\Http\Controllers\GameMatchController;
use App\Http\Controllers\QuestionsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::post('/start', '');

Route::controller(QuestionsController::class)->group(function () {
    Route::get('/question/{matchId}', 'showQuestion');
    Route::post('/answer/{questionId}', 'answerQuestion');
});

Route::controller(GameMatchController::class)->group(function () {
    Route::post('/start', 'startGame');
});


// Route::post('/answer', '');
