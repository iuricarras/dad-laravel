<?php

use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\GameController;
use App\Http\Controllers\api\MultiplayerGamePlayedController;
use App\Http\Controllers\api\TransactionController;
use App\Http\Controllers\api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{user}', [UserController::class, 'show']);
Route::get('/users/{user}/transactions', [UserController::class, 'transactions']);
Route::get('/users/{user}/singleplayerGames', [UserController::class, 'games']);
Route::get('/users/{user}/multiplayerGames', [UserController::class, 'multiplayerGames']);
Route::put('/users/{user}', [UserController::class, 'update']);
Route::delete('/users/{user}', [UserController::class, 'destroy']);
Route::get('/transactions', [TransactionController::class, 'index']);
Route::get('/transactions/{transaction}', [TransactionController::class, 'show']);
Route::post('/transactions', [TransactionController::class, 'store']);
Route::get('/games', [GameController::class, 'index']);
Route::get('/games/{game}', [GameController::class, 'show']);
Route::post('/games', [GameController::class, 'store']);
Route::put('/games/{game}', [GameController::class, 'update']);

Route::post('/multiplayerGamesPlayed', [MultiplayerGamePlayedController::class, 'store']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refreshtoken', [AuthController::class, 'refreshToken']);

});
Route::post('/auth/login', [AuthController::class, 'login']);






