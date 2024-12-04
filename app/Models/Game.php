<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $fillable = [
        'type',
        'status',
        'began_at',
        'ended_at',
        'created_user_id',
        'winner_user_id',
        'board_id',
        'total_time',
        'custom',
        'total_turns_winner'
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
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_user_id');
    }

    public function board()
    {
        return $this->hasOne(Board::class, 'id', 'board_id');
    }


}
