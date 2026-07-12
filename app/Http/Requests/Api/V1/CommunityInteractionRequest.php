<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\CommunityReactionType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CommunityInteractionRequest extends FormRequest
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
        return ['type' => ['sometimes', Rule::in(['post', 'comment'])], 'id' => ['sometimes', 'integer'], 'reaction' => ['sometimes', Rule::enum(CommunityReactionType::class)]];
    }
}
