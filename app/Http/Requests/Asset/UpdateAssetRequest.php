<?php

namespace App\Http\Requests\Asset;

use App\Enums\AssetStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateAssetRequest extends FormRequest
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
        $asset = $this->route('asset');
        $assetId = is_object($asset) ? $asset->id : $asset;

        return [
            'asset_code' => ['sometimes', 'required', 'string', 'max:100', 'regex:/^[A-Za-z0-9._-]+$/', Rule::unique('assets', 'asset_code')->ignore($assetId)],
            'category_id' => ['sometimes', 'required', 'integer', 'exists:asset_categories,id'],
            'location_id' => ['sometimes', 'required', 'integer', 'exists:locations,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'status' => ['sometimes', 'required', new Enum(AssetStatus::class)],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'purchase_date' => ['nullable', 'date'],
            'purchase_cost' => ['nullable', 'numeric', 'min:0'],
            'specifications' => ['nullable', 'array'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
