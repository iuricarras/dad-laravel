<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUpdateGameRequest;
use App\Http\Resources\GameResource;
use App\Models\Board;
use App\Models\Game;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $time =  new Carbon($request->input('began_at'));
        $data['began_at'] = $time->toDateTimeString();
        $data['created_user_id'] = 7;
        #$data['user_id'] = $request->user()->id;
        $game = Game::create($data);
        return new GameResource($game);
    }  

    public function update(StoreUpdateGameRequest $request, Game $game)
    {
        $data = $request->validated();
        if($request->input('ended_at') != null){
            $time =  new Carbon($request->input('ended_at'));
            $data['ended_at'] = $time->toDateTimeString();
        }
        $game->update($data);
        return new GameResource($game);
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

        $query = Game::where('type', 'M')
            ->whereNotNull('winner_user_id');

        if ($boardId) {
            $query->where('board_id', $boardId);
        }

        $topWinners = $query
            ->select(
                'winner_user_id',
                'board_id',
                DB::raw('COUNT(*) as wins'),
                DB::raw('MIN(ended_at) as first_victory_time')
            )
            ->groupBy('winner_user_id', 'board_id')
            ->with(['winner:id,nickname', 'board:id,board_cols,board_rows'])
            ->orderBy('wins', 'desc')
            ->orderBy('first_victory_time', 'asc')
            ->take(5)
            ->get();

        return response()->json($topWinners);
    }

    
    public function personalScoreboard(Request $request, $userId)
    {
        
        $singlePlayerData = Game::where('type', 'S')
            ->where('created_user_id', $userId)
            ->select('board_id', 
                DB::raw('MIN(total_time) as best_time'), )
            ->groupBy('board_id')
            ->with('board:id,board_cols,board_rows')
            ->get();

        
        $totalVictories = Game::where('type', 'M')
            ->where('winner_user_id', $userId)
            ->count();

        $totalLosses = Game::where('type', 'M')
            ->whereHas('multiplayerGamesPlayed', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->where('winner_user_id', '!=', $userId)
            ->count();

        return response()->json([
            'single_player' => $singlePlayerData,
            'multiplayer' => [
                'total_victories' => $totalVictories,
                'total_losses' => $totalLosses,
            ],
        ]);
    }

    public function gameHistory($userId)
{
    $games = Game::where(function ($query) use ($userId) {
        $query->where('created_user_id', $userId)
              ->orWhereHas('multiplayerGamesPlayed', function ($q) use ($userId) {
                  $q->where('user_id', $userId);
              });
    })
    ->with('board:id,board_cols,board_rows')
    ->select('id', 'type', 'status', 'began_at', 'ended_at', 'total_time', 'board_id', 'created_at')
    ->orderBy('created_at', 'desc')
    ->get();

    return response()->json($games);
}


}