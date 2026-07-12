<?php

namespace App\Domain\Notifications\Services;

use App\Domain\Moderation\Exceptions\InvalidModerationOperation;
use App\Enums\NotificationChannel;
use App\Enums\NotificationPriority;

class NotificationTypeRegistry
{
    /** @var array<string, array{schema_version:int, category:string, priority:NotificationPriority, channels:list<NotificationChannel>, mandatory_in_app:bool, mandatory_email:bool, email_disableable:bool, may_expire:bool, spoiler_sensitive:bool, payload_fields:list<string>, active:bool}> */
    private const DEFINITIONS = [
        'moderation.report.received' => ['schema_version' => 1, 'category' => 'moderation', 'priority' => NotificationPriority::Normal, 'channels' => [NotificationChannel::InApp, NotificationChannel::Email], 'mandatory_in_app' => true, 'mandatory_email' => false, 'email_disableable' => true, 'may_expire' => false, 'spoiler_sensitive' => false, 'payload_fields' => ['report_id', 'status'], 'active' => true],
        'moderation.report.closed' => ['schema_version' => 1, 'category' => 'moderation', 'priority' => NotificationPriority::Normal, 'channels' => [NotificationChannel::InApp, NotificationChannel::Email], 'mandatory_in_app' => true, 'mandatory_email' => false, 'email_disableable' => true, 'may_expire' => false, 'spoiler_sensitive' => false, 'payload_fields' => ['report_id', 'status', 'resolution_code'], 'active' => true],
        'moderation.action.applied' => ['schema_version' => 1, 'category' => 'moderation', 'priority' => NotificationPriority::High, 'channels' => [NotificationChannel::InApp, NotificationChannel::Email], 'mandatory_in_app' => true, 'mandatory_email' => false, 'email_disableable' => true, 'may_expire' => false, 'spoiler_sensitive' => false, 'payload_fields' => ['action_id', 'case_public_id', 'reason_code'], 'active' => true],
        'moderation.restriction.lifted' => ['schema_version' => 1, 'category' => 'moderation', 'priority' => NotificationPriority::High, 'channels' => [NotificationChannel::InApp, NotificationChannel::Email], 'mandatory_in_app' => true, 'mandatory_email' => false, 'email_disableable' => true, 'may_expire' => false, 'spoiler_sensitive' => false, 'payload_fields' => ['restriction_id'], 'active' => true],
        'moderation.appeal.received' => ['schema_version' => 1, 'category' => 'moderation', 'priority' => NotificationPriority::Normal, 'channels' => [NotificationChannel::InApp, NotificationChannel::Email], 'mandatory_in_app' => true, 'mandatory_email' => false, 'email_disableable' => true, 'may_expire' => false, 'spoiler_sensitive' => false, 'payload_fields' => ['appeal_id', 'status'], 'active' => true],
        'moderation.appeal.decided' => ['schema_version' => 1, 'category' => 'moderation', 'priority' => NotificationPriority::High, 'channels' => [NotificationChannel::InApp, NotificationChannel::Email], 'mandatory_in_app' => true, 'mandatory_email' => false, 'email_disableable' => true, 'may_expire' => false, 'spoiler_sensitive' => false, 'payload_fields' => ['appeal_id', 'decision'], 'active' => true],
        'moderation.case.assigned' => ['schema_version' => 1, 'category' => 'moderation', 'priority' => NotificationPriority::High, 'channels' => [NotificationChannel::InApp, NotificationChannel::Email], 'mandatory_in_app' => true, 'mandatory_email' => false, 'email_disableable' => true, 'may_expire' => false, 'spoiler_sensitive' => false, 'payload_fields' => ['case_public_id', 'assignment_id'], 'active' => true],
        'editorial.revision.approved' => ['schema_version' => 1, 'category' => 'editorial', 'priority' => NotificationPriority::Normal, 'channels' => [NotificationChannel::InApp, NotificationChannel::Email], 'mandatory_in_app' => false, 'mandatory_email' => false, 'email_disableable' => true, 'may_expire' => true, 'spoiler_sensitive' => true, 'payload_fields' => ['revision_id', 'status'], 'active' => true],
        'editorial.revision.applied' => ['schema_version' => 1, 'category' => 'editorial', 'priority' => NotificationPriority::Normal, 'channels' => [NotificationChannel::InApp, NotificationChannel::Email], 'mandatory_in_app' => false, 'mandatory_email' => false, 'email_disableable' => true, 'may_expire' => true, 'spoiler_sensitive' => true, 'payload_fields' => ['revision_id', 'status'], 'active' => true],
        'media.review.approved' => ['schema_version' => 1, 'category' => 'media', 'priority' => NotificationPriority::Normal, 'channels' => [NotificationChannel::InApp, NotificationChannel::Email], 'mandatory_in_app' => false, 'mandatory_email' => false, 'email_disableable' => true, 'may_expire' => true, 'spoiler_sensitive' => true, 'payload_fields' => ['media_type', 'media_id', 'status'], 'active' => true],
        'journey.completed' => ['schema_version' => 1, 'category' => 'journey', 'priority' => NotificationPriority::Low, 'channels' => [NotificationChannel::InApp], 'mandatory_in_app' => false, 'mandatory_email' => false, 'email_disableable' => true, 'may_expire' => true, 'spoiler_sensitive' => true, 'payload_fields' => ['journey_id'], 'active' => true],
        'rewatch.completed' => ['schema_version' => 1, 'category' => 'journey', 'priority' => NotificationPriority::Low, 'channels' => [NotificationChannel::InApp], 'mandatory_in_app' => false, 'mandatory_email' => false, 'email_disableable' => true, 'may_expire' => true, 'spoiler_sensitive' => true, 'payload_fields' => ['rewatch_cycle_id'], 'active' => true],
        'community.bunker.invited' => ['schema_version' => 1, 'category' => 'community', 'priority' => NotificationPriority::Normal, 'channels' => [NotificationChannel::InApp, NotificationChannel::Email], 'mandatory_in_app' => false, 'mandatory_email' => false, 'email_disableable' => true, 'may_expire' => true, 'spoiler_sensitive' => false, 'payload_fields' => ['invitation_id', 'bunker_id'], 'active' => true],
        'community.bunker.join_requested' => ['schema_version' => 1, 'category' => 'community', 'priority' => NotificationPriority::Normal, 'channels' => [NotificationChannel::InApp], 'mandatory_in_app' => false, 'mandatory_email' => false, 'email_disableable' => true, 'may_expire' => true, 'spoiler_sensitive' => false, 'payload_fields' => ['join_request_id', 'bunker_id'], 'active' => true],
        'community.bunker.join_approved' => ['schema_version' => 1, 'category' => 'community', 'priority' => NotificationPriority::Normal, 'channels' => [NotificationChannel::InApp], 'mandatory_in_app' => false, 'mandatory_email' => false, 'email_disableable' => true, 'may_expire' => true, 'spoiler_sensitive' => false, 'payload_fields' => ['join_request_id', 'bunker_id'], 'active' => true],
        'community.bunker.banned' => ['schema_version' => 1, 'category' => 'community', 'priority' => NotificationPriority::High, 'channels' => [NotificationChannel::InApp, NotificationChannel::Email], 'mandatory_in_app' => true, 'mandatory_email' => false, 'email_disableable' => true, 'may_expire' => false, 'spoiler_sensitive' => false, 'payload_fields' => ['ban_id', 'bunker_id'], 'active' => true],
        'community.post.mentioned' => ['schema_version' => 1, 'category' => 'community', 'priority' => NotificationPriority::Normal, 'channels' => [NotificationChannel::InApp], 'mandatory_in_app' => false, 'mandatory_email' => false, 'email_disableable' => true, 'may_expire' => true, 'spoiler_sensitive' => true, 'payload_fields' => ['mention_id'], 'active' => true],
    ];

