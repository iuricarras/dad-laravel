<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
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

    public function games(User $user)
    {
        return $user->games;
    }
}
