<?php

namespace App\Http\Requests\Mutation;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class MutateAssetRequest extends FormRequest
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
            'to_location_id' => ['required', 'integer', 'exists:locations,id', 'different:from_location_id'],
            'mutation_date' => ['required', 'date'],
            'reason' => ['required', 'string'],
            'processed_by' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
