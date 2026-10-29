<?php

namespace App\Http\Requests\Asset;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ScanAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Kode aset hanya huruf, angka, titik, strip, dan underscore.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9._-]+$/'],
        ];
    }
}
