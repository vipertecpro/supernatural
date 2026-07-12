<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Community\Actions\ManageBunkers;
use App\Enums\BunkerMembershipRole;
use App\Enums\BunkerStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreBunkerBanRequest;
use App\Http\Requests\Api\V1\StoreBunkerRequest;
use App\Http\Requests\Api\V1\StoreBunkerRuleRequest;
use App\Http\Requests\Api\V1\StoreInvitationRequest;
use App\Http\Requests\Api\V1\StoreJoinRequest;
use App\Http\Requests\Api\V1\UpdateBunkerRequest;
use App\Http\Resources\Api\V1\BunkerResource;
use App\Models\Bunker;
use App\Models\BunkerBan;
use App\Models\BunkerCategory;
use App\Models\BunkerInvitation;
use App\Models\BunkerJoinRequest;
use App\Models\BunkerMembership;
use App\Models\BunkerRule;
use App\Models\Universe;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BunkerController extends Controller
{
    public function categories(Request $request): JsonResponse
    {
        return ApiResponse::success($request, BunkerCategory::query()->where('is_active', true)->orderBy('position')->get(['id', 'key', 'name', 'description'])->toArray());
    }

    public function index(Request $request, Universe $universe): JsonResponse
    {
        $paginator = Bunker::query()->with('categories')->withoutActivePublicRestriction()->where(['universe_id' => $universe->id, 'visibility' => 'public', 'status' => 'published'])->orderByDesc('published_at')->orderByDesc('id')->cursorPaginate(min(max($request->integer('page.size', 20), 1), 50));

        return ApiResponse::cursor($request, BunkerResource::collection($paginator->getCollection())->resolve($request), $paginator);
    }

    public function show(Request $request, Bunker $bunker): JsonResponse
    {
        if ($bunker->visibility->value !== 'public') {
            abort_unless($request->user() !== null && Gate::forUser($request->user())->allows('view', $bunker), 404);
        } else {
            abort_unless($bunker->status === BunkerStatus::Published, 404);
        }

        return ApiResponse::success($request, (new BunkerResource($bunker->load('categories')))->resolve($request));
    }

    public function rules(Request $request, Bunker $bunker): JsonResponse
    {
        $this->show($request, $bunker);

        return ApiResponse::success($request, $bunker->rules()->where('is_active', true)->orderBy('position')->get(['id', 'title', 'description', 'category', 'position'])->toArray());
    }

    public function members(Request $request, Bunker $bunker): JsonResponse
    {
        abort_unless($request->user() !== null && Gate::forUser($request->user())->allows('view', $bunker), 404);
        $paginator = $bunker->memberships()->where('status', 'active')->whereNotNull('active_key')->orderByDesc('joined_at')->orderByDesc('id')->cursorPaginate(min(max($request->integer('page.size', 20), 1), 50));

        return ApiResponse::cursor($request, $paginator->getCollection()->map(fn (BunkerMembership $membership): array => ['id' => $membership->id, 'user_id' => $membership->user_id, 'role' => $membership->role->value, 'joined_at' => $membership->joined_at?->toISOString()])->all(), $paginator);
    }

    public function myMemberships(Request $request): JsonResponse
    {
        $paginator = BunkerMembership::query()->with('bunker')->where(['user_id' => $request->user()->id, 'status' => 'active'])->whereNotNull('active_key')->orderByDesc('joined_at')->orderByDesc('id')->cursorPaginate(min(max($request->integer('page.size', 20), 1), 50));

        return ApiResponse::cursor($request, $paginator->getCollection()->map(fn (BunkerMembership $membership): array => ['id' => $membership->id, 'bunker' => (new BunkerResource($membership->bunker))->resolve($request), 'role' => $membership->role->value, 'lock_version' => $membership->lock_version])->all(), $paginator);
    }

    public function store(StoreBunkerRequest $request, Universe $universe, ManageBunkers $action): JsonResponse
    {
        Gate::authorize('create', Bunker::class);

        return ApiResponse::success($request, (new BunkerResource($action->create($request->user(), $universe->id, $request->validated())))->resolve($request), status: 201);
    }

    public function update(UpdateBunkerRequest $request, Bunker $bunker, ManageBunkers $action): JsonResponse
    {
        Gate::authorize('update', $bunker);

        return ApiResponse::success($request, (new BunkerResource($action->update($bunker, $request->user(), $request->validated())))->resolve($request));
    }

    public function publish(UpdateBunkerRequest $request, Bunker $bunker, ManageBunkers $action): JsonResponse
    {
        Gate::authorize('update', $bunker);

        return ApiResponse::success($request, (new BunkerResource($action->transition($bunker, $request->user(), BunkerStatus::Published, $request->integer('lock_version'))))->resolve($request));
    }

    public function archive(UpdateBunkerRequest $request, Bunker $bunker, ManageBunkers $action): JsonResponse
    {
        Gate::authorize('update', $bunker);

        return ApiResponse::success($request, (new BunkerResource($action->transition($bunker, $request->user(), BunkerStatus::Archived, $request->integer('lock_version'))))->resolve($request));
    }

    public function transfer(UpdateBunkerRequest $request, Bunker $bunker, ManageBunkers $action): JsonResponse
    {
        Gate::authorize('transferOwnership', $bunker);
        $membership = BunkerMembership::query()->findOrFail($request->integer('membership_id'));

        return ApiResponse::success($request, (new BunkerResource($action->transferOwnership($bunker, $request->user(), $membership, $request->integer('lock_version'))))->resolve($request));
    }

    public function join(StoreJoinRequest $request, Bunker $bunker, ManageBunkers $action): JsonResponse
    {
        $join = $action->requestJoin($bunker, $request->user(), $request->validated('message'));

        return ApiResponse::success($request, ['id' => $join->id, 'status' => $join->status->value], status: 201);
    }

    public function approveJoin(StoreJoinRequest $request, BunkerJoinRequest $joinRequest, ManageBunkers $action): JsonResponse
    {
        return $this->decideJoin($request, $joinRequest, $action, true);
    }

    public function rejectJoin(StoreJoinRequest $request, BunkerJoinRequest $joinRequest, ManageBunkers $action): JsonResponse
    {
        return $this->decideJoin($request, $joinRequest, $action, false);
    }

    public function withdrawJoin(Request $request, BunkerJoinRequest $joinRequest, ManageBunkers $action): JsonResponse
    {
        $join = $action->withdrawJoin($joinRequest, $request->user());

        return ApiResponse::success($request, ['id' => $join->id, 'status' => $join->status->value]);
    }

    public function invite(StoreInvitationRequest $request, Bunker $bunker, ManageBunkers $action): JsonResponse
    {
        Gate::authorize('moderate', $bunker);
        $result = $action->invite($bunker, $request->user(), User::query()->findOrFail($request->integer('invited_user_id')), BunkerMembershipRole::from($request->string('proposed_role')->toString()));

        return ApiResponse::success($request, ['id' => $result['invitation']->id, 'status' => $result['invitation']->status->value, 'acceptance_token' => $result['token']], status: 201);
    }

    public function acceptInvitation(StoreInvitationRequest $request, BunkerInvitation $invitation, ManageBunkers $action): JsonResponse
    {
        $membership = $action->acceptInvitation($invitation, $request->user(), $request->string('token')->toString());

        return ApiResponse::success($request, ['membership_id' => $membership->id, 'role' => $membership->role->value]);
    }

    public function declineInvitation(StoreInvitationRequest $request, BunkerInvitation $invitation, ManageBunkers $action): JsonResponse
    {
        $record = $action->declineInvitation($invitation, $request->user());

        return ApiResponse::success($request, ['id' => $record->id, 'status' => $record->status->value]);
    }

    public function revokeInvitation(StoreInvitationRequest $request, BunkerInvitation $invitation, ManageBunkers $action): JsonResponse
    {
        $record = $action->revokeInvitation($invitation, $request->user());

        return ApiResponse::success($request, ['id' => $record->id, 'status' => $record->status->value]);
    }

    public function updateMembership(UpdateBunkerRequest $request, BunkerMembership $membership, ManageBunkers $action): JsonResponse
    {
        Gate::authorize('update', $membership->bunker);
        $record = $action->updateMembership($membership, $request->user(), BunkerMembershipRole::from($request->string('role')->toString()), $request->integer('lock_version'));

        return ApiResponse::success($request, ['id' => $record->id, 'role' => $record->role->value, 'lock_version' => $record->lock_version]);
    }

    public function removeMembership(Request $request, BunkerMembership $membership, ManageBunkers $action): JsonResponse
    {
        $record = $action->endMembership($membership, $request->user());

        return ApiResponse::success($request, ['id' => $record->id, 'status' => $record->status->value]);
    }

    public function ban(StoreBunkerBanRequest $request, Bunker $bunker, ManageBunkers $action): JsonResponse
    {
        Gate::authorize('moderate', $bunker);
        $ban = $action->ban($bunker, $request->user(), User::query()->findOrFail($request->integer('user_id')), $request->string('reason_code')->toString(), $request->string('user_visible_explanation')->toString(), $request->validated('private_note'), $request->validated('expires_at'));

        return ApiResponse::success($request, ['id' => $ban->id, 'status' => $ban->status->value], status: 201);
    }

    public function liftBan(Request $request, BunkerBan $ban, ManageBunkers $action): JsonResponse
    {
        $record = $action->liftBan($ban, $request->user());

        return ApiResponse::success($request, ['id' => $record->id, 'status' => $record->status->value]);
    }

    public function storeRule(StoreBunkerRuleRequest $request, Bunker $bunker, ManageBunkers $action): JsonResponse
    {
        Gate::authorize('update', $bunker);
        $rule = $action->createRule($bunker, $request->user(), $request->validated());

        return ApiResponse::success($request, ['id' => $rule->id, 'title' => $rule->title, 'position' => $rule->position], status: 201);
    }

    public function updateRule(UpdateBunkerRequest $request, BunkerRule $rule, ManageBunkers $action): JsonResponse
    {
        Gate::authorize('update', $rule->bunker);
        $record = $action->updateRule($rule, $request->user(), $request->validated());

        return ApiResponse::success($request, ['id' => $record->id, 'title' => $record->title, 'lock_version' => $record->lock_version]);
    }

    public function deleteRule(Request $request, BunkerRule $rule): JsonResponse
    {
        Gate::authorize('update', $rule->bunker);
        $rule->update(['is_active' => false, 'updated_by' => $request->user()->id, 'lock_version' => $rule->lock_version + 1]);

        return ApiResponse::success($request, ['id' => $rule->id, 'active' => false]);
    }

    public function reorderRules(UpdateBunkerRequest $request, Bunker $bunker, ManageBunkers $action): JsonResponse
    {
        Gate::authorize('update', $bunker);
        $action->reorderRules($bunker, $request->user(), $request->validated('rule_ids'));

        return ApiResponse::success($request, ['reordered' => true]);
    }

    private function decideJoin(StoreJoinRequest $request, BunkerJoinRequest $joinRequest, ManageBunkers $action, bool $approve): JsonResponse
    {
        Gate::authorize('moderate', $joinRequest->bunker);
        $join = $action->decideJoin($joinRequest, $request->user(), $approve, $request->validated('decision_explanation'));

        return ApiResponse::success($request, ['id' => $join->id, 'status' => $join->status->value]);
    }
}
