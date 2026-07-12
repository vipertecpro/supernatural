<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class SearchSuggestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['q' => ['required', 'string', 'min:2', 'max:100'], 'universe_id' => ['sometimes', 'integer', 'exists:universes,id'], 'locale' => ['sometimes', 'string', 'max:35'], 'limit' => ['sometimes', 'integer', 'min:1', 'max:10']];
    }
}
