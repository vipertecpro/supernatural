<?php

namespace App\Models;

use App\Enums\ModerationCaseStatus;
use App\Enums\ReportPriority;
use Database\Factories\ModerationCaseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $public_id
 * @property string|null $target_type
 * @property int|null $target_id
 * @property int|null $subject_user_id
 * @property ModerationCaseStatus $status
 * @property ReportPriority $priority
 * @property string|null $resolution_code
 * @property string|null $user_visible_summary
 * @property string|null $private_internal_summary
 * @property int $lock_version
 * @property mixed $opened_at
 * @property mixed $triaged_at
 * @property mixed $investigation_started_at
 * @property mixed $decision_at
 * @property mixed $closed_at
 */
class ModerationCase extends Model
{
    /** @use HasFactory<ModerationCaseFactory> */
    use HasFactory;

    protected $fillable = ['public_id', 'target_type', 'target_id', 'subject_user_id', 'status', 'priority', 'opened_by_user_id', 'opened_at', 'triaged_at', 'investigation_started_at', 'decision_at', 'closed_at', 'resolution_code', 'user_visible_summary', 'private_internal_summary', 'safe_metadata', 'lock_version'];

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    /** @return MorphTo<Model, $this> */
    public function target(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsTo<User, $this> */
    public function subjectUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subject_user_id');
    }

    /** @return HasMany<Report, $this> */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    /** @return HasMany<ModerationCaseAssignment, $this> */
    public function assignments(): HasMany
    {
        return $this->hasMany(ModerationCaseAssignment::class);
    }

    /** @return HasMany<ModerationAction, $this> */
    public function actions(): HasMany
    {
        return $this->hasMany(ModerationAction::class);
    }

    /** @return HasMany<Appeal, $this> */
    public function appeals(): HasMany
    {
        return $this->hasMany(Appeal::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['status' => ModerationCaseStatus::class, 'priority' => ReportPriority::class, 'opened_at' => 'immutable_datetime', 'triaged_at' => 'immutable_datetime', 'investigation_started_at' => 'immutable_datetime', 'decision_at' => 'immutable_datetime', 'closed_at' => 'immutable_datetime', 'safe_metadata' => 'array', 'lock_version' => 'integer'];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $case): void {
            if ($case->getAttribute('public_id') === null) {
                $case->setAttribute('public_id', (string) Str::ulid());
            }
        });
    }
}
