<?php

namespace App\Http\Requests\AssetCategory;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:asset_categories,name'],
            'code' => ['required', 'string', 'max:50', 'unique:asset_categories,code'],
            'description' => ['nullable', 'string'],
            'schema_fields' => ['nullable', 'array'],
        ];
    }
}
