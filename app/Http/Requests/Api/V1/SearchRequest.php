<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\CanonClassification;
use App\Enums\CanonStatus;
use App\Enums\SearchDocumentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'q' => ['required', 'string', 'min:2', 'max:100'], 'filter' => ['sometimes', 'array:universe_id,type,canon'],
            'filter.universe_id' => ['sometimes', 'integer', 'exists:universes,id'], 'filter.type' => ['sometimes', Rule::enum(SearchDocumentType::class)],
            'filter.canon' => ['sometimes', 'string', Rule::in([...array_column(CanonStatus::cases(), 'value'), ...array_column(CanonClassification::cases(), 'value')])],
            'locale' => ['sometimes', 'string', 'max:35', 'regex:/^[A-Za-z]{2,3}(?:[-_][A-Za-z0-9]{2,8})*$/'], 'page' => ['sometimes', 'array:size,after'],
            'page.size' => ['sometimes', 'integer', 'min:1', 'max:50'], 'page.after' => ['sometimes', 'string', 'max:500'], 'sort' => ['sometimes', Rule::in(['relevance'])],
        ];
    }
}
