<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
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
            'type' => 'required|in:B,P,I',
            'transaction_datetime' => 'required|date',
            'user_id' => 'exists:users,id',
            'game_id' => 'nullable|exists:games,id|required_if:type,I',
            'euros' => 'nullable|numeric|min:0|required_if:type,P', 
            'payment_type' => 'nullable|required_if:type,P|in:MBWAY,IBAN,MB,VISA,PAYPAL',
            'payment_reference' => 'nullable|required_if:type,P|string|max:255',
            'brain_coins' => 'required|integer',
        ];
    }
}
