<?php

namespace App\Http\Requests\AssetCategory;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
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
        $category = $this->route('asset_category');
        $categoryId = is_object($category) ? $category->id : $category;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('asset_categories', 'name')->ignore($categoryId)],
            'code' => ['sometimes', 'required', 'string', 'max:50', Rule::unique('asset_categories', 'code')->ignore($categoryId)],
            'description' => ['nullable', 'string'],
            'schema_fields' => ['nullable', 'array'],
        ];
    }
}
