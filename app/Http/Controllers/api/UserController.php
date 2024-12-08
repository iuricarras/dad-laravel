<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUpdateUserRequest;
use App\Http\Requests\StoreUpdateUserFotoRequest;
use App\Http\Resources\UserResource;
use App\Models\Game;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        $games = $user->games()
            ->with('board')
            ->orderBy('began_at', 'desc')
            ->get();

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
                    'board_cols' => $game->board->board_cols,
                    'board_rows' => $game->board->board_rows,
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


    public function update_Foto(StoreUpdateUserFotoRequest $request, User $user)
    {

        $validatedData = $request->validated();

        // Verifica se o campo "photo" existe
    if ($request->has('photo')) {
        // Remove o prefixo Base64 e decodifica a imagem
        $photoContent = base64_decode(preg_replace('/^data:image\/[a-zA-Z]+;base64,/', '', $validatedData['photo']));

        if ($photoContent === false) {
            return response()->json(['error' => 'Erro ao decodificar a imagem.'], 422);
        }

        // Gera um nome para a imagem
        //$photoFilename = $user->id . '_' . uniqid() . '.jpg';
        // Vai buscar o nome da imagem da base de dados
        $photoFilename = $user->photo_filename;

        // Guarda a imagem (como esta no config/filesystems.php) (storage/app/public/photos)
        Storage::disk('public')->put('photos/' . $photoFilename, $photoContent);

        // Atualiza o campo da base de dados
        $validatedData['photo_filename'] = $photoFilename;

        // Limpa o campo da foto para evitar um erro no update
        unset($validatedData['photo']);
    }


        // Atualiza os dados do utilizador
        $user->update($validatedData);

        return new UserResource($user);
    }

}
