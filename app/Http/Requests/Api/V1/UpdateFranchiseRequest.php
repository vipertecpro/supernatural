<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Franchise;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFranchiseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('franchise')) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $franchise = $this->franchise();

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', 'alpha_dash:ascii', Rule::unique('franchises')->where('universe_id', $franchise->universe_id)->ignore($franchise)],
            'description' => ['nullable', 'string', 'max:10000'],
            'position' => ['sometimes', 'integer', 'min:0'],
            'metadata' => ['sometimes', 'array'],
        ];
    }

    private function franchise(): Franchise
    {
        $franchise = $this->route('franchise');

        return $franchise instanceof Franchise ? $franchise : throw new \LogicException('Franchise route binding is required.');
    }
}
