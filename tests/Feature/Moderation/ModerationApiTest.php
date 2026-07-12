<?php

use App\Domain\Moderation\Actions\ManageModerationCases;
use App\Domain\Moderation\Exceptions\InvalidModerationOperation;
use App\Enums\ReportPriority;
use App\Enums\RoleName;
use App\Models\ModerationCase;
use App\Models\Permission;
use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\Universe;
use App\Models\User;
use Database\Seeders\ReportCategorySeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->seed([RolePermissionSeeder::class, ReportCategorySeeder::class]);
    Event::fake();
});

function openApiModerationCase(User $actor, ?User $subject = null): ModerationCase
{
    $target = Universe::factory()->published()->create();

    return app(ManageModerationCases::class)->open($actor, ['target_type' => 'universe', 'target_id' => $target->id, 'subject_user_id' => $subject?->id, 'priority' => 'normal']);
}

test('moderation case API requires explicit permissions and never exposes private assignment notes', function () {
    $moderator = editorialUser(RoleName::Moderator);
    $fan = editorialUser(RoleName::Fan);
    $target = Universe::factory()->published()->create();
    $payload = ['target_type' => 'universe', 'target_id' => $target->id, 'priority' => ReportPriority::Normal->value];

    $this->actingAs($fan)->postJson('/api/v1/moderation/cases', $payload)->assertForbidden();
    $response = $this->actingAs($moderator)->postJson('/api/v1/moderation/cases', $payload)->assertCreated()->assertJsonMissingPath('data.private_note');
    $caseId = $response->json('data.id');
    $this->actingAs($moderator)->postJson("/api/v1/moderation/cases/{$caseId}/assign", ['moderator_user_id' => $moderator->id, 'private_note' => 'This must remain private.'])->assertCreated();
    $this->actingAs($moderator)->getJson("/api/v1/moderation/cases/{$caseId}")->assertSuccessful()->assertDontSee('This must remain private.');
});

test('reporters and subjects cannot access moderation case resources', function () {
    $moderator = editorialUser(RoleName::Moderator);
    $reporter = User::factory()->create();
    $subject = User::factory()->create();
    $target = Universe::factory()->published()->create();
    $report = Report::factory()->create(['reporter_user_id' => $reporter->id, 'target_type' => 'universe', 'target_id' => $target->id]);
    $case = app(ManageModerationCases::class)->open($moderator, ['target_type' => 'universe', 'target_id' => $target->id, 'subject_user_id' => $subject->id, 'priority' => 'normal', 'report_ids' => [$report->id]]);

    $this->actingAs($reporter)->getJson("/api/v1/moderation/cases/{$case->public_id}")->assertForbidden();
    $this->actingAs($subject)->getJson("/api/v1/moderation/cases/{$case->public_id}")->assertForbidden();
});

test('rights cases cannot be assigned without separate rights authority', function () {
    $moderator = editorialUser(RoleName::Moderator);
    $administrator = editorialUser(RoleName::Administrator);
    $target = Universe::factory()->published()->create();
    $category = ReportCategory::query()->where('key', 'copyright_or_ownership')->firstOrFail();
    $report = Report::factory()->create(['report_category_id' => $category->id, 'target_type' => 'universe', 'target_id' => $target->id]);
    $case = app(ManageModerationCases::class)->open($moderator, ['target_type' => 'universe', 'target_id' => $target->id, 'priority' => 'high', 'report_ids' => [$report->id]]);

    expect(fn () => app(ManageModerationCases::class)->assign($case, $moderator, $moderator))->toThrow(InvalidModerationOperation::class);
    $assignment = app(ManageModerationCases::class)->assign($case, $administrator, $moderator);
    expect($assignment->moderator_user_id)->toBe($administrator->id);
});

test('moderation action API omits private notes and reporter identity', function () {
    $moderator = editorialUser(RoleName::Moderator);
    $subject = User::factory()->create();
    $case = openApiModerationCase($moderator, $subject);
    $payload = ['type' => 'warning_issued', 'target_user_id' => $subject->id, 'reason_code' => 'policy_warning', 'user_visible_explanation' => 'A warning was issued.', 'private_moderator_note' => 'Internal analysis only.'];

    $this->actingAs($moderator)->postJson("/api/v1/moderation/cases/{$case->public_id}/actions", $payload)->assertCreated()->assertDontSee('Internal analysis only.');
    $this->actingAs($moderator)->getJson("/api/v1/moderation/cases/{$case->public_id}")->assertSuccessful()->assertDontSee('Internal analysis only.');
});

test('moderation responses retain stable request identifiers and bounded cursor metadata', function () {
    $moderator = editorialUser(RoleName::Moderator);
    ModerationCase::factory()->count(3)->create();

    $this->actingAs($moderator)->withHeader('X-Request-ID', 'prompt-9-request')->getJson('/api/v1/moderation/cases?page[size]=2')
        ->assertSuccessful()
        ->assertJsonPath('meta.request_id', 'prompt-9-request')
        ->assertJsonPath('meta.pagination.per_page', 2)
        ->assertJsonCount(2, 'data');
});

test('permission and category seeders remain idempotent', function () {
    $this->seed([RolePermissionSeeder::class, ReportCategorySeeder::class]);
    $this->seed([RolePermissionSeeder::class, ReportCategorySeeder::class]);

    expect(ReportCategory::query()->count())->toBe(12)
        ->and(Permission::query()->where('name', 'moderation.appeals.review')->count())->toBe(1);
});
