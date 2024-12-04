<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpdateGameRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => 'required|in:S,M',
            'winner_user_id' => 'nullable|exists:App\Models\User,id',
            'status' => 'required|in:PE,PL,E,I',
            'began_at' => 'date',
            'ended_at' => 'date|after:began_at|missing_unless:status,E',
            "board_id" => "required|exists:App\Models\Board,id",
            "total_time" => "numeric|nullable",
            'custom' => 'json',
            'total_turns_winner' => 'numeric|nullable',
        ];
    }
}
