<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Franchise;
use App\Models\Universe;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFranchiseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Franchise::class) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash:ascii', Rule::unique('franchises')->where('universe_id', $this->universe()->getKey())],
            'description' => ['nullable', 'string', 'max:10000'],
            'position' => ['sometimes', 'integer', 'min:0'],
            'metadata' => ['sometimes', 'array'],
        ];
    }

    private function universe(): Universe
    {
        $universe = $this->route('universe');

        return $universe instanceof Universe ? $universe : throw new \LogicException('Universe route binding is required.');
    }
}