    /** @return array{schema_version:int, category:string, priority:NotificationPriority, channels:list<NotificationChannel>, mandatory_in_app:bool, mandatory_email:bool, email_disableable:bool, may_expire:bool, spoiler_sensitive:bool, payload_fields:list<string>, active:bool} */
    public function definition(string $type): array
    {
        $definition = self::DEFINITIONS[$type] ?? null;
        if ($definition === null) {
            throw new InvalidModerationOperation('The notification type is not active or recognized.', 'notification_type_unavailable');
        }

        return $definition;
    }

    /** @return list<string> */
    public function keys(): array
    {
        return array_keys(self::DEFINITIONS);
    }

    /** @param array<string, mixed> $payload */
    public function validatePayload(string $type, array $payload): void
    {
        $definition = $this->definition($type);
        $unknown = array_diff(array_keys($payload), $definition['payload_fields']);
        if ($unknown !== []) {
            throw new InvalidModerationOperation('The notification payload contains unsupported fields.', 'notification_payload_invalid');
        }
        foreach ($payload as $key => $value) {
            if (! is_scalar($value) && $value !== null) {
                throw new InvalidModerationOperation('Notification payload values must be scalar.', 'notification_payload_invalid');
            }
            if (preg_match('/token|secret|authorization|password|note|history|position|session/i', $key) === 1) {
                throw new InvalidModerationOperation('The notification payload contains a prohibited field.', 'notification_payload_invalid');
            }
        }
    }
}
