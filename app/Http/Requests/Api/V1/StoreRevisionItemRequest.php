<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\RevisionOperation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRevisionItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('revision')) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'field' => ['required', 'string', 'max:64'],
            'operation' => ['sometimes', Rule::enum(RevisionOperation::class)],
            'value' => ['present'],
            'position' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
