<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
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
            'type' => 'required|in:cash_in,cash_out',
            'description' => 'nullable|string',
        ];
    }
}
