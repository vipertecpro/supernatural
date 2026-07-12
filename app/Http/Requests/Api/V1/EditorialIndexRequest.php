<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\EditorialRevisionStatus;
use App\Models\EditorialRevision;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EditorialIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', EditorialRevision::class) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'status' => ['sometimes', Rule::enum(EditorialRevisionStatus::class)],
            'target_type' => ['sometimes', Rule::in(['franchise', 'work', 'work_translation', 'season', 'episode'])],
            'page.size' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ];
    }
}
