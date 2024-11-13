<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUpdateGameRequest;
use App\Models\Game;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function index()
    {
        return Game::get();
    }
    
    public function show(Game $game)
    {
        return $game;
    }

    public function store(StoreUpdateGameRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        $game = Game::create($data);
        return $game;
    }  

    public function update(StoreUpdateGameRequest $request, Game $game)
    {
        $game->update($request->validated());
        return $game;
    }
}
