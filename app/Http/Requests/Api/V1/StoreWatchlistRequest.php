<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreWatchlistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $creating = $this->isMethod('POST');

        return ['name' => [$creating ? 'required' : 'sometimes', 'string', 'max:255'], 'slug' => ['sometimes', 'string', 'max:255'], 'description' => ['sometimes', 'nullable', 'string', 'max:2000'], 'universe_id' => ['sometimes', 'nullable', 'integer', 'exists:universes,id'], 'is_default' => ['sometimes', 'boolean'], 'expected_version' => [$creating ? 'sometimes' : 'required', 'integer', 'min:0']];
    }
}
