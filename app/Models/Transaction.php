<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'type',
        'transaction_datetime',
        'user_id',
        'game_id',
        'euros',
        'payment_type',
        'payment_reference',
        'brain_coins',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}
