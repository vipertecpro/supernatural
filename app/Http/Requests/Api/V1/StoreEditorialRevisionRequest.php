<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\PermissionName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEditorialRevisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission(PermissionName::EditorialRevisionsCreate) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'target_type' => ['required', Rule::in(['franchise', 'work', 'work_translation', 'season', 'episode'])],
            'target_id' => ['required', 'integer', 'min:1'],
            'summary' => ['required', 'string', 'max:500'],
            'parent_revision_id' => ['nullable', 'integer', 'exists:editorial_revisions,id'],
            'metadata' => ['nullable', 'array', 'max:20'],
        ];
    }
}
