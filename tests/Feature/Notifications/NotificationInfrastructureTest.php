<?php

use App\Domain\Moderation\Exceptions\InvalidModerationOperation;
use App\Domain\Notifications\Actions\CreateUserNotification;
use App\Domain\Notifications\Actions\ManageNotificationDeliveries;
use App\Domain\Notifications\Services\NotificationRenderer;
use App\Domain\Notifications\Services\NotificationTypeRegistry;
use App\Enums\ContentRestrictionType;
use App\Enums\NotificationChannel;
use App\Enums\NotificationDeliveryStatus;
use App\Enums\NotificationPreferenceState;
use App\Events\AppealDecided;
use App\Events\ViewingProgressUpdated;
use App\Listeners\CreateDomainNotification;
use App\Models\Appeal;
use App\Models\ContentRestriction;
use App\Models\ModerationAction;
use App\Models\NotificationDelivery;
use App\Models\NotificationPreference;
use App\Models\Report;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\UserRestriction;
use App\Models\ViewingProgressEvent;
use App\Models\Work;
use App\Notifications\StableNotificationMail;
use Database\Seeders\ReportCategorySeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->seed([RolePermissionSeeder::class, ReportCategorySeeder::class]);
    Notification::fake();
});

test('notification registry exposes stable versioned types and rejects unsafe payloads', function () {
    $registry = app(NotificationTypeRegistry::class);
    $definition = $registry->definition('moderation.action.applied');

    expect($definition['schema_version'])->toBe(1)
        ->and($definition['mandatory_in_app'])->toBeTrue()
        ->and($registry->keys())->toContain('journey.completed', 'rewatch.completed')
        ->and(fn () => $registry->validatePayload('moderation.action.applied', ['action_id' => 1, 'case_public_id' => 'case', 'reason_code' => 'reason', 'private_note' => 'secret']))->toThrow(InvalidModerationOperation::class)
        ->and(fn () => $registry->definition('arbitrary.client.type'))->toThrow(InvalidModerationOperation::class);
});

test('notification creation is idempotent and records separate channel deliveries', function () {
    $user = User::factory()->create();
    $action = app(CreateUserNotification::class);
    $payload = ['report_id' => 42, 'status' => 'submitted'];

    $first = $action->execute($user, 'moderation.report.received', 'event:42', $payload);
    $second = $action->execute($user, 'moderation.report.received', 'event:42', $payload);

    expect($second->id)->toBe($first->id)
        ->and(UserNotification::query()->count())->toBe(1)
        ->and(NotificationDelivery::query()->count())->toBe(2)
        ->and(NotificationDelivery::query()->where('channel', NotificationChannel::InApp)->firstOrFail()->status)->toBe(NotificationDeliveryStatus::Delivered)
        ->and(NotificationDelivery::query()->where('channel', NotificationChannel::Email)->firstOrFail()->status)->toBe(NotificationDeliveryStatus::Queued);
    Notification::assertSentTo($user, StableNotificationMail::class);
});

test('optional email preferences create a safe suppressed delivery', function () {
    $user = User::factory()->create();
    NotificationPreference::factory()->create(['user_id' => $user->id, 'type' => 'moderation.report.received', 'channel' => NotificationChannel::Email, 'state' => NotificationPreferenceState::Disabled]);

    app(CreateUserNotification::class)->execute($user, 'moderation.report.received', 'suppressed:1', ['report_id' => 1, 'status' => 'submitted']);

    $delivery = NotificationDelivery::query()->where('channel', NotificationChannel::Email)->firstOrFail();
    expect($delivery->status)->toBe(NotificationDeliveryStatus::Suppressed)
        ->and($delivery->failure_code)->toBe('user_preference')
        ->and($delivery->provider_response_code)->toBeNull();
    Notification::assertNothingSent();
});

test('renderer uses recipient context and trusted route keys without raw payload exposure', function () {
    $user = User::factory()->create();
    $reportNotice = UserNotification::factory()->create(['user_id' => $user->id]);
    $rendered = app(NotificationRenderer::class)->render($reportNotice);

    expect($rendered['rendering'])->toBe('detailed')
        ->and($rendered['action_route_key'])->toBe('api.v1.me.reports.show')
        ->and($rendered['action_route_params'])->toBe(['report' => $reportNotice->payload['report_id']]);

    $work = Work::factory()->published()->create();
    $spoilerNotice = UserNotification::factory()->create(['user_id' => $user->id, 'type' => 'moderation.action.applied', 'subject_type' => 'work', 'subject_id' => $work->id, 'payload' => ['action_id' => 1, 'case_public_id' => 'case', 'reason_code' => 'reason']]);
    expect(app(NotificationRenderer::class)->render($spoilerNotice)['rendering'])->toBe('redacted');

    ContentRestriction::factory()->create(['target_type' => 'work', 'target_id' => $work->id, 'type' => ContentRestrictionType::HiddenFromPublic]);
    expect(app(NotificationRenderer::class)->render($spoilerNotice->fresh())['rendering'])->toBe('unavailable');
});

