<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\BunkerMembershipRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvitationRequest extends FormRequest
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
        $creating = $this->routeIs('api.v1.bunkers.invitations.store');

        return ['invited_user_id' => [Rule::requiredIf($creating), 'integer', 'exists:users,id'], 'proposed_role' => [Rule::requiredIf($creating), Rule::enum(BunkerMembershipRole::class)->only([BunkerMembershipRole::Administrator, BunkerMembershipRole::Moderator, BunkerMembershipRole::Member])], 'token' => [Rule::requiredIf($this->routeIs('api.v1.bunker-invitations.accept')), 'string', 'size:64']];
    }
}
