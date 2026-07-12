<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\MediaAttachmentRole;
use App\Models\MediaAttachment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMediaAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', MediaAttachment::class) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'media_asset_id' => ['nullable', 'integer', 'exists:media_assets,id', 'required_without:external_embed_id', 'prohibited_with:external_embed_id'],
            'external_embed_id' => ['nullable', 'integer', 'exists:external_embeds,id', 'required_without:media_asset_id', 'prohibited_with:media_asset_id'],
            'attachable_type' => ['required', 'string', Rule::in(['universe', 'franchise', 'work', 'work_translation', 'season', 'episode', 'lore_entity', 'lore_entity_translation', 'lore_alias', 'entity_appearance', 'lore_relationship', 'timeline', 'timeline_entry'])],
            'attachable_id' => ['required', 'integer', 'min:1'], 'role' => ['required', Rule::enum(MediaAttachmentRole::class)], 'position' => ['sometimes', 'integer', 'min:0', 'max:10000'],
            'is_primary' => ['sometimes', 'boolean'], 'locale' => ['nullable', 'string', 'max:35', 'regex:/^[A-Za-z]{2,3}(?:[-_][A-Za-z0-9]{2,8})*$/'],
            'caption_override' => ['nullable', 'string', 'max:5000'], 'alt_text_override' => ['nullable', 'string', 'max:1000'], 'spoiler_constraint_id' => ['nullable', 'integer', 'exists:spoiler_constraints,id'],
        ];
    }
}
