<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\Base64Image;

class StoreUpdateUserUpdateRequest extends FormRequest
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
            'name' => 'sometimes|string|min:5',
            'email' => 'sometimes|email',
            'nickname' => 'sometimes|string|min:3',
            'password' => 'sometimes|string|min:3',
            //'photo' => 'sometimes|nullable|base64image',
            'photo' => 'sometimes|nullable|string',
        ];
    }
}
