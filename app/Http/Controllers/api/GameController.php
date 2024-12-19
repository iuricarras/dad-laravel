<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\StoreUpdateGameRequest;
use App\Http\Resources\GameResource;
use App\Models\Board;
use App\Models\Game;
use App\Models\MultiplayerGamePlayed;
use App\Models\Transaction;
use App\Models\User;
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
        $data['created_user_id'] = $request->user()->id;

        $game = Game::create($data);

        if ($game->type == 'M') {
            $players = $request->input('players');
            for ($i = 0; $i < count($players); $i++) {
                $multiplayerGame = new MultiplayerGamePlayed();
                $multiplayerGame->game_id = $game->id;
                $multiplayerGame->user_id = $players[$i]['player']['id'];
                $multiplayerGame->save();

                $transactionData = new Transaction();

                $transactionData->fill([
                    'type' => 'I',
                    'transaction_datetime' => $data['began_at'],
                    'user_id' => $players[$i]['player']['id'],
                    'game_id' => $game->id,
                    'brain_coins' => -5,
                ]);

                $user = User::find($players[$i]['player']['id']);
                $user->decrement('brain_coins_balance', 5);

                $transactionData->save();
            }
            return new GameResource($game);
        }
        
        if ($data['board_id'] != 1) {
            $transactionData = new Transaction();


            $transactionData->fill([
                'type' => 'I',
                'transaction_datetime' => $data['began_at'],
                'user_id' => $data['created_user_id'],
                'game_id' => $game->id,
                'brain_coins' => -1,
            ]);

            $transactionData->save();

            $request->user()->decrement('brain_coins_balance', 1);
        }

        return new GameResource($game);
    }

    public function update(StoreUpdateGameRequest $request, Game $game)
    {
        $data = $request->validated();
        if ($request->input('ended_at') != null) {
            $time =  new Carbon($request->input('ended_at'));
            $data['ended_at'] = $time->toDateTimeString();
        }

        $game->update($data);

        if ($game->type == 'M') {
            $players = $request->input('players');
            for ($i = 0; $i < count($players); $i++) {
                $multiplayerGame = MultiplayerGamePlayed::where('game_id', $game->id)->where('user_id', $players[$i]['player']['id'])->first();
                $multiplayerGame->player_won = ($players[$i]['player']['id'] == $game->winner_user_id) ? 1 : 0;
                $multiplayerGame->pairs_discovered = $players[$i]['numPars'];
                $multiplayerGame->save();

                if ($players[$i]['player']['id'] == $game->winner_user_id) {
                    $transactionData = new Transaction();

                    $transactionData->fill([
                        'type' => 'I',
                        'transaction_datetime' => $data['ended_at'],
                        'user_id' => $players[$i]['player']['id'],
                        'game_id' => $game->id,
                        'brain_coins' => count($players) * 5 - 3,
                    ]);

                    $transactionData->save();

                    $user = User::find($players[$i]['player']['id']);
                    $user->increment('brain_coins_balance', count($players) * 5 - 3);
                }
            }
        }

        return new GameResource($game);
    }

    public function topSinglePlayerGames(Request $request)
    {
        $boardId = $request->input('board_id');
        $sortBy = $request->input('sort_by', 'total_time');

        $query = Game::where('type', 'S')
            ->whereNotNull('total_turns_winner')
            ->whereHas('user', function ($query) {
                $query->whereNull('deleted_at');
            });

        if ($boardId) {
            $query->where('board_id', $boardId);
        }

        $topGames = $query
            ->orderBy($sortBy, 'asc')
            ->with(['user:id,nickname', 'board:id,board_cols,board_rows'])
            ->take(5)
            ->get();

        $minTurns = $query->min('total_turns_winner');

        return response()->json([
            'top_games' => $topGames,
            'min_turns' => $minTurns,
        ]);
    }


    public function topMultiplayerGames(Request $request)
    {
        $boardId = $request->input('board_id');

        $query = Game::where('type', 'M')
            ->whereNotNull('winner_user_id')
            ->whereHas('user', function ($query) {
                $query->whereNULL('deleted_at');
            });


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


    public function personalScoreboard(Request $request)
    {
        $userId = $request->user()->id;

        $singlePlayerData = Game::where('type', 'S')
            ->where('created_user_id', $userId)
            ->select(
                'board_id',
                DB::raw('MIN(total_time) as best_time'),
                DB::raw('MIN(total_turns_winner) as min_turns')
            )
            ->groupBy('board_id')
            ->with('board:id,board_cols,board_rows')
            ->orderBy(DB::raw('(SELECT board_cols FROM boards WHERE boards.id = games.board_id)'), 'asc')
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


    public function gameHistory(Request $request)
    {
        $userId = $request->user()->id;

        $page = $request->query('page', 1);
        $itemsPerPage = $request->query('itemsPerPage', 10);
        $offset = ($page - 1) * $itemsPerPage;
        $type = $request->query('type', null);
        $board = $request->query('board', null);

        $gamesQuery = Game::where(function ($query) use ($userId) {
            $query->where('created_user_id', $userId)
                ->orWhereHas('multiplayerGamesPlayed', function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                });
        });

        if ($type) {
            $gamesQuery->where('type', $type);
        }

        if ($board) {
            $gamesQuery->where('board_id', $board);
        }

        $games = $gamesQuery
            ->with(['board:id,board_cols,board_rows', 'creator:id,nickname', 'winner:id,nickname'])
            ->select('id', 'type', 'status', 'began_at', 'ended_at', 'total_time', 'total_turns_winner', 'board_id', 'created_user_id', 'winner_user_id', 'created_at')
            ->orderBy('created_at', 'desc')
            ->skip($offset)
            ->take($itemsPerPage)
            ->get();

        $totalGames = $gamesQuery->count();

        return response()->json([
            'games' => $games,
            'total' => $totalGames,
            'page' => $page,
            'itemsPerPage' => $itemsPerPage,
        ]);
    }
}
