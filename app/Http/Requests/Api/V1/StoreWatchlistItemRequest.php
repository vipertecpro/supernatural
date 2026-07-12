<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWatchlistItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['target_type' => ['required', Rule::in(['work', 'season', 'episode'])], 'target_id' => ['required', 'integer', 'min:1'], 'private_note' => ['nullable', 'string', 'max:1000']];
    }
}
