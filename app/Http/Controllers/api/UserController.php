<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUpdateUserRequest;
use App\Http\Requests\StoreUpdateUserFotoRequest;
use App\Http\Requests\StoreCreateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\Game;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;


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


    // função para verificar a pass e despois apaga o user
    public function checkBeforeDelete(Request $request, User $user)
    {
        // valida os dados(para não fazer outro StoreRequest)
        $request->validate([
            'password' => 'required|string',
        ]);

        // verificar se password é válida
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'error' => 'Senha inválida'], 403);
        }

        // se a password for válida, chamar a função abaixo (destroy)
        return $this->destroy($user);
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

    public function createUser(StoreCreateUserRequest $request, User $user)
    {

        // valida os dados
        $validatedData = $request->validated();

        // calcula o próximo ID
        $nextId = User::max('id') + 1;

        // caso nos dados exista uma foto
        if (isset($validatedData['photo'])) {
            $photoContent = base64_decode(preg_replace('/^data:image\/[a-zA-Z]+;base64,/', '', $validatedData['photo']));
            $photoFilename = $nextId . '_' . uniqid() . '.jpg';

			Storage::disk('public')->put('photos/' . $photoFilename, $photoContent);

            // altera o campo da foto com o nome gerado
            $validatedData['photo_filename'] = $photoFilename;
        }

        // aplica uma Hash na password antes de criar o user
        $validatedData['password'] = Hash::make($validatedData['password']);

        // cria o user
        $user = User::create($validatedData);

        return new UserResource($user);
    }


    public function updateFoto(StoreUpdateUserFotoRequest $request, User $user)
    {

        // valida os dados
        $validatedData = $request->validated();


        // verifica se a password existe no pedido,
        // antes de refazer e alterar a HASH da password
        if ($request->has('password') == $user->password) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        }

        // verifica se a foto existe
        if ($request->has('photo')) {
            // remove o prefixo BASE64 e decodifica a imagem
            $photoContent = base64_decode(preg_replace('/^data:image\/[a-zA-Z]+;base64,/', '', $validatedData['photo']));

            if ($photoContent === false) {
                return response()->json(['error' => 'Erro ao decodificar a imagem.'], 422);
            }

            // verifica se o user já tem uma foto armazenada
            if (!empty($user->photo_filename)) {
                // remove a foto antiga
                Storage::disk('public')->delete('photos/' . $user->photo_filename);
            }

            // gera um nome para a foto
            $photoFilename = $user->id . '_' . uniqid() . '.jpg';

            // guarda a imagem (como esta no config/filesystems.php) (storage/app/public/photos)
            Storage::disk('public')->put('photos/' . $photoFilename, $photoContent);

            // atualiza o nome da foto na base de dados
            $validatedData['photo_filename'] = $photoFilename;

            // remove o campo da foto nos dados a atualizar
            unset($validatedData['photo']);
        }


        // atualiza o resto dos dados do user
        $user->update($validatedData);

        return new UserResource($user);
    }

}
