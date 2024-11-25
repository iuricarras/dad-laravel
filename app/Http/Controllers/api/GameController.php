<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUpdateGameRequest;
use App\Models\Board;
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

    public function topSinglePlayerGames(Request $request)
{
    $boardId = $request->input('board_id');
    $query = Game::where('type', 'S')->whereNotNull('total_time');

    if ($boardId) {
        $query->where('board_id', $boardId);
    }

    $topGames = $query
        ->orderBy('total_time', 'asc')
        ->with(['user:id,nickname', 'board:id,board_cols,board_rows'])
        ->take(5)
        ->get();

    return response()->json($topGames);
}


public function topMultiplayerGames(Request $request)
{
    $boardId = $request->input('board_id');
    $query = Game::where('type', 'M')->whereNotNull('total_time');

    if ($boardId) {
        $query->where('board_id', $boardId);
    }

    $topGames = $query
        ->orderBy('total_time', 'asc')
        ->with(['winner:id,nickname', 'board:id,board_cols,board_rows'])
        ->take(5)
        ->get();

    return response()->json($topGames);
}



public function fetchBoards()
{
    $boards = Board::select('id', 'board_cols', 'board_rows')->get();
    return response()->json($boards);
}

public function fetchFilteredGames(Request $request, $type)
{
    $boardId = $request->input('board_id');
    $query = Game::where('type', $type)->whereNotNull('total_time');

    if ($boardId) {
        $query->where('board_id', $boardId);
    }

    $topGames = $query
        ->orderBy('total_time', 'asc')
        ->with(['user:id,nickname', 'board:id,board_cols,board_rows'])
        ->take(5)
        ->get();

    return response()->json($topGames);
}


}
