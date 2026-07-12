<?php

namespace App\Domain\Community\Actions;

use App\Domain\Community\Exceptions\InvalidCommunityOperation;
use App\Domain\Identity\Services\InteractionSafetyEvaluator;
use App\Domain\Moderation\Services\RestrictionEvaluator;
use App\Enums\BunkerBanStatus;
use App\Enums\BunkerInvitationStatus;
use App\Enums\BunkerJoinRequestStatus;
use App\Enums\BunkerMembershipRole;
use App\Enums\BunkerMembershipStatus;
use App\Enums\BunkerStatus;
use App\Enums\BunkerVisibility;
use App\Enums\PublicationStatus;
use App\Enums\RestrictionScope;
use App\Events\BunkerCreated;
use App\Events\BunkerInvitationCreated;
use App\Events\BunkerMemberBanned;
use App\Events\BunkerMembershipApproved;
use App\Events\BunkerMembershipRequested;
use App\Models\Bunker;
use App\Models\BunkerBan;
use App\Models\BunkerInvitation;
use App\Models\BunkerJoinRequest;
use App\Models\BunkerMembership;
use App\Models\BunkerRule;
use App\Models\Universe;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ManageBunkers
{
    public function __construct(private readonly RestrictionEvaluator $restrictions, private readonly AuditLogger $audit, private readonly InteractionSafetyEvaluator $interactionSafety) {}

    /** @param array<string, mixed> $data */
    public function create(User $actor, int $universeId, array $data): Bunker
    {
        $this->denyScope($actor, RestrictionScope::BunkerCreation);
        if (! Universe::query()->where(['status' => PublicationStatus::Published, 'is_public' => true])->whereKey($universeId)->exists()) {
            throw new InvalidCommunityOperation('The selected universe is unavailable.', 'community_universe_unavailable');
        }

        return DB::transaction(function () use ($actor, $universeId, $data): Bunker {
            $bunker = Bunker::query()->create([
                'universe_id' => $universeId,
                'owner_user_id' => $actor->id,
                'name' => trim(strip_tags((string) $data['name'])),
                'slug' => Str::slug((string) ($data['slug'] ?? $data['name'])),
                'description' => $this->plain($data['description'] ?? null),
                'rules_summary' => $this->plain($data['rules_summary'] ?? null),
                'visibility' => BunkerVisibility::from((string) $data['visibility']),
                'status' => BunkerStatus::Draft,
                'requires_approval' => (bool) ($data['requires_approval'] ?? false),
                'requires_invitation' => (string) $data['visibility'] === BunkerVisibility::InviteOnly->value,
                'default_locale' => strtolower((string) ($data['default_locale'] ?? 'en')),
                'spoiler_severity' => $data['spoiler_severity'] ?? null,
                'lock_version' => 0,
            ]);
            $membership = BunkerMembership::query()->create(['bunker_id' => $bunker->id, 'user_id' => $actor->id, 'role' => BunkerMembershipRole::Owner, 'status' => BunkerMembershipStatus::Active, 'approved_by' => $actor->id, 'active_key' => $this->key($bunker->id, $actor->id), 'joined_at' => now()]);
            $bunker->update(['owner_membership_key' => $membership->id]);
            $bunker->categories()->sync($data['category_ids'] ?? []);
            $this->audit->record('community.bunker_created', $bunker, ['universe_id' => $universeId, 'visibility' => $bunker->visibility->value], $actor);
            BunkerCreated::dispatch($bunker->id, $actor->id);

            return $bunker->fresh(['categories', 'memberships']);
        });
    }

    /** @param array<string, mixed> $data */
    public function update(Bunker $bunker, User $actor, array $data): Bunker
    {
        return DB::transaction(function () use ($bunker, $data): Bunker {
            $locked = Bunker::query()->lockForUpdate()->findOrFail($bunker->id);
            $this->assertVersion($locked->lock_version, (int) $data['lock_version']);
            $locked->fill(array_filter([
                'name' => isset($data['name']) ? trim(strip_tags((string) $data['name'])) : null,
                'description' => array_key_exists('description', $data) ? $this->plain($data['description']) : null,
                'rules_summary' => array_key_exists('rules_summary', $data) ? $this->plain($data['rules_summary']) : null,
                'visibility' => $data['visibility'] ?? null,
                'requires_approval' => $data['requires_approval'] ?? null,
                'default_locale' => isset($data['default_locale']) ? strtolower((string) $data['default_locale']) : null,
                'spoiler_severity' => $data['spoiler_severity'] ?? null,
            ], fn (mixed $value): bool => $value !== null));
            $locked->lock_version++;
            $locked->save();
            if (array_key_exists('category_ids', $data)) {
                $locked->categories()->sync($data['category_ids']);
            }

            return $locked->fresh(['categories']);
        });
    }

    public function transition(Bunker $bunker, User $actor, BunkerStatus $status, int $version): Bunker
    {
        return DB::transaction(function () use ($bunker, $actor, $status, $version): Bunker {
            $locked = Bunker::query()->lockForUpdate()->findOrFail($bunker->id);
            $this->assertVersion($locked->lock_version, $version);
            if ($status === BunkerStatus::Published && $locked->status !== BunkerStatus::Draft) {
                throw new InvalidCommunityOperation('Only a draft Bunker may be published.', 'invalid_bunker_transition');
            }
            $locked->status = $status;
            $locked->published_at = $status === BunkerStatus::Published ? now() : $locked->published_at;
            $locked->archived_at = $status === BunkerStatus::Archived ? now() : null;
            $locked->lock_version++;
            $locked->save();
            $this->audit->record('community.bunker_'.($status === BunkerStatus::Published ? 'published' : 'archived'), $locked, ['status' => $status->value, 'lock_version' => $locked->lock_version], $actor);

            return $locked;
        });
    }

    public function transferOwnership(Bunker $bunker, User $actor, BunkerMembership $membership, int $version): Bunker
    {
        return DB::transaction(function () use ($bunker, $actor, $membership, $version): Bunker {
            $locked = Bunker::query()->lockForUpdate()->findOrFail($bunker->id);
            $this->assertVersion($locked->lock_version, $version);
            if ($membership->bunker_id !== $locked->id || $membership->status !== BunkerMembershipStatus::Active || $membership->user_id === null) {
                throw new InvalidCommunityOperation('Ownership may transfer only to an active member.');
            }
            BunkerMembership::query()->whereKey($locked->owner_membership_key)->update(['role' => BunkerMembershipRole::Administrator]);
            $membership->update(['role' => BunkerMembershipRole::Owner]);
            $locked->update(['owner_user_id' => $membership->user_id, 'owner_membership_key' => $membership->id, 'lock_version' => $locked->lock_version + 1]);
            $this->audit->record('community.bunker_ownership_transferred', $locked, ['new_owner_user_id' => $membership->user_id, 'lock_version' => $locked->lock_version], $actor);

            return $locked->fresh();
        });
    }

    public function requestJoin(Bunker $bunker, User $actor, ?string $message): BunkerJoinRequest
    {
        $this->denyScope($actor, RestrictionScope::BunkerMembershipRequest);
        if ($bunker->visibility === BunkerVisibility::InviteOnly || $this->isBanned($bunker, $actor)) {
            throw new InvalidCommunityOperation('This Bunker is not accepting this join request.', 'join_request_unavailable');
        }
        if ($this->isMember($bunker, $actor)) {
            throw new InvalidCommunityOperation('You are already a member.');
        }
        $request = BunkerJoinRequest::query()->create(['bunker_id' => $bunker->id, 'user_id' => $actor->id, 'status' => BunkerJoinRequestStatus::Pending, 'active_key' => $this->key($bunker->id, $actor->id), 'message' => $this->plain($message), 'submitted_at' => now()]);
        BunkerMembershipRequested::dispatch($request->id, $bunker->id, $actor->id);

        return $request;
    }

    public function decideJoin(BunkerJoinRequest $request, User $reviewer, bool $approve, ?string $explanation): BunkerJoinRequest
    {
        return DB::transaction(function () use ($request, $reviewer, $approve, $explanation): BunkerJoinRequest {
            $locked = BunkerJoinRequest::query()->lockForUpdate()->findOrFail($request->id);
            if ($locked->status !== BunkerJoinRequestStatus::Pending || $locked->user_id === null) {
                throw new InvalidCommunityOperation('This join request is already resolved.');
            }
            if ($approve && $this->isBanned($locked->bunker, $locked->user_id)) {
                throw new InvalidCommunityOperation('A banned user cannot join.');
            }
            $locked->update(['status' => $approve ? BunkerJoinRequestStatus::Approved : BunkerJoinRequestStatus::Rejected, 'active_key' => null, 'reviewed_by' => $reviewer->id, 'decision_explanation' => $this->plain($explanation), 'reviewed_at' => now()]);
            if ($approve) {
                BunkerMembership::query()->create(['bunker_id' => $locked->bunker_id, 'user_id' => $locked->user_id, 'role' => BunkerMembershipRole::Member, 'status' => BunkerMembershipStatus::Active, 'active_key' => $this->key($locked->bunker_id, $locked->user_id), 'approved_by' => $reviewer->id, 'joined_at' => now()]);
                BunkerMembershipApproved::dispatch($locked->id, $locked->bunker_id, $locked->user_id);
            }

            return $locked;
        });
    }

    public function withdrawJoin(BunkerJoinRequest $request, User $actor): BunkerJoinRequest
    {
        if ($request->user_id !== $actor->id || $request->status !== BunkerJoinRequestStatus::Pending) {
            throw new InvalidCommunityOperation('This join request cannot be withdrawn.');
        }
        $request->update(['status' => BunkerJoinRequestStatus::Withdrawn, 'active_key' => null]);

        return $request;
    }

    /** @return array{invitation:BunkerInvitation,token:string} */
    public function invite(Bunker $bunker, User $actor, User $target, BunkerMembershipRole $role): array
    {
        if ($this->isMember($bunker, $target) || $this->isBanned($bunker, $target) || ! $this->interactionSafety->mayInviteToBunker($actor, $target, $bunker)) {
            throw new InvalidCommunityOperation('This user cannot be invited.');
        }
        $token = Str::random(64);
        $invitation = BunkerInvitation::query()->create(['bunker_id' => $bunker->id, 'invited_user_id' => $target->id, 'inviter_user_id' => $actor->id, 'proposed_role' => $role, 'token_hash' => hash('sha256', $token), 'status' => BunkerInvitationStatus::Pending, 'active_key' => $this->key($bunker->id, $target->id), 'sent_at' => now(), 'expires_at' => now()->addDays(7)]);
        BunkerInvitationCreated::dispatch($invitation->id, $bunker->id, $target->id);

        return ['invitation' => $invitation, 'token' => $token];
    }

    public function acceptInvitation(BunkerInvitation $invitation, User $actor, string $token): BunkerMembership
    {
        return DB::transaction(function () use ($invitation, $actor, $token): BunkerMembership {
            $locked = BunkerInvitation::query()->lockForUpdate()->findOrFail($invitation->id);
            if ($locked->invited_user_id !== $actor->id || $locked->status !== BunkerInvitationStatus::Pending || $locked->expires_at->isPast() || ! hash_equals($locked->token_hash, hash('sha256', $token)) || $this->isBanned($locked->bunker, $actor)) {
                throw new InvalidCommunityOperation('The invitation is invalid or expired.', 'invalid_invitation');
            }
            $locked->update(['status' => BunkerInvitationStatus::Accepted, 'active_key' => null, 'accepted_at' => now()]);

            return BunkerMembership::query()->create(['bunker_id' => $locked->bunker_id, 'user_id' => $actor->id, 'role' => $locked->proposed_role, 'status' => BunkerMembershipStatus::Active, 'active_key' => $this->key($locked->bunker_id, $actor->id), 'approved_by' => $locked->inviter_user_id, 'joined_at' => now()]);
        });
    }

    public function declineInvitation(BunkerInvitation $invitation, User $actor): BunkerInvitation
    {
        if ($invitation->invited_user_id !== $actor->id || $invitation->status !== BunkerInvitationStatus::Pending) {
            throw new InvalidCommunityOperation('This invitation cannot be declined.');
        }
        $invitation->update(['status' => BunkerInvitationStatus::Declined, 'active_key' => null, 'declined_at' => now()]);

        return $invitation;
    }

    public function revokeInvitation(BunkerInvitation $invitation, User $actor): BunkerInvitation
    {
        if ($invitation->status !== BunkerInvitationStatus::Pending || $this->localRole($invitation->bunker, $actor) === null) {
            throw new InvalidCommunityOperation('This invitation cannot be revoked.');
        }
        $invitation->update(['status' => BunkerInvitationStatus::Revoked, 'active_key' => null, 'revoked_at' => now()]);

        return $invitation;
    }

    public function updateMembership(BunkerMembership $membership, User $actor, BunkerMembershipRole $role, int $version): BunkerMembership
    {
        return DB::transaction(function () use ($membership, $actor, $role, $version): BunkerMembership {
            $locked = BunkerMembership::query()->lockForUpdate()->findOrFail($membership->id);
            $this->assertVersion($locked->lock_version, $version);
            if ($locked->role === BunkerMembershipRole::Owner || $role === BunkerMembershipRole::Owner || ! in_array($this->localRole($locked->bunker, $actor), [BunkerMembershipRole::Owner, BunkerMembershipRole::Administrator], true)) {
                throw new InvalidCommunityOperation('This local role change is not permitted.');
            }
            if ($role === BunkerMembershipRole::Administrator && $this->localRole($locked->bunker, $actor) !== BunkerMembershipRole::Owner) {
                throw new InvalidCommunityOperation('Only the owner may appoint an administrator.');
            }
            $locked->update(['role' => $role, 'lock_version' => $locked->lock_version + 1]);
            $this->audit->record('community.bunker_role_changed', $locked, ['bunker_id' => $locked->bunker_id, 'role' => $role->value], $actor);

            return $locked;
        });
    }

    public function endMembership(BunkerMembership $membership, User $actor): BunkerMembership
    {
        if ($membership->role === BunkerMembershipRole::Owner) {
            throw new InvalidCommunityOperation('The owner must transfer ownership or archive the Bunker before leaving.');
        }
        $isSelf = $membership->user_id === $actor->id;
        if (! $isSelf && ! in_array($this->localRole($membership->bunker, $actor), [BunkerMembershipRole::Owner, BunkerMembershipRole::Administrator], true)) {
            throw new InvalidCommunityOperation('This membership cannot be removed.');
        }
        $membership->update(['status' => $isSelf ? BunkerMembershipStatus::Left : BunkerMembershipStatus::Removed, 'active_key' => null, $isSelf ? 'left_at' : 'removed_at' => now(), 'lock_version' => $membership->lock_version + 1]);
        if (! $isSelf) {
            $this->audit->record('community.bunker_membership_removed', $membership, ['bunker_id' => $membership->bunker_id, 'user_id' => $membership->user_id], $actor);
        }

        return $membership;
    }

    public function ban(Bunker $bunker, User $actor, User $target, string $reasonCode, string $explanation, ?string $privateNote, ?string $expiresAt): BunkerBan
    {
        if ($bunker->owner_user_id === $target->id || $actor->id === $target->id) {
            throw new InvalidCommunityOperation('The Bunker owner or acting moderator cannot be locally banned.');
        }

        return DB::transaction(function () use ($bunker, $actor, $target, $reasonCode, $explanation, $privateNote, $expiresAt): BunkerBan {
            BunkerMembership::query()->where(['bunker_id' => $bunker->id, 'user_id' => $target->id, 'active_key' => $this->key($bunker->id, $target->id)])->update(['status' => BunkerMembershipStatus::Banned, 'active_key' => null, 'removed_at' => now()]);
            BunkerJoinRequest::query()->where(['bunker_id' => $bunker->id, 'user_id' => $target->id, 'status' => BunkerJoinRequestStatus::Pending])->update(['status' => BunkerJoinRequestStatus::Rejected, 'active_key' => null, 'reviewed_at' => now(), 'reviewed_by' => $actor->id]);
            BunkerInvitation::query()->where(['bunker_id' => $bunker->id, 'invited_user_id' => $target->id, 'status' => BunkerInvitationStatus::Pending])->update(['status' => BunkerInvitationStatus::Revoked, 'active_key' => null, 'revoked_at' => now()]);
            $ban = BunkerBan::query()->create(['bunker_id' => $bunker->id, 'user_id' => $target->id, 'issued_by' => $actor->id, 'reason_code' => $reasonCode, 'user_visible_explanation' => $this->plain($explanation), 'private_note' => $this->plain($privateNote), 'status' => BunkerBanStatus::Active, 'active_key' => $this->key($bunker->id, $target->id), 'effective_at' => now(), 'expires_at' => $expiresAt]);
            $this->audit->record('community.bunker_member_banned', $ban, ['bunker_id' => $bunker->id, 'user_id' => $target->id, 'reason_code' => $reasonCode], $actor);
            BunkerMemberBanned::dispatch($ban->id, $bunker->id, $target->id);

            return $ban;
        });
    }

    public function liftBan(BunkerBan $ban, User $actor): BunkerBan
    {
        if ($ban->status !== BunkerBanStatus::Active || ! in_array($this->localRole($ban->bunker, $actor), [BunkerMembershipRole::Owner, BunkerMembershipRole::Administrator, BunkerMembershipRole::Moderator], true)) {
            throw new InvalidCommunityOperation('This ban cannot be lifted.');
        }
        $ban->update(['status' => BunkerBanStatus::Lifted, 'active_key' => null, 'lifted_by' => $actor->id, 'lifted_at' => now()]);
        $this->audit->record('community.bunker_ban_lifted', $ban, ['bunker_id' => $ban->bunker_id, 'user_id' => $ban->user_id], $actor);

        return $ban;
    }

    /** @param array<string, mixed> $data */
    public function createRule(Bunker $bunker, User $actor, array $data): BunkerRule
    {
        return BunkerRule::query()->create(['bunker_id' => $bunker->id, 'title' => trim(strip_tags((string) $data['title'])), 'description' => $this->plain($data['description']), 'category' => $data['category'], 'position' => $data['position'], 'is_active' => true, 'created_by' => $actor->id, 'updated_by' => $actor->id]);
    }

    /** @param array<string, mixed> $data */
    public function updateRule(BunkerRule $rule, User $actor, array $data): BunkerRule
    {
        $this->assertVersion($rule->lock_version, (int) $data['lock_version']);
        $rule->update(['title' => isset($data['title']) ? trim(strip_tags((string) $data['title'])) : $rule->title, 'description' => array_key_exists('description', $data) ? $this->plain($data['description']) : $rule->description, 'category' => $data['category'] ?? $rule->category, 'is_active' => $data['is_active'] ?? $rule->is_active, 'updated_by' => $actor->id, 'lock_version' => $rule->lock_version + 1]);

        return $rule;
    }

    /** @param list<int> $ruleIds */
    public function reorderRules(Bunker $bunker, User $actor, array $ruleIds): void
    {
        DB::transaction(function () use ($bunker, $actor, $ruleIds): void {
            if ($bunker->rules()->whereIn('id', $ruleIds)->count() !== count($ruleIds)) {
                throw new InvalidCommunityOperation('The rule order contains an invalid rule.');
            } foreach ($ruleIds as $position => $id) {
                BunkerRule::query()->whereKey($id)->update(['position' => 1000 + $position, 'updated_by' => $actor->id]);
            } foreach ($ruleIds as $position => $id) {
                BunkerRule::query()->whereKey($id)->update(['position' => $position]);
            }
        });
    }

    public function isMember(Bunker $bunker, User|int $user): bool
    {
        $id = $user instanceof User ? $user->id : $user;

        return BunkerMembership::query()->where(['bunker_id' => $bunker->id, 'user_id' => $id, 'status' => BunkerMembershipStatus::Active])->whereNotNull('active_key')->exists();
    }

    public function isBanned(Bunker $bunker, User|int $user): bool
    {
        $id = $user instanceof User ? $user->id : $user;

        return BunkerBan::query()->where(['bunker_id' => $bunker->id, 'user_id' => $id, 'status' => BunkerBanStatus::Active])->where('effective_at', '<=', now())->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))->exists();
    }

    public function localRole(Bunker $bunker, User $user): ?BunkerMembershipRole
    {
        return BunkerMembership::query()->where(['bunker_id' => $bunker->id, 'user_id' => $user->id, 'status' => BunkerMembershipStatus::Active])->first()?->role;
    }

    private function denyScope(User $user, RestrictionScope $scope): void
    {
        if ($this->restrictions->hasUserScope($user, $scope)) {
            throw new InvalidCommunityOperation('This Community capability is restricted.', 'community_capability_restricted');
        }
    }

    private function assertVersion(int $actual, int $expected): void
    {
        if ($actual !== $expected) {
            throw new InvalidCommunityOperation('The record changed since it was loaded.', 'optimistic_lock_conflict');
        }
    }

    private function key(int $bunkerId, int $userId): string
    {
        return $bunkerId.':'.$userId;
    }

    private function plain(mixed $value): ?string
    {
        return $value === null ? null : trim(strip_tags((string) $value));
    }
}
