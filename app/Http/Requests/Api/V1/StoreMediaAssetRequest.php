<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\MediaKind;
use App\Enums\MediaOrigin;
use App\Models\MediaAsset;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMediaAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', MediaAsset::class) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:'.config('media.max_upload_kilobytes'), 'mimetypes:'.implode(',', array_keys(config('media.mime_types')))],
            'universe_id' => ['nullable', 'integer', 'exists:universes,id'], 'source_id' => ['nullable', 'integer', 'exists:sources,id'],
            'content_license_id' => ['nullable', 'integer', 'exists:content_licenses,id'], 'kind' => ['required', Rule::enum(MediaKind::class)],
            'origin' => ['required', Rule::enum(MediaOrigin::class)], 'alt_text' => ['nullable', 'string', 'max:1000'], 'caption' => ['nullable', 'string', 'max:5000'],
            'attribution_text' => ['nullable', 'string', 'max:5000'], 'copyright_owner' => ['nullable', 'string', 'max:255'],
        ];
    }
}
