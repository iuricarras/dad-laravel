<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\Game;
use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use SoftDeletes;

    public function index()
    {
        return UserResource::collection(User::get());
    }

    public function show(User $user)
    {
        return new UserResource($user);
    }

    public function transactions(User $user)
    {
        return $user->transactions;
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
            return response()->json(['error' => 'User has transactions or games, soft deleted'], 409);
        }else{
            $user->forceDelete();
        }

        return response()->json([], 204);
    }
}
