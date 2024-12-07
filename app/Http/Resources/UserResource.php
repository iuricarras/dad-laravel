<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return
        [
            'id'=> $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'type' => $this->type,
            'nickname' => $this->nickname,
            'blocked' => $this->blocked,
            'brain_coins_balance' => $this->brain_coins_balance,
            'deleted_at' => $this->deleted_at,
            //'photo_filename' => $this->photo_filename
            'photo_filename' => $this->photo_filename
                ? asset('storage/' . $this->photo_filename)
                : null,
        ];
    }
}
