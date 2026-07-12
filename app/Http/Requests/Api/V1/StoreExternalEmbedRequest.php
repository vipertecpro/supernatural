<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\ExternalMediaProvider;
use App\Enums\MediaKind;
use App\Models\ExternalEmbed;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExternalEmbedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', ExternalEmbed::class) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'universe_id' => ['nullable', 'integer', 'exists:universes,id'], 'source_id' => ['required', 'integer', 'exists:sources,id'],
            'content_license_id' => ['nullable', 'integer', 'exists:content_licenses,id'], 'provider' => ['required', Rule::enum(ExternalMediaProvider::class)],
            'url' => ['required', 'url:https', 'max:2048'], 'kind' => ['required', Rule::enum(MediaKind::class)], 'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'], 'creator' => ['nullable', 'string', 'max:255'],
            'publisher' => ['nullable', 'string', 'max:255'], 'attribution_text' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
