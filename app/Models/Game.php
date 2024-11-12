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
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function users()
    {
        return $this->hasOne(User::class,'id','created_user_id');
    }

}
