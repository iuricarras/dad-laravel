<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMultiplayerGamePlayedRequest;
use App\Models\MultiplayerGamePlayed;
use Illuminate\Http\Request;

class MultiplayerGamePlayedController extends Controller
{
    public function store(StoreMultiplayerGamePlayedRequest $request)
    {
        $data = $request->validated();

        $data['user_id'] = $request->user()->id;

        $multiplayerGamePlayed = MultiplayerGamePlayed::create($data);

        return $multiplayerGamePlayed;
    }
}
