<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpdateTransactionRequest extends FormRequest
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
            'game_id' => 'nullable|exists:games,id',
            'amount' => 'required|numeric',
            'type' => 'required|in:B,P,I',
            'brain_coins' => 'required|numeric',
            'payment_method' => 'in:MBWAY,IBAN,PAYPAL,VISA,MB',
            'payment_reference' => 'string',
        ];
    }
}
