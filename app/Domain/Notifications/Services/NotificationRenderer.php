<?php

namespace App\Domain\Notifications\Services;

use App\Domain\Catalog\Services\SpoilerVisibilityService;
use App\Domain\Moderation\Services\RestrictionEvaluator;
use App\Enums\SpoilerVisibility;
use App\Models\UserNotification;
use Illuminate\Database\Eloquent\Model;

class NotificationRenderer
{
    public function __construct(private readonly RestrictionEvaluator $restrictions, private readonly SpoilerVisibilityService $spoilers) {}

    /** @return array{title:string, body:string, action_label:string|null, action_route_key:string|null, action_route_params:array<string, int|string>, warning:string|null, icon_key:string, rendering:string} */
    public function render(UserNotification $notification): array
    {
        $notification->loadMissing(['user', 'subject']);
        $subject = $notification->subject;
        $rendering = 'detailed';
        $warning = null;

        if ($notification->subject_type !== null && ! $subject instanceof Model) {
            $rendering = 'unavailable';
        } elseif ($subject instanceof Model && $this->restrictions->isHiddenFromPublic($subject)) {
            $rendering = 'unavailable';
        } elseif ($subject instanceof Model && method_exists($subject, 'spoilerConstraints')) {
            $decision = $this->spoilers->decide($subject, $notification->user);
            $rendering = match ($decision) {
                SpoilerVisibility::Hidden => 'unavailable', SpoilerVisibility::Redacted => 'redacted', SpoilerVisibility::Warning => 'warning', default => 'detailed'
            };
            $warning = $decision === SpoilerVisibility::Warning ? 'This notification may reference content beyond your spoiler preference.' : null;
        }

        [$title, $body, $actionLabel, $routeKey, $routeParams] = $this->copy($notification, $rendering);

        return ['title' => $title, 'body' => $body, 'action_label' => $actionLabel, 'action_route_key' => $routeKey, 'action_route_params' => $routeParams, 'warning' => $warning, 'icon_key' => str($notification->type)->before('.')->toString(), 'rendering' => $rendering];
    }

    /** @return array{string, string, string|null, string|null, array<string, int|string>} */
    private function copy(UserNotification $notification, string $rendering): array
    {
        if ($rendering === 'unavailable') {
            return ['Notification update', 'The referenced item is no longer available.', null, null, []];
        }
        if ($rendering === 'redacted') {
            return ['Spoiler-sensitive update', 'Details are hidden by your spoiler preferences.', null, null, []];
        }

        return match ($notification->type) {
            'moderation.report.received' => ['Report received', 'Your report was received for private review.', 'View report', 'api.v1.me.reports.show', ['report' => (int) $notification->payload['report_id']]],
            'moderation.report.closed' => ['Report reviewed', 'Review of your report is complete.', 'View report', 'api.v1.me.reports.show', ['report' => (int) $notification->payload['report_id']]],
            'moderation.action.applied' => ['Moderation action applied', 'A moderation action affecting your account was applied.', null, null, []],
            'moderation.restriction.lifted' => ['Restriction lifted', 'A restriction affecting your account has been lifted.', null, null, []],
            'moderation.appeal.received' => ['Appeal received', 'Your appeal was received for independent review.', 'View appeal', 'api.v1.me.appeals.show', ['appeal' => (int) $notification->payload['appeal_id']]],
            'moderation.appeal.decided' => ['Appeal decided', 'A decision is available for your appeal.', 'View appeal', 'api.v1.me.appeals.show', ['appeal' => (int) $notification->payload['appeal_id']]],
            'moderation.case.assigned' => ['Moderation case assigned', 'A moderation case has been assigned to you.', 'View case', 'api.v1.moderation.cases.show', ['case' => (string) $notification->payload['case_public_id']]],
            'editorial.revision.approved' => ['Revision approved', 'Your editorial revision was approved.', null, null, []],
            'editorial.revision.applied' => ['Revision applied', 'Your editorial revision was applied.', null, null, []],
            'media.review.approved' => ['Media approved', 'Your media record passed review.', null, null, []],
            'journey.completed' => ['Journey completed', 'You completed a private viewing journey.', 'View journey', 'api.v1.me.journeys.show', ['journey' => (int) $notification->payload['journey_id']]],
            'rewatch.completed' => ['Rewatch completed', 'You completed a private rewatch cycle.', null, null, []],
            'community.bunker.invited' => ['Bunker invitation', 'You received a Bunker invitation.', null, null, []],
            'community.bunker.join_requested' => ['Join request', 'A user requested to join your Bunker.', null, null, []],
            'community.bunker.join_approved' => ['Join request approved', 'Your Bunker join request was approved.', 'View Bunker', 'api.v1.bunkers.show', ['bunker' => (int) $notification->payload['bunker_id']]],
            'community.bunker.banned' => ['Bunker access restricted', 'Your access to a Bunker was restricted.', null, null, []],
            'community.post.mentioned' => ['Community mention', 'You were mentioned in eligible Community content.', null, null, []],
            default => ['Notification update', 'An account update is available.', null, null, []],
        };
    }
}
