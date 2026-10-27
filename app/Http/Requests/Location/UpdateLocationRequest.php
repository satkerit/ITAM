<?php

namespace App\Http\Requests\Location;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLocationRequest extends FormRequest
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
        $location = $this->route('location');
        $locationId = is_object($location) ? $location->id : $location;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('locations', 'name')->ignore($locationId)],
            'address' => ['sometimes', 'required', 'string'],
            'description' => ['nullable', 'string'],
        ];
    }
}
