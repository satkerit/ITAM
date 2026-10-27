<?php

namespace App\Http\Requests\Disposal;

use App\Enums\DisposalType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class DisposeAssetRequest extends FormRequest
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
            'asset_id' => ['required', 'integer', 'exists:assets,id', 'unique:asset_disposals,asset_id'],
            'disposal_type' => ['required', new Enum(DisposalType::class)],
            'date' => ['required', 'date'],
            'value' => ['nullable', 'numeric', 'min:0'],
            'buyer_name' => ['nullable', 'string', 'max:255'],
            'authorized_by' => ['nullable', 'integer', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
