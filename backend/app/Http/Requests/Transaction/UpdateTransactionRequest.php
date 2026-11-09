<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => 'sometimes|required|date',
            'type' => 'sometimes|required|in:cash_in,cash_out',
            'amount' => 'sometimes|required|numeric|min:0.01',
            'category_id' => 'sometimes|required|exists:categories,id',
            'account_id' => 'sometimes|required|exists:accounts,id',
            'payment_method' => 'sometimes|required|string|max:50',
            'description' => 'nullable|string',
        ];
    }
}
