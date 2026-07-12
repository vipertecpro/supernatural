<?php

namespace App\Domain\Catalog\Services;

use App\Enums\PermissionName;
use App\Enums\SpoilerClassificationStatus;
use App\Enums\SpoilerSeverity;
use App\Enums\SpoilerTolerance;
use App\Enums\SpoilerVisibility;
use App\Models\EntityAppearance;
use App\Models\Episode;
use App\Models\LoreAlias;
use App\Models\LoreEntity;
use App\Models\LoreEntityTranslation;
use App\Models\LoreRelationship;
use App\Models\Season;
use App\Models\SpoilerBoundary;
use App\Models\Timeline;
use App\Models\TimelineEntry;
use App\Models\User;
use App\Models\UserSpoilerPreference;
use App\Models\ViewingProgress;
use App\Models\Work;
use App\Models\WorkTranslation;
use Illuminate\Database\Eloquent\Model;

class SpoilerVisibilityService
{
    public function decide(Model $model, ?User $viewer = null): SpoilerVisibility
    {
        if ($viewer?->hasPermission(PermissionName::EditorialSpoilersBypass)) {
            return SpoilerVisibility::Visible;
        }

        $model->loadMissing('spoilerConstraints.boundaries.work', 'spoilerConstraints.boundaries.season', 'spoilerConstraints.boundaries.episode');
        $approved = $model->getRelation('spoilerConstraints')
            ->where('classification_status', SpoilerClassificationStatus::Approved);

        if ($approved->isEmpty()) {
            return SpoilerVisibility::Redacted;
        }

        $unsafe = $approved->filter(fn ($constraint): bool => $constraint->severity !== SpoilerSeverity::None)
            ->filter(fn ($constraint): bool => ! $this->boundariesSatisfied($constraint->boundaries->all(), $viewer));

        if ($unsafe->isEmpty()) {
            return SpoilerVisibility::Visible;
        }

        $severity = $unsafe->sortByDesc(fn ($constraint): int => $constraint->severity->rank())->first()->severity;
        $universeId = $this->universeId($model);
        $tolerance = $viewer === null ? SpoilerTolerance::Strict : $this->tolerance($viewer, $universeId);

        return match ($tolerance) {
            SpoilerTolerance::Permissive => SpoilerVisibility::Warning,
            SpoilerTolerance::Warn => $severity->rank() <= SpoilerSeverity::Moderate->rank()
                ? SpoilerVisibility::Warning
                : SpoilerVisibility::Redacted,
            SpoilerTolerance::Strict => $severity === SpoilerSeverity::Finale
                ? SpoilerVisibility::Hidden
                : SpoilerVisibility::Redacted,
        };
    }

    public function shouldRedact(Model $model, ?User $viewer = null): bool
    {
        return in_array($this->decide($model, $viewer), [SpoilerVisibility::Redacted, SpoilerVisibility::Hidden], true);
    }

    /** @param list<SpoilerBoundary> $boundaries */
    private function boundariesSatisfied(array $boundaries, ?User $viewer): bool
    {
        if ($viewer === null || $boundaries === []) {
            return false;
        }

        return collect($boundaries)->every(function (SpoilerBoundary $boundary) use ($viewer): bool {
            $progress = ViewingProgress::query()
                ->with(['season', 'episode'])
                ->where('user_id', $viewer->id)
                ->where('work_id', $boundary->work_id)
                ->first();

            if ($progress === null) {
                return false;
            }
            if ($boundary->episode_id !== null) {
                return $progress->episode !== null
                    && $progress->episode->position >= $boundary->episode->position;
            }
            if ($boundary->season_id !== null) {
                return $progress->season !== null
                    && $progress->season->position >= $boundary->season->position;
            }

            return true;
        });
    }

    private function tolerance(User $viewer, int $universeId): SpoilerTolerance
    {
        $preference = UserSpoilerPreference::query()
            ->where('user_id', $viewer->id)
            ->where('universe_id', $universeId)
            ->first();

        return $preference === null ? SpoilerTolerance::Strict : $preference->tolerance;
    }

    private function universeId(Model $model): int
    {
        return match (true) {
            $model instanceof Work => $model->universe_id,
            $model instanceof Season, $model instanceof Episode, $model instanceof WorkTranslation => (int) $model->work()->value('universe_id'),
            $model instanceof LoreEntity, $model instanceof Timeline => (int) $model->getAttribute('universe_id'),
            $model instanceof LoreEntityTranslation, $model instanceof LoreAlias, $model instanceof EntityAppearance => (int) $model->loreEntity()->value('universe_id'),
            $model instanceof LoreRelationship => (int) $model->sourceEntity()->value('universe_id'),
            $model instanceof TimelineEntry => (int) $model->timeline()->value('universe_id'),
            default => (int) $model->getAttribute('universe_id'),
        };
    }
}
