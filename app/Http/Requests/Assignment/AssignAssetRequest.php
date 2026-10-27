<?php

namespace App\Http\Requests\Assignment;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AssignAssetRequest extends FormRequest
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
            'asset_id' => ['required', 'integer', 'exists:assets,id'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'assigned_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'assigned_by' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
