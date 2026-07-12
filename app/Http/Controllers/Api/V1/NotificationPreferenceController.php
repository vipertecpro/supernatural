<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Editorial\Exceptions\OptimisticLockConflict;
use App\Domain\Moderation\Exceptions\InvalidModerationOperation;
use App\Domain\Notifications\Services\NotificationTypeRegistry;
use App\Enums\NotificationChannel;
use App\Enums\NotificationPreferenceState;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateNotificationPreferencesRequest;
use App\Models\NotificationPreference;
use App\Support\ApiResponse;
use App\Support\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class NotificationPreferenceController extends Controller
{
    public function index(Request $request, NotificationTypeRegistry $types): JsonResponse
    {
        Gate::authorize('viewAny', NotificationPreference::class);
        $existing = NotificationPreference::query()->where('user_id', $request->user()->id)->get()->keyBy(fn (NotificationPreference $preference): string => $preference->type.':'.$preference->channel->value);
        $preferences = [];
        foreach ($types->keys() as $type) {
            $definition = $types->definition($type);
            foreach ($definition['channels'] as $channel) {
                $stored = $existing->get($type.':'.$channel->value);
                $preferences[] = ['type' => $type, 'channel' => $channel->value, 'state' => $stored === null ? NotificationPreferenceState::Enabled->value : $stored->state->value, 'lock_version' => $stored === null ? 0 : $stored->lock_version, 'mandatory' => $channel === NotificationChannel::InApp && $definition['mandatory_in_app'] || $channel === NotificationChannel::Email && $definition['mandatory_email']];
            }
        }

        return ApiResponse::success($request, $preferences);
    }

    public function update(UpdateNotificationPreferencesRequest $request, NotificationTypeRegistry $types, AuditLogger $auditLogger): JsonResponse
    {
        Gate::authorize('create', NotificationPreference::class);
        $records = DB::transaction(function () use ($request, $types, $auditLogger): array {
            $records = [];
            foreach ($request->preferences() as $input) {
                $definition = $types->definition($input['type']);
                $channel = NotificationChannel::from($input['channel']);
                if (! in_array($channel, $definition['channels'], true)) {
                    throw new InvalidModerationOperation('The notification channel is not supported for this type.', 'notification_preference_invalid');
                }
                $state = NotificationPreferenceState::from($input['state']);
                if ($state === NotificationPreferenceState::Disabled && (($channel === NotificationChannel::InApp && $definition['mandatory_in_app']) || ($channel === NotificationChannel::Email && $definition['mandatory_email']))) {
                    $auditLogger->record('notifications.mandatory_preference_change_rejected', null, ['type' => $input['type'], 'channel' => $channel->value], $request->user());
                    throw new InvalidModerationOperation('Mandatory notification delivery cannot be disabled.', 'mandatory_notification_preference');
                }

                $preference = NotificationPreference::query()->firstOrNew(['user_id' => $request->user()->id, 'type' => $input['type'], 'channel' => $channel]);
                if ($preference->exists && $preference->lock_version !== (int) $input['expected_version']) {
                    throw new OptimisticLockConflict;
                }
                if (! $preference->exists && (int) $input['expected_version'] !== 0) {
                    throw new OptimisticLockConflict;
                }
                $preference->fill(['state' => $state, 'lock_version' => (int) $input['expected_version'] + 1])->save();

                $records[] = $preference;
            }

            return $records;
        });

        return ApiResponse::success($request, array_map(fn (NotificationPreference $preference): array => ['type' => $preference->type, 'channel' => $preference->channel->value, 'state' => $preference->state->value, 'lock_version' => $preference->lock_version], $records));
    }
}
