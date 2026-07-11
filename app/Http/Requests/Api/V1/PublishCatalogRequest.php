<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class PublishCatalogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['is_public' => ['sometimes', 'boolean']];
    }

    public function isPublic(): bool
    {
        return $this->boolean('is_public', true);
    }
}
