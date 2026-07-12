<?php

use App\Domain\Moderation\Actions\ManageReports;
use App\Domain\Moderation\Exceptions\InvalidModerationOperation;
use App\Enums\ReportEvidenceType;
use App\Enums\ReportStatus;
use App\Events\ReportSubmitted;
use App\Models\ContentRestriction;
use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\Universe;
use App\Models\User;
use App\Models\UserRestriction;
use Database\Seeders\ReportCategorySeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->seed([RolePermissionSeeder::class, ReportCategorySeeder::class]);
});

test('report categories seed idempotently with stable controlled keys', function () {
    $this->seed(ReportCategorySeeder::class);

    expect(ReportCategory::query()->count())->toBe(12)
        ->and(ReportCategory::query()->where('key', 'copyright_or_ownership')->value('rights_review_required'))->toBeTruthy()
        ->and(ReportCategory::query()->where('key', 'privacy_violation')->value('safety_review_required'))->toBeTruthy();
});

test('a verified user submits a private report without automatic enforcement', function () {
    Event::fake([ReportSubmitted::class]);
    $reporter = User::factory()->create();
    $target = Universe::factory()->published()->create();

    $report = app(ManageReports::class)->submit($reporter, ['category' => 'other', 'target_type' => 'universe', 'target_id' => $target->id, 'explanation' => '<b>A bounded concern</b>'], 'request-1');

    expect($report->status)->toBe(ReportStatus::Submitted)
        ->and($report->explanation)->toBe('A bounded concern')
        ->and($report->moderation_case_id)->toBeNull()
        ->and(UserRestriction::query()->count())->toBe(0)
        ->and(ContentRestriction::query()->count())->toBe(0);
    Event::assertDispatched(ReportSubmitted::class, fn (ReportSubmitted $event): bool => $event->reportId === $report->id && $event->reporterUserId === $reporter->id);
});

test('duplicate reports remain distinct and link to the earlier report', function () {
    Event::fake([ReportSubmitted::class]);
    $reporter = User::factory()->create();
    $target = Universe::factory()->published()->create();
    $data = ['category' => 'spam', 'target_type' => 'universe', 'target_id' => $target->id, 'explanation' => 'Repeated misleading submission.'];

    $first = app(ManageReports::class)->submit($reporter, $data, 'first');
    $second = app(ManageReports::class)->submit($reporter, $data, 'second');

    expect($second->id)->not->toBe($first->id)
        ->and($second->status)->toBe(ReportStatus::Linked)
        ->and($second->duplicate_of_report_id)->toBe($first->id);
});

test('unsupported and inaccessible private targets are rejected', function () {
    $reporter = User::factory()->create();
    $draft = Universe::factory()->create();

    expect(fn () => app(ManageReports::class)->submit($reporter, ['category' => 'other', 'target_type' => 'universe', 'target_id' => $draft->id, 'explanation' => 'Not publicly accessible.'], null))->toThrow(InvalidModerationOperation::class)
        ->and(fn () => app(ManageReports::class)->submit($reporter, ['category' => 'other', 'target_type' => 'personal_note', 'target_id' => 1, 'explanation' => 'Unsupported target.'], null))->toThrow(InvalidModerationOperation::class);
});

test('report withdrawal and evidence rules preserve resolved history', function () {
    Event::fake([ReportSubmitted::class]);
    $reporter = User::factory()->create();
    $target = Universe::factory()->published()->create();
    $action = app(ManageReports::class);
    $report = $action->submit($reporter, ['category' => 'other', 'target_type' => 'universe', 'target_id' => $target->id, 'explanation' => 'A reviewable concern.'], null);
    $evidence = $action->addEvidence($report, $reporter, ['type' => ReportEvidenceType::ExternalUrl->value, 'external_url' => 'https://example.test/evidence', 'description' => '<script>bad()</script>Context']);
    $withdrawn = $action->withdraw($report, $reporter);

    expect($evidence->external_url)->toBe('https://example.test/evidence')
        ->and($evidence->description)->toBe('bad()Context')
        ->and($withdrawn->status)->toBe(ReportStatus::Withdrawn)
        ->and(fn () => $action->addEvidence($withdrawn, $reporter, ['type' => ReportEvidenceType::Explanation->value, 'description' => 'Too late']))->toThrow(InvalidModerationOperation::class);
});

test('report API requires authentication verification ownership and a strict rate limit', function () {
    $target = Universe::factory()->published()->create();
    $payload = ['category' => 'other', 'target_type' => 'universe', 'target_id' => $target->id, 'explanation' => 'A sufficiently clear report explanation.'];

    $this->postJson('/api/v1/reports', $payload)->assertUnauthorized();
    $this->actingAs(User::factory()->unverified()->create())->postJson('/api/v1/reports', $payload)->assertForbidden();

    $owner = User::factory()->create();
    $response = $this->actingAs($owner)->postJson('/api/v1/reports', $payload)->assertCreated()->assertJsonMissingPath('data.reporter_user_id');
    $reportId = $response->json('data.id');
    $this->actingAs(User::factory()->create())->getJson("/api/v1/me/reports/{$reportId}")->assertNotFound();
    $this->actingAs($owner)->getJson("/api/v1/me/reports/{$reportId}")->assertSuccessful()->assertJsonPath('meta.api_version', 'v1');

    for ($attempt = 0; $attempt < 4; $attempt++) {
        $this->actingAs($owner)->postJson('/api/v1/reports', $payload)->assertCreated();
    }
    $this->actingAs($owner)->postJson('/api/v1/reports', $payload)->assertTooManyRequests();
});

test('report evidence API rejects unsafe schemes and executable markup', function () {
    $report = Report::factory()->create();

    $this->actingAs($report->reporter)->postJson("/api/v1/me/reports/{$report->id}/evidence", ['type' => 'external_url', 'external_url' => 'http://example.test/file'])->assertUnprocessable();
    $this->actingAs($report->reporter)->postJson("/api/v1/me/reports/{$report->id}/evidence", ['type' => 'explanation', 'description' => '<iframe src="https://example.test"></iframe>'])->assertUnprocessable();
});
