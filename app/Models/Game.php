<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $fillable = [
        'name',
        'price',
        'genre',
        'release_date',
        'created_user_id',
        'winner_user_id',
        'board_id',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function user()
    {
        return $this->hasOne(User::class,'id','created_user_id');
    }

    public function multiplayerGamesPlayed()
    {
        return $this->hasMany(MultiplayerGamePlayed::class, 'game_id', 'id');
    }

    public function winner()
    {
        return $this->hasOne(User::class, 'id', 'winner_user_id');
    }
    
    public function board()
    {
        return $this->hasOne(Board::class, 'id', 'board_id');
    }


}
