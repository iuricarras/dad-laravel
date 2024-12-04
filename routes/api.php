<?php

use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\BoardController;
use App\Http\Controllers\api\GameController;
use App\Http\Controllers\api\MultiplayerGamePlayedController;
use App\Http\Controllers\api\StatisticsController;
use App\Http\Controllers\api\TransactionController;
use App\Http\Controllers\api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::get('/games/topSinglePlayer', [GameController::class, 'topSinglePlayerGames']);
Route::get('/games/topMultiplayer', [GameController::class, 'topMultiplayerGames']);
Route::get('/games/topSinglePlayerMinTurns', [GameController::class, 'topSinglePlayerGamesMinTurns']);



Route::get('/boards', [BoardController::class, 'fetchBoards']);
Route::get('/boards/all', [BoardController::class, 'index']);

Route::post('/transactions', [TransactionController::class, 'store']);

Route::get('/transactions/{transaction}', [TransactionController::class, 'show']);

Route::get('/statistics', [StatisticsController::class, 'index']);
Route::get('/statistics/games-per-month', [StatisticsController::class, 'gamesPerMonth']);
Route::get('/statistics/purchases-per-month', [StatisticsController::class, 'purchasesPerMonth']);
Route::get('/statistics/games-per-week', [StatisticsController::class, 'gamesPerWeek']);
Route::get('/statistics/purchases-per-week', [StatisticsController::class, 'purchasesPerWeek']);
Route::get('/statistics/purchases-by-player', [StatisticsController::class, 'purchasesByPlayer']);




Route::get('/games', [GameController::class, 'index']);
    Route::get('/games/{game}', [GameController::class, 'show']);


Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refreshtoken', [AuthController::class, 'refreshToken']);
    Route::get('/users/me', [UserController::class , 'showMe']);
    Route::get('/scoreboard', [GameController::class, 'personalScoreboard']);
   
    Route::get('/games-history', [GameController::class, 'gameHistory']);
    Route::post('/games', [GameController::class, 'store']);
    Route::put('/games/{game}', [GameController::class, 'update']);
    Route::get('/games/scoreboard/{user}', [GameController::class, 'personalScoreboard']);
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::get('/users/{user}/transactions', [UserController::class, 'transactions']);
    Route::get('/users/{user}/games', [UserController::class, 'games']);
    Route::get('/users/{user}/singleplayerGames', [UserController::class, 'games']);
    Route::get('/users/{user}/multiplayerGames', [UserController::class, 'multiplayerGames']);
    Route::put('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);



    Route::post('/multiplayerGamesPlayed', [MultiplayerGamePlayedController::class, 'store']);
});
Route::post('/auth/login', [AuthController::class, 'login']);






