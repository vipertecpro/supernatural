<?php

use App\Domain\Editorial\Exceptions\OptimisticLockConflict;
use App\Domain\Moderation\Actions\ManageAppeals;
use App\Domain\Moderation\Actions\ManageModerationCases;
use App\Domain\Moderation\Exceptions\InvalidModerationOperation;
use App\Domain\Moderation\Services\RestrictionEvaluator;
use App\Enums\AppealDecisionType;
use App\Enums\AppealStatus;
use App\Enums\ContentRestrictionType;
use App\Enums\ModerationActionType;
use App\Enums\ModerationCaseStatus;
use App\Enums\ReportPriority;
use App\Enums\ReportStatus;
use App\Enums\RestrictionScope;
use App\Enums\RestrictionStatus;
use App\Enums\RoleName;
use App\Events\ReportClosed;
use App\Events\SearchProjectionRemovalRequested;
use App\Events\SearchProjectionRequested;
use App\Models\Appeal;
use App\Models\AppealDecision;
use App\Models\ContentRestriction;
use App\Models\ModerationAction;
use App\Models\ModerationCase;
use App\Models\Report;
use App\Models\SearchDocument;
use App\Models\Universe;
use App\Models\User;
use App\Models\UserRestriction;
use App\Models\Work;
use Database\Seeders\ReportCategorySeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->seed([RolePermissionSeeder::class, ReportCategorySeeder::class]);
    Event::fakeExcept([SearchProjectionRemovalRequested::class, SearchProjectionRequested::class]);
});

function openModerationCase(User $actor, ?User $subject = null, ?Work $work = null): ModerationCase
{
    $target = $work ?? Universe::factory()->published()->create();

    return app(ManageModerationCases::class)->open($actor, ['target_type' => $target->getMorphClass(), 'target_id' => $target->id, 'subject_user_id' => $subject?->id, 'priority' => ReportPriority::Normal->value]);
}

test('case assignment is explicit preserves history and rejects conflicts', function () {
    $assigner = editorialUser(RoleName::Moderator);
    $assignee = editorialUser(RoleName::Moderator);
    $subject = User::factory()->create();
    $case = openModerationCase($assigner, $subject);
    $action = app(ManageModerationCases::class);

    $first = $action->assign($case, $assignee, $assigner, 'Private allocation note.');
    $replacement = editorialUser(RoleName::Moderator);
    $second = $action->assign($case, $replacement, $assigner);

    expect($first->fresh()->status->value)->toBe('cancelled')
        ->and($first->fresh()->private_note)->toBe('Private allocation note.')
        ->and($second->active_primary_key)->toBe('primary')
        ->and(fn () => $action->assign($case, $subject, $assigner))->toThrow(InvalidModerationOperation::class);
});

test('case lifecycle requires valid transitions resolution and fresh versions', function () {
    $moderator = editorialUser(RoleName::Moderator);
    $case = openModerationCase($moderator);
    $action = app(ManageModerationCases::class);

    $case = $action->transition($case, ModerationCaseStatus::Triaged, 0, $moderator);
    expect(fn () => $action->transition($case, ModerationCaseStatus::Investigating, 0, $moderator))->toThrow(OptimisticLockConflict::class);
    $case = $action->transition($case, ModerationCaseStatus::Investigating, 1, $moderator);
    expect(fn () => $action->transition($case, ModerationCaseStatus::Dismissed, 2, $moderator))->toThrow(InvalidModerationOperation::class);
    $case = $action->transition($case, ModerationCaseStatus::Dismissed, 2, $moderator, ['resolution_code' => 'not_substantiated', 'user_visible_summary' => 'No action was required.']);

    expect($case->status)->toBe(ModerationCaseStatus::Dismissed)->and($case->resolution_code)->toBe('not_substantiated');
});

