<?php

namespace App\Listeners;

use App\Domain\Notifications\Actions\CreateUserNotification;
use App\Events\AppealDecided;
use App\Events\AppealSubmitted;
use App\Events\EditorialRevisionApplied;
use App\Events\EditorialRevisionApproved;
use App\Events\MediaPublished;
use App\Events\ModerationActionApplied;
use App\Events\ModerationCaseAssigned;
use App\Events\ReportClosed;
use App\Events\ReportSubmitted;
use App\Events\RewatchCycleCompleted;
use App\Events\UserRestrictionLifted;
use App\Events\ViewingJourneyCompleted;
use App\Models\Appeal;
use App\Models\EditorialRevision;
use App\Models\ModerationAction;
use App\Models\ModerationCase;
use App\Models\Report;
use App\Models\RewatchCycle;
use App\Models\User;
use App\Models\UserRestriction;
use App\Models\UserViewingJourney;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class CreateDomainNotification implements ShouldQueueAfterCommit
{
    public string $queue = 'notifications';

    public int $tries = 3;

    public function __construct(private readonly CreateUserNotification $notifications) {}

    public function handle(object $event): void
    {
        match (true) {
            $event instanceof ReportSubmitted => $this->reportSubmitted($event),
            $event instanceof ReportClosed => $this->reportClosed($event),
            $event instanceof ModerationCaseAssigned => $this->caseAssigned($event),
            $event instanceof ModerationActionApplied => $this->actionApplied($event),
            $event instanceof UserRestrictionLifted => $this->restrictionLifted($event),
            $event instanceof AppealSubmitted => $this->appealSubmitted($event),
            $event instanceof AppealDecided => $this->appealDecided($event),
            $event instanceof EditorialRevisionApproved => $this->editorial($event->revisionId, 'editorial.revision.approved', 'approved'),
            $event instanceof EditorialRevisionApplied => $this->editorial($event->revisionId, 'editorial.revision.applied', 'applied'),
            $event instanceof MediaPublished => $this->mediaPublished($event),
            $event instanceof ViewingJourneyCompleted => $this->journeyCompleted($event),
            $event instanceof RewatchCycleCompleted => $this->rewatchCompleted($event),
            default => null,
        };
    }

    private function reportSubmitted(ReportSubmitted $event): void
    {
        $this->create(User::query()->find($event->reporterUserId), 'moderation.report.received', 'report-submitted:'.$event->reportId, ['report_id' => $event->reportId, 'status' => 'submitted'], Report::query()->find($event->reportId));
    }

    private function reportClosed(ReportClosed $event): void
    {
        $this->create(User::query()->find($event->reporterUserId), 'moderation.report.closed', 'report-closed:'.$event->reportId, ['report_id' => $event->reportId, 'status' => 'closed', 'resolution_code' => $event->resolutionCode], Report::query()->find($event->reportId));
    }

    private function caseAssigned(ModerationCaseAssigned $event): void
    {
        $case = ModerationCase::query()->find($event->moderationCaseId);
        if ($case !== null) {
            $this->create(User::query()->find($event->moderatorUserId), 'moderation.case.assigned', 'case-assigned:'.$event->assignmentId, ['case_public_id' => $case->public_id, 'assignment_id' => $event->assignmentId], $case);
        }
    }

    private function actionApplied(ModerationActionApplied $event): void
    {
        $action = ModerationAction::query()->with('moderationCase')->find($event->moderationActionId);
        if ($action !== null && $event->targetUserId !== null) {
            $this->create(User::query()->find($event->targetUserId), 'moderation.action.applied', 'moderation-action:'.$event->moderationActionId, ['action_id' => $action->id, 'case_public_id' => $action->moderationCase->public_id, 'reason_code' => $action->reason_code], $action);
        }
    }

    private function restrictionLifted(UserRestrictionLifted $event): void
    {
        $this->create(User::query()->find($event->userId), 'moderation.restriction.lifted', 'restriction-lifted:'.$event->restrictionId, ['restriction_id' => $event->restrictionId], UserRestriction::query()->find($event->restrictionId));
    }

    private function appealSubmitted(AppealSubmitted $event): void
    {
        $this->create(User::query()->find($event->appellantUserId), 'moderation.appeal.received', 'appeal-submitted:'.$event->appealId, ['appeal_id' => $event->appealId, 'status' => 'submitted'], Appeal::query()->find($event->appealId));
    }

    private function appealDecided(AppealDecided $event): void
    {
        $this->create(User::query()->find($event->appellantUserId), 'moderation.appeal.decided', 'appeal-decided:'.$event->decisionId, ['appeal_id' => $event->appealId, 'decision' => $event->decisionType], Appeal::query()->find($event->appealId));
    }

    private function editorial(int $revisionId, string $type, string $status): void
    {
        $revision = EditorialRevision::query()->find($revisionId);
        if ($revision !== null) {
            $this->create(User::query()->find($revision->author_user_id), $type, $type.':'.$revisionId, ['revision_id' => $revisionId, 'status' => $status], $revision);
        }
    }

    private function mediaPublished(MediaPublished $event): void
    {
        $class = Relation::getMorphedModel($event->mediaType);
        $media = $class === null ? null : $class::query()->find($event->mediaId);
        $ownerId = $media?->getAttribute('owner_user_id');
        if ($media instanceof Model && is_int($ownerId)) {
            $this->create(User::query()->find($ownerId), 'media.review.approved', 'media-published:'.$event->mediaType.':'.$event->mediaId, ['media_type' => $event->mediaType, 'media_id' => $event->mediaId, 'status' => 'approved'], $media);
        }
    }

    private function journeyCompleted(ViewingJourneyCompleted $event): void
    {
        $this->create(User::query()->find($event->userId), 'journey.completed', 'journey-completed:'.$event->journeyId, ['journey_id' => $event->journeyId], UserViewingJourney::query()->find($event->journeyId));
    }

    private function rewatchCompleted(RewatchCycleCompleted $event): void
    {
        $this->create(User::query()->find($event->userId), 'rewatch.completed', 'rewatch-completed:'.$event->rewatchCycleId, ['rewatch_cycle_id' => $event->rewatchCycleId], RewatchCycle::query()->find($event->rewatchCycleId));
    }

    /** @param array<string, mixed> $payload */
    private function create(?User $recipient, string $type, string $key, array $payload, ?Model $subject): void
    {
        if ($recipient !== null) {
            $this->notifications->execute($recipient, $type, $key, $payload, $subject, correlationKey: $key);
        }
    }
}
