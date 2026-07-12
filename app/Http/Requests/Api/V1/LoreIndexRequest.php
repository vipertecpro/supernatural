<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\LoreEntityType;
use App\Enums\LoreRelationshipStatus;
use App\Enums\PublicationStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LoreIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return ['filter' => ['sometimes', 'array:type,status,relationship_type_id'], 'filter.type' => ['sometimes', Rule::enum(LoreEntityType::class)], 'filter.status' => ['sometimes', Rule::in([...array_column(PublicationStatus::cases(), 'value'), ...array_column(LoreRelationshipStatus::cases(), 'value')])], 'filter.relationship_type_id' => ['sometimes', 'integer', 'exists:relationship_types,id'], 'sort' => ['sometimes', Rule::in(['name', '-name', 'published_at', '-published_at', 'sort_key', '-sort_key'])], 'include' => ['sometimes', 'string', 'max:255'], 'page' => ['sometimes', 'array:size,after'], 'page.size' => ['sometimes', 'integer', 'min:1', 'max:50'], 'page.after' => ['sometimes', 'string', 'max:1000']];
    }

    public function pageSize(): int
    {
        return min(50, max(1, $this->integer('page.size', 20)));
    }
}
