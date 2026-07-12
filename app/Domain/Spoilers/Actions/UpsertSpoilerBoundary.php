<?php

namespace App\Domain\Spoilers\Actions;

use App\Domain\Editorial\Exceptions\InvalidEditorialOperation;
use App\Enums\PermissionName;
use App\Enums\SpoilerClassificationStatus;
use App\Enums\SpoilerSeverity;
use App\Models\Bunker;
use App\Models\CommunityComment;
use App\Models\CommunityPost;
use App\Models\Episode;
use App\Models\RevisionBlock;
use App\Models\Season;
use App\Models\SpoilerBoundary;
use App\Models\SpoilerConstraint;
use App\Models\User;
use App\Models\Work;
use App\Models\WorkTranslation;
use App\Support\AuditLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UpsertSpoilerBoundary
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(
        Model $target,
        Work $work,
        ?Season $season,
        ?Episode $episode,
        SpoilerSeverity $severity,
        SpoilerClassificationStatus $status,
        User $actor,
        ?string $warning = null,
        ?SpoilerConstraint $constraint = null,
    ): SpoilerBoundary {
        if ($status === SpoilerClassificationStatus::Approved && ! $actor->hasPermission(PermissionName::EditorialSpoilersReview)) {
            throw new InvalidEditorialOperation('Approving a spoiler classification requires spoiler-review permission.', 'spoiler_review_required');
        }

        $universeId = $this->universeId($target);
        if ($constraint !== null && ($constraint->spoilerable_type !== $target->getMorphClass() || $constraint->spoilerable_id !== $target->getKey())) {
            throw new InvalidEditorialOperation('The spoiler constraint belongs to a different target.', 'invalid_spoiler_target');
        }
        if ($work->universe_id !== $universeId
            || ($season !== null && $season->work_id !== $work->id)
            || ($episode !== null && ($episode->work_id !== $work->id || $episode->season_id !== $season?->id))) {
            throw new InvalidEditorialOperation('The spoiler boundary must use one coherent universe, work, season, and episode path.', 'invalid_spoiler_boundary');
        }

        return DB::transaction(function () use ($target, $work, $season, $episode, $severity, $status, $actor, $warning, $constraint, $universeId): SpoilerBoundary {
            $constraint ??= SpoilerConstraint::query()->create([
                'spoilerable_type' => $target->getMorphClass(),
                'spoilerable_id' => $target->getKey(),
                'universe_id' => $universeId,
                'severity' => $severity,
                'classification_status' => $status,
                'warning' => $warning,
                'classified_by' => $actor->id,
                'classified_at' => now(),
            ]);
            $previous = $constraint->only(['severity', 'classification_status', 'warning']);
            if ($constraint->exists && ($constraint->severity !== $severity || $constraint->classification_status !== $status || $constraint->warning !== $warning)) {
                $constraint->corrections()->create([
                    'corrected_by_user_id' => $actor->id,
                    'reason' => 'Classification corrected through the approved editorial endpoint.',
                    'previous_classification' => $previous,
                    'corrected_at' => now(),
                ]);
                $constraint->update([
                    'severity' => $severity,
                    'classification_status' => $status,
                    'warning' => $warning,
                    'reviewed_by' => $status === SpoilerClassificationStatus::Approved ? $actor->id : null,
                    'reviewed_at' => $status === SpoilerClassificationStatus::Approved ? now() : null,
                ]);
            }

            $boundary = $constraint->boundaries()->updateOrCreate(['work_id' => $work->id], [
                'season_id' => $season?->id,
                'episode_id' => $episode?->id,
            ]);
            $this->auditLogger->record($constraint->wasRecentlyCreated ? 'spoilers.classification_created' : 'spoilers.classification_corrected', $constraint, [
                'target_type' => $target->getMorphClass(),
                'target_id' => $target->getKey(),
                'status' => $status->value,
                'severity' => $severity->value,
            ], $actor);

            return $boundary->fresh(['constraint', 'work', 'season', 'episode']);
        });
    }

    private function universeId(Model $target): int
    {
        if ($target instanceof Work) {
            return $target->universe_id;
        }
        if ($target instanceof Season || $target instanceof Episode) {
            return $target->work()->value('universe_id');
        }
        if ($target instanceof WorkTranslation) {
            return $target->work()->value('universe_id');
        }
        if ($target instanceof RevisionBlock) {
            $revisable = $target->revision->revisable;

            return $this->universeId($revisable);
        }

        if ($target instanceof Bunker || $target instanceof CommunityPost) {
            return $target->universe_id;
        }
        if ($target instanceof CommunityComment) {
            return $target->post()->value('universe_id');
        }

        throw new InvalidEditorialOperation('This target does not support normalized spoiler boundaries.', 'invalid_spoiler_target');
    }
}
