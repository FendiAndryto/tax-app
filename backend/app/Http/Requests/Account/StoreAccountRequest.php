<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'name_en' => 'nullable|string|max:100',
            'name_ja' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:50',
            'initial_balance' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ];
    }
}
