<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VoteCommunityPollRequest extends FormRequest
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
        $voting = $this->routeIs('api.v1.community.polls.votes.store');

        return ['option_ids' => [Rule::requiredIf($voting), 'array', 'min:1', 'max:10'], 'option_ids.*' => ['required', 'integer', 'distinct', 'exists:poll_options,id'], 'lock_version' => [Rule::requiredIf($this->routeIs('api.v1.community.polls.close')), 'integer', 'min:0']];
    }
}
