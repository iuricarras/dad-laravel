<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\Game;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{


    public function index()
    {
        return User::get();
    }

    public function show(User $user)
    {
        return $user;
    }

    public function showMe(Request $request)
    {
        return new UserResource($request->user());
    }

    public function transactions(User $user)
    {

        $users = $user->transactions();
        
        return $users -> orderBy('transaction_datetime', 'desc')->get();
    }
    public function games(User $user)
    {
        // Busca todos os jogos do usuário
        $games = $user->games()
            ->with('board') // Adiciona a relação com a tabela 'boards'
            ->orderBy('began_at', 'desc')
            ->get();

        // Mapeia para adicionar as informações do board dentro de cada jogo
        $gamesWithBoardDetails = $games->map(function ($game) {
            return [
                'id' => $game->id,
                'type' => $game->type,
                'created_user_id' => $game->created_user_id,
                'winner_user_id' => $game->winner_user_id,
                'status' => $game->status,
                'began_at' => $game->began_at,
                'ended_at' => $game->ended_at,
                'total_time' => $game->total_time,
                'board' => [
                    'board_id' => $game->board_id,
                    'board_cols' => $game->board->board_cols, // Número de colunas do board
                    'board_rows' => $game->board->board_rows, // Número de linhas do board
                ],
            ];
        });

        return response()->json($gamesWithBoardDetails);
    }



    public function singleplayerGames(User $user)
    {
        return $user->games->where('type', '==', 'S');
    }

    public function multiplayerGames(User $user)
    {
        $multiplayerGames = $user->multiplayerGames;

        $gamesInformation = Game::whereIn('id', $multiplayerGames->pluck('game_id'))->get();

        return $gamesInformation;
    }

    public function update(StoreUpdateUserRequest $request, User $user)
    {

        $user->update($request->validated());

        return new UserResource($user);
    }

    public function destroy(User $user)
    {
        if($user->transactions()->count() > 0 || $user->games()->count() > 0)
        {
            $user->delete();
            return response()->json([
                'message' => 'User has transactions or games, soft deleted.',
            ], 200);
        }else{
            $user->forceDelete();
        }

        return response()->json([], 204);
    }
}
