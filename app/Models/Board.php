<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
    protected $fillable = ['board_cols', 'board_rows'];

    public function games()
    {
        return $this->hasMany(Game::class, 'board_id', 'id');
    }
}
