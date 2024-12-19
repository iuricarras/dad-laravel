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
use App\Models\User;
use App\Models\Game;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/games/topSinglePlayer', [GameController::class, 'topSinglePlayerGames']);
Route::get('/games/topMultiplayer', [GameController::class, 'topMultiplayerGames']);

Route::get('/boards', [BoardController::class, 'fetchBoards']);
Route::get('/boards/all', [BoardController::class, 'index']);

Route::get('/statistics', [StatisticsController::class, 'index']);
Route::get('/statistics/games-per-month', [StatisticsController::class, 'gamesPerMonth']);
Route::get('/statistics/games-per-week', [StatisticsController::class, 'gamesPerWeek']);
Route::get('/statistics/purchases-per-month', [StatisticsController::class, 'purchasesPerMonth']);
Route::get('/statistics/purchases-per-week', [StatisticsController::class, 'purchasesPerWeek']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refreshtoken', [AuthController::class, 'refreshToken']);
    Route::get('/users/me', [UserController::class , 'showMe']);

    Route::get('/scoreboard', [GameController::class, 'personalScoreboard'])->can('player', User::class);

    Route::get('/games-history', [GameController::class, 'gameHistory'])->can('player', User::class);
    Route::post('/games', [GameController::class, 'store'])->can('player', User::class);
    Route::get('/games', [GameController::class, 'index'])->can('viewAny', User::class);
    Route::put('/games/{game}', [GameController::class, 'update'])->can('player', User::class);
    Route::get('/games/scoreboard/{user}', [GameController::class, 'personalScoreboard'])->can('view', 'user');
    Route::post('/multiplayerGamesPlayed', [MultiplayerGamePlayedController::class, 'store'])->can('player', User::class);

    //Route::get('/transactions', [TransactionController::class, 'index']);
    Route::get('/transactions', [TransactionController::class, 'getTransactions'])->can('player', User::class);
    Route::post('/transactions', [TransactionController::class, 'store'])->can('player', User::class);
    Route::get('/users/{user}/transactions', [TransactionController::class, 'show'])->can('viewAny', User::class);
    //Users
    Route::get('/users', [UserController::class, 'index'])->can('viewAny', User::class);
    Route::get('/users/{user}', [UserController::class, 'show'])->can('view', 'user');
    Route::get('/users/{user}/games', [UserController::class, 'games'])->can('view', 'user');
    Route::get('/users/{user}/singleplayerGames', [UserController::class, 'games'])->can('view', 'user');
    Route::get('/users/{user}/multiplayerGames', [UserController::class, 'multiplayerGames'])->can('view', 'user');

    //Route::match(['put', 'patch'], '/users/{user}', [UserController::class, 'update']);
    Route::put('/users/{user}', [UserController::class, 'update'])->can('update', 'user');
    Route::patch('/users/{user}', [UserController::class, 'updateFoto'])->can('view', 'user');
    Route::post('/users/{user}/delete', [UserController::class, 'checkBeforeDelete'])->can('delete', 'user');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->can('delete', 'user');
    Route::post('/users/admin', [UserController::class, 'createAdmin'])->can('viewAny', User::class);

    //Statistics
    Route::get('/statistics/purchases-by-player', [StatisticsController::class, 'purchasesByPlayer'])->can('viewAny', User::class);
    Route::get('/statistics/payment-types', [StatisticsController::class, 'paymentTypes'])->can('viewAny', User::class);
    Route::get('/statistics/payment-value', [StatisticsController::class, 'paymentValuesByMonth'])->can('viewAny', User::class);
});
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/users', [UserController::class, 'createUser']);






