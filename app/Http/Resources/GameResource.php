<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'created_user_id' => $this->created_user_id,
            'type' => $this->type,
            'status' => $this->status,
            'total_time' => $this->total_time,
            'board_id' => $this->board_id,
            'custom' => $this->custom,
        ];
    }
}
