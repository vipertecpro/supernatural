<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePersonalTargetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $targetInRoute = $this->route('type') !== null;

        return ['target_type' => [$targetInRoute ? 'sometimes' : 'required', Rule::in(['universe', 'franchise', 'work', 'season', 'episode', 'lore_entity', 'timeline'])], 'target_id' => [$targetInRoute ? 'sometimes' : 'required', 'integer', 'min:1'], 'rating' => ['sometimes', 'required', 'integer', 'between:1,5']];
    }
}
