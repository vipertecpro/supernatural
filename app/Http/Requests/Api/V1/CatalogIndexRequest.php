<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\PublicationStatus;
use App\Enums\WorkType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CatalogIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'array:size,after'],
            'page.size' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'page.after' => ['sometimes', 'string', 'max:500'],
            'filter' => ['sometimes', 'array:type,status,franchise_id'],
            'filter.type' => ['sometimes', Rule::enum(WorkType::class)],
            'filter.status' => ['sometimes', Rule::enum(PublicationStatus::class)],
            'filter.franchise_id' => ['sometimes', 'integer', 'exists:franchises,id'],
            'sort' => ['sometimes', Rule::in(['position', '-position', 'published_at', '-published_at', 'title', '-title'])],
            'include' => ['sometimes', 'string', Rule::in(['translations', 'series_detail'])],
            'locale' => ['sometimes', 'string', 'max:35', 'regex:/^[A-Za-z]{2,3}(?:[-_][A-Za-z0-9]{2,8})*$/'],
        ];
    }

    public function pageSize(): int
    {
        return (int) $this->input('page.size', 20);
    }
}
