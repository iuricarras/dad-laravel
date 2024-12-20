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
use App\Models\Transaction;
use Carbon\Carbon;


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

    if ($request->has('search')) {
        $query->where('nickname', 'LIKE', '%' . $request->input('search') . '%');
    }

    $query->orderBy('nickname', 'asc');
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


    public function checkBeforeDelete(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'error' => 'Senha inválida'], 403);
        }
        return $this->destroy($user);
    }



    public function destroy(User $user)
    {
        if($user->transactions()->count() > 0 || $user->games()->count() > 0)
        {
            $user->brain_coins_balance = 0;
            $user->save();
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

        $validatedData = $request->validated();

        $nextId = User::max('id') + 1;


        if (isset($validatedData['photo'])) {
            $photoContent = base64_decode(preg_replace('/^data:image\/[a-zA-Z]+;base64,/', '', $validatedData['photo']));

            if ($photoContent === false) {
                return response()->json(['error' => 'Erro ao decodificar a imagem.'], 422);
            }

            $imageDetails = getimagesizefromstring($photoContent);

            if ($imageDetails === false) {
                return response()->json(['error' => 'Não foi possível identificar o formato da imagem.'], 422);
            }

            $mimeType = $imageDetails['mime'];

            $extension = '';
            switch ($mimeType) {
                case 'image/jpeg':
                    $extension = '.jpg';
                    break;
                case 'image/png':
                    $extension = '.png';
                    break;
                case 'image/gif':
                    $extension = '.gif';
                    break;
                default:
                return response()->json(['error' => 'Formato de imagem não suportado.'], 422);
            }
            $photoFilename = $nextId . '_' . uniqid() . $extension;

			Storage::disk('public')->put('photos/' . $photoFilename, $photoContent);

            $validatedData['photo_filename'] = $photoFilename;
        }

        $validatedData['password'] = Hash::make($validatedData['password']);

        $user = User::create($validatedData);


        $transactionData = new Transaction();
        $transactionData->fill([
            'type' => 'B',
            'transaction_datetime' => Carbon::now()->format('Y-m-d H:i:s'),
            'user_id' => $user->id,
            'brain_coins' => 10,
        ]);
        $transactionData->save();
        $user->increment('brain_coins_balance', 10);
        return new UserResource($user);
    }

    public function createAdmin(StoreCreateUserRequest $request, User $user)
    {

        $validatedData = $request->validated();

        $nextId = User::max('id') + 1;

        if (isset($validatedData['photo'])) {
            $photoContent = base64_decode(preg_replace('/^data:image\/[a-zA-Z]+;base64,/', '', $validatedData['photo']));

            if ($photoContent === false) {
                return response()->json(['error' => 'Erro ao decodificar a imagem.'], 422);
            }

            $imageDetails = getimagesizefromstring($photoContent);

            if ($imageDetails === false) {
                return response()->json(['error' => 'Não foi possível identificar o formato da imagem.'], 422);
            }

            $mimeType = $imageDetails['mime'];

            $extension = '';
            switch ($mimeType) {
                case 'image/jpeg':
                    $extension = '.jpg';
                    break;
                case 'image/png':
                    $extension = '.png';
                    break;
                case 'image/gif':
                    $extension = '.gif';
                    break;
                default:
                return response()->json(['error' => 'Formato de imagem não suportado.'], 422);
            }

            $photoFilename = $nextId . '_' . uniqid() . $extension;

			Storage::disk('public')->put('photos/' . $photoFilename, $photoContent);

            $validatedData['photo_filename'] = $photoFilename;
        }

        $validatedData['password'] = Hash::make($validatedData['password']);

        $validatedData['type'] = 'A';


        $user = User::create($validatedData);

        return new UserResource($user);
    }

    public function updateFoto(StoreUpdateUserFotoRequest $request, User $user)
    {

        $validatedData = $request->validated();


        if ($request->has('password') == $user->password) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        }

        if ($request->has('photo')) {
            $photoContent = base64_decode(preg_replace('/^data:image\/[a-zA-Z]+;base64,/', '', $validatedData['photo']));

            if ($photoContent === false) {
                return response()->json(['error' => 'Erro ao decodificar a imagem.'], 422);
            }

            $imageDetails = getimagesizefromstring($photoContent);

            if ($imageDetails === false) {
                return response()->json(['error' => 'Não foi possível identificar o formato da imagem.'], 422);
            }

            $mimeType = $imageDetails['mime'];

            $extension = '';
            switch ($mimeType) {
                case 'image/jpeg':
                    $extension = '.jpg';
                    break;
                case 'image/png':
                    $extension = '.png';
                    break;
                case 'image/gif':
                    $extension = '.gif';
                    break;
                default:
                return response()->json(['error' => 'Formato de imagem não suportado.'], 422);
            }

            if (!empty($user->photo_filename)) {
                Storage::disk('public')->delete('photos/' . $user->photo_filename);
            }

            $photoFilename = $user->id . '_' . uniqid() . $extension;

            Storage::disk('public')->put('photos/' . $photoFilename, $photoContent);

            $validatedData['photo_filename'] = $photoFilename;

            unset($validatedData['photo']);
        }

        $user->update($validatedData);

        return new UserResource($user);
    }

}
