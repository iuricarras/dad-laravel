<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MultiplayerGamePlayed extends Model
{
    protected $table = 'multiplayer_games_played';
    public $timestamps = false;
    protected $fillable = [
        'game_id',
        'user_id',
        'score',
    ];

    public function game()
    {
        return $this->belongsTo(Game::class, 'id', 'game_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id', 'user_id');
    }
}
