<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\CommunityPollResultsVisibility;
use App\Enums\CommunityPollType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCommunityPollRequest extends FormRequest
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
        return ['question' => ['required', 'string', 'max:240'], 'type' => ['required', Rule::enum(CommunityPollType::class)], 'maximum_choices' => ['required', 'integer', 'min:1', 'max:10'], 'results_visibility' => ['required', Rule::enum(CommunityPollResultsVisibility::class)], 'closes_at' => ['nullable', 'date', 'after:now'], 'options' => ['required', 'array', 'min:2', 'max:10'], 'options.*' => ['required', 'string', 'max:160', 'distinct']];
    }
}