test('closing a case closes linked reports and dispatches a privacy safe reporter event', function () {
    Event::fake([ReportClosed::class]);
    $moderator = editorialUser(RoleName::Moderator);
    $target = Universe::factory()->published()->create();
    $report = Report::factory()->create(['target_type' => 'universe', 'target_id' => $target->id]);
    $manager = app(ManageModerationCases::class);
    $case = $manager->open($moderator, ['target_type' => 'universe', 'target_id' => $target->id, 'priority' => 'normal', 'report_ids' => [$report->id]]);
    $case = $manager->transition($case, ModerationCaseStatus::Investigating, 0, $moderator);
    $case = $manager->transition($case, ModerationCaseStatus::Dismissed, 1, $moderator, ['resolution_code' => 'not_substantiated']);
    $manager->transition($case, ModerationCaseStatus::Closed, 2, $moderator, ['resolution_code' => 'not_substantiated']);

    expect($report->fresh()->status)->toBe(ReportStatus::Closed);
    Event::assertDispatched(ReportClosed::class, fn (ReportClosed $event): bool => $event->reportId === $report->id && $event->reporterUserId === $report->reporter_user_id);
});

test('user restrictions are scoped stacked expiring and liftable', function () {
    $moderator = editorialUser(RoleName::Moderator);
    $subject = User::factory()->create();
    $case = openModerationCase($moderator, $subject);
    $manager = app(ManageModerationCases::class);
    $manager->applyAction($case, $moderator, ['type' => ModerationActionType::UserRestricted->value, 'target_user_id' => $subject->id, 'reason_code' => 'report_abuse', 'user_visible_explanation' => 'Report submission is temporarily restricted.', 'expires_at' => now()->addDay()->toISOString(), 'restriction_scopes' => [RestrictionScope::ReportSubmission->value]]);
    $restriction = UserRestriction::query()->firstOrFail();
    $evaluator = app(RestrictionEvaluator::class);

    expect($evaluator->hasUserScope($subject, RestrictionScope::ReportSubmission))->toBeTrue()
        ->and($evaluator->hasUserScope($subject, RestrictionScope::CatalogContribution))->toBeFalse();

    $manager->liftUserRestriction($restriction, $moderator);
    expect($restriction->fresh()->status)->toBe(RestrictionStatus::Lifted)
        ->and($evaluator->hasUserScope($subject, RestrictionScope::ReportSubmission))->toBeFalse();

    $expired = UserRestriction::factory()->create(['user_id' => $subject->id, 'expires_at' => now()->subMinute()]);
    $expired->scopes()->create(['scope' => RestrictionScope::CatalogContribution]);
    expect($evaluator->hasUserScope($subject, RestrictionScope::CatalogContribution))->toBeFalse();
});

test('capability restriction blocks direct report API but not private journey reads', function () {
    $moderator = editorialUser(RoleName::Moderator);
    $subject = User::factory()->create();
    $case = openModerationCase($moderator, $subject);
    app(ManageModerationCases::class)->applyAction($case, $moderator, ['type' => 'user_restricted', 'target_user_id' => $subject->id, 'reason_code' => 'report_abuse', 'user_visible_explanation' => 'Report submission is restricted.', 'expires_at' => now()->addDay()->toISOString(), 'restriction_scopes' => ['report_submission']]);
    $target = Universe::factory()->published()->create();

    $this->actingAs($subject)->postJson('/api/v1/reports', ['category' => 'other', 'target_type' => 'universe', 'target_id' => $target->id, 'explanation' => 'Blocked attempt.'])->assertForbidden()->assertJsonPath('error.code', 'capability_restricted');
    $this->actingAs($subject)->getJson('/api/v1/me/journeys')->assertSuccessful();
});

test('platform suspension preserves notification and appeal access only', function () {
    $administrator = editorialUser(RoleName::Administrator);
    $subject = User::factory()->create();
    $case = openModerationCase($administrator, $subject);
    app(ManageModerationCases::class)->applyAction($case, $administrator, ['type' => 'platform_suspended', 'target_user_id' => $subject->id, 'reason_code' => 'serious_violation', 'user_visible_explanation' => 'Platform access is suspended.']);

    $this->actingAs($subject)->getJson('/api/v1/me/journeys')->assertForbidden()->assertJsonPath('error.code', 'platform_access_restricted');
    $this->actingAs($subject)->getJson('/api/v1/me/notifications')->assertSuccessful();
    $this->actingAs($subject)->getJson('/api/v1/me/appeals')->assertSuccessful();
});

