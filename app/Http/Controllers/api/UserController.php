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


    public function index(Request $request)
{
    $page = max(1, (int) $request->query('page', 1));
    $itemsPerPage = max(1, (int) $request->query('itemsPerPage', 8));
    $type = $request->query('type', 'All');
    $blocked = $request->query('blocked', 'All');

    $query = User::query();

    if ($type != 'All') {
        $query->where('type', $type);
    }

    if ($blocked != 'All') {
        $query->where('blocked', $blocked === 'Yes');
    }

    $totalUsers = $query->count();
    $users = $query->skip(($page - 1) * $itemsPerPage)
                   ->take($itemsPerPage)
                   ->get();

    return response()->json([
        'users' => $users,
        'total' => $totalUsers,
        'page' => $page,
        'itemsPerPage' => $itemsPerPage,
        'type' => $type,
        'blocked' => $blocked,
    ]);
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
    
    public function games(Request $request, User $user)
{
    $page = $request->query('page', 1);
    $itemsPerPage = $request->query('itemsPerPage', 10);
    $type = $request->query('type', null);
    $status = $request->query('status', null);

    $offset = ($page - 1) * $itemsPerPage;

    $query = $user->games()->with('board')->orderBy('began_at', 'desc');

    // Aplica os filtros se fornecidos
    if ($type) {
        $query->where('type', $type);
    }
    if ($status) {
        $query->where('status', $status);
    }

    $totalGames = $query->count();

    $games = $query->skip($offset)->take($itemsPerPage)->get();

    return response()->json([
        'games' => $games,
        'total' => $totalGames,
        'page' => $page,
        'itemsPerPage' => $itemsPerPage,
    ]);
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
