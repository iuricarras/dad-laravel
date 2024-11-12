<?php

use App\Http\Controllers\api\GameController;
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
Route::get('/users/{user}/games', [UserController::class, 'games']); 

Route::get('/transactions', [TransactionController::class, 'index']);
Route::get('/transactions/{transaction}', [TransactionController::class, 'show']);

Route::get('/games', [GameController::class, 'index']);