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
