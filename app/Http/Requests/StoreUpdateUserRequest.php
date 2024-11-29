<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpdateUserRequest extends FormRequest
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
            'name' => 'required|string|min:5',
            'email' => 'required|email',
            'nickname' => 'required|string|min:3',
            'password' => 'nullable|string|min:3',
            'blocked' => 'required|boolean',
            'brain_coins_balance' => 'nullable|integer',
        ];
    }
}
