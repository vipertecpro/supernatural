<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePersonalNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $creating = $this->isMethod('POST');

        return ['target_type' => [$creating ? 'required' : 'sometimes', Rule::in(['work', 'season', 'episode', 'lore_entity', 'timeline_entry', 'user_viewing_journey'])], 'target_id' => [$creating ? 'required' : 'sometimes', 'integer', 'min:1'], 'title' => ['sometimes', 'nullable', 'string', 'max:255'], 'body' => [$creating ? 'required' : 'sometimes', 'string', 'max:10000'], 'is_pinned' => ['sometimes', 'boolean'], 'is_spoiler_sensitive' => ['sometimes', 'boolean'], 'expected_version' => ['sometimes', 'integer', 'min:0']];
    }
}