test('content restriction hides public detail removes search and lifting restores eligibility', function () {
    $moderator = editorialUser(RoleName::Moderator);
    $work = Work::factory()->published()->create();
    SearchDocument::factory()->create(['source_type' => 'work', 'source_id' => $work->id, 'universe_id' => $work->universe_id]);
    $case = openModerationCase($moderator, work: $work);
    $manager = app(ManageModerationCases::class);
    $manager->applyAction($case, $moderator, ['type' => ModerationActionType::ContentHidden->value, 'target_type' => 'work', 'target_id' => $work->id, 'reason_code' => 'policy_review', 'user_visible_explanation' => 'This item is temporarily unavailable.', 'expires_at' => now()->addDay()->toISOString(), 'content_restriction_type' => ContentRestrictionType::HiddenFromPublic->value]);
    $restriction = ContentRestriction::query()->firstOrFail();

    expect(Work::query()->visibleToPublic()->whereKey($work)->exists())->toBeFalse()
        ->and(SearchDocument::query()->where('source_type', 'work')->where('source_id', $work->id)->exists())->toBeFalse();
    $this->getJson("/api/v1/works/{$work->id}")->assertNotFound();

    $manager->liftContentRestriction($restriction, $moderator);
    expect(Work::query()->visibleToPublic()->whereKey($work)->exists())->toBeTrue()
        ->and($restriction->fresh()->status)->toBe(RestrictionStatus::Lifted);
});

test('editing freeze is enforced by backend policy', function () {
    $administrator = editorialUser(RoleName::Administrator);
    $work = Work::factory()->published()->create();
    $case = openModerationCase($administrator, work: $work);
    app(ManageModerationCases::class)->applyAction($case, $administrator, ['type' => 'content_editing_frozen', 'target_type' => 'work', 'target_id' => $work->id, 'reason_code' => 'integrity_review', 'user_visible_explanation' => 'Editing is frozen during review.', 'expires_at' => now()->addDay()->toISOString(), 'content_restriction_type' => 'editing_frozen']);

    expect($administrator->can('update', $work))->toBeFalse();
});

test('appeals enforce ownership uniqueness reviewer separation and immutable original actions', function () {
    $moderator = editorialUser(RoleName::Moderator);
    $reviewer = editorialUser(RoleName::Moderator);
    $subject = User::factory()->create();
    $case = openModerationCase($moderator, $subject);
    $action = app(ManageModerationCases::class)->applyAction($case, $moderator, ['type' => 'user_restricted', 'target_user_id' => $subject->id, 'reason_code' => 'temporary_limit', 'user_visible_explanation' => 'A temporary restriction applies.', 'expires_at' => now()->addDay()->toISOString(), 'restriction_scopes' => ['catalog_contribution']]);
    $appeals = app(ManageAppeals::class);

    expect(fn () => $appeals->submit(User::factory()->create(), $action, 'I should not be able to appeal this action.'))->toThrow(InvalidModerationOperation::class);
    $appeal = $appeals->submit($subject, $action, 'Please review this restriction using the added context.');
    expect(fn () => $appeals->submit($subject, $action, 'Duplicate appeal attempt with more words.'))->toThrow(InvalidModerationOperation::class)
        ->and(fn () => $appeals->decide($appeal, $moderator, ['type' => 'upheld', 'user_visible_explanation' => 'Conflict.']))->toThrow(InvalidModerationOperation::class);

    $decision = $appeals->decide($appeal, $reviewer, ['type' => AppealDecisionType::Overturned->value, 'user_visible_explanation' => 'The restriction was overturned after independent review.']);
    expect($decision->type)->toBe(AppealDecisionType::Overturned)
        ->and($appeal->fresh()->status)->toBe(AppealStatus::Decided)
        ->and(UserRestriction::query()->firstOrFail()->status)->toBe(RestrictionStatus::Lifted)
        ->and(ModerationAction::query()->whereKey($action)->exists())->toBeTrue();
});

test('moderation actions and appeal decisions are immutable', function () {
    $action = ModerationAction::factory()->create();
    $appeal = Appeal::factory()->create(['moderation_action_id' => $action->id, 'moderation_case_id' => $action->moderation_case_id]);
    $decision = AppealDecision::factory()->create(['appeal_id' => $appeal->id]);

    expect(fn () => $action->update(['reason_code' => 'changed']))->toThrow(LogicException::class)
        ->and(fn () => $decision->delete())->toThrow(LogicException::class);
});