test('deleted notification subjects render a safe unavailable fallback', function () {
    $notice = UserNotification::factory()->create(['subject_type' => 'work', 'subject_id' => 999999]);
    $rendered = app(NotificationRenderer::class)->render($notice);

    expect($rendered['rendering'])->toBe('unavailable')
        ->and($rendered['body'])->toBe('The referenced item is no longer available.')
        ->and($rendered['action_route_key'])->toBeNull();
});

test('domain consumer creates only allowlisted privacy safe notifications', function () {
    $user = User::factory()->create();
    $appeal = Appeal::factory()->create(['appellant_user_id' => $user->id]);
    $listener = app(CreateDomainNotification::class);

    $listener->handle(new AppealDecided($appeal->id, $user->id, 100, 'upheld'));
    $listener->handle(new ViewingProgressUpdated(1, $user->id, 'in_progress'));

    expect(UserNotification::query()->count())->toBe(1)
        ->and(UserNotification::query()->firstOrFail()->payload)->toBe(['appeal_id' => $appeal->id, 'decision' => 'upheld'])
        ->and(is_subclass_of(CreateDomainNotification::class, ShouldQueueAfterCommit::class))->toBeTrue()
        ->and(is_subclass_of(AppealDecided::class, ShouldBroadcast::class))->toBeFalse();
});

test('notification API is recipient scoped supports read unread archive and bounded read all', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $notice = UserNotification::factory()->create(['user_id' => $owner->id]);
    UserNotification::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($other)->getJson("/api/v1/me/notifications/{$notice->id}")->assertNotFound();
    $this->actingAs($owner)->postJson("/api/v1/me/notifications/{$notice->id}/read")->assertSuccessful()->assertJsonPath('data.read_at', fn ($value): bool => is_string($value));
    $this->actingAs($owner)->postJson("/api/v1/me/notifications/{$notice->id}/unread")->assertSuccessful()->assertJsonPath('data.read_at', null);
    $this->actingAs($owner)->postJson('/api/v1/me/notifications/read-all')->assertSuccessful()->assertJsonPath('data.updated', 2);
    $this->actingAs($owner)->postJson("/api/v1/me/notifications/{$notice->id}/archive")->assertSuccessful()->assertJsonPath('data.status', 'archived');
    $this->actingAs($owner)->getJson('/api/v1/me/notifications')->assertSuccessful()->assertJsonMissingPath('data.0.payload');
});

test('mandatory in app preference cannot be disabled and stale versions conflict', function () {
    $user = User::factory()->create();
    $mandatory = ['preferences' => [['type' => 'moderation.action.applied', 'channel' => 'in_app', 'state' => 'disabled', 'expected_version' => 0]]];
    $this->actingAs($user)->patchJson('/api/v1/me/notification-preferences', $mandatory)->assertConflict()->assertJsonPath('error.code', 'mandatory_notification_preference');

    $optional = ['preferences' => [['type' => 'journey.completed', 'channel' => 'in_app', 'state' => 'disabled', 'expected_version' => 0]]];
    $this->actingAs($user)->patchJson('/api/v1/me/notification-preferences', $optional)->assertSuccessful()->assertJsonPath('data.0.lock_version', 1);
    $this->actingAs($user)->patchJson('/api/v1/me/notification-preferences', $optional)->assertConflict()->assertJsonPath('error.code', 'optimistic_lock_conflict');
});

test('failed email delivery retries are bounded and do not copy addresses or provider payloads', function () {
    $delivery = NotificationDelivery::factory()->failed()->create(['channel' => NotificationChannel::Email, 'attempt_number' => 1]);
    $retry = app(ManageNotificationDeliveries::class)->retry($delivery);

    expect($retry->attempt_number)->toBe(2)
        ->and($retry->status)->toBe(NotificationDeliveryStatus::Queued)
        ->and($retry->getAttributes())->not->toHaveKeys(['email', 'provider_payload']);

    $last = NotificationDelivery::factory()->failed()->create(['channel' => NotificationChannel::Email, 'attempt_number' => 3]);
    expect(fn () => app(ManageNotificationDeliveries::class)->retry($last))->toThrow(InvalidModerationOperation::class);
});

test('account deletion anonymizes reports and removes notification state without journey copying', function () {
    $user = User::factory()->create();
    $report = Report::factory()->create(['reporter_user_id' => $user->id]);
    UserNotification::factory()->create(['user_id' => $user->id]);
    NotificationPreference::factory()->create(['user_id' => $user->id]);
    $action = ModerationAction::factory()->create(['target_user_id' => $user->id]);
    $restriction = UserRestriction::factory()->create(['user_id' => $user->id, 'moderation_action_id' => $action->id]);
    $appeal = Appeal::factory()->create(['appellant_user_id' => $user->id, 'moderation_action_id' => $action->id, 'moderation_case_id' => $action->moderation_case_id, 'user_restriction_id' => $restriction->id]);

    $user->delete();

    expect($report->fresh()->reporter_user_id)->toBeNull()
        ->and(UserNotification::query()->count())->toBe(0)
        ->and(NotificationPreference::query()->count())->toBe(0)
        ->and($appeal->fresh()->appellant_user_id)->toBeNull()
        ->and($appeal->fresh()->user_restriction_id)->toBeNull()
        ->and(UserRestriction::query()->whereKey($restriction)->exists())->toBeFalse()
        ->and(ViewingProgressEvent::query()->where('user_id', $user->id)->count())->toBe(0);
});
