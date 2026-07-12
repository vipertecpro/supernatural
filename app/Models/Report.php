<?php

namespace App\Models;

use App\Enums\ReportPriority;
use App\Enums\ReportStatus;
use Database\Factories\ReportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property int|null $reporter_user_id
 * @property int $report_category_id
 * @property string $target_type
 * @property int $target_id
 * @property int|null $moderation_case_id
 * @property int|null $duplicate_of_report_id
 * @property ReportStatus $status
 * @property ReportPriority $priority
 * @property string|null $reason_code
 * @property string|null $explanation
 * @property mixed $submitted_at
 * @property mixed $withdrawn_at
 * @property mixed $closed_at
 */
class Report extends Model
{
    /** @use HasFactory<ReportFactory> */
    use HasFactory;

    protected $fillable = ['reporter_user_id', 'report_category_id', 'target_type', 'target_id', 'moderation_case_id', 'duplicate_of_report_id', 'status', 'priority', 'reason_code', 'explanation', 'request_id', 'safe_metadata', 'submitted_at', 'withdrawn_at', 'closed_at'];

    /** @return BelongsTo<User, $this> */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_user_id');
    }

    /** @return BelongsTo<ReportCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ReportCategory::class, 'report_category_id');
    }

    /** @return MorphTo<Model, $this> */
    public function target(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsTo<ModerationCase, $this> */
    public function moderationCase(): BelongsTo
    {
        return $this->belongsTo(ModerationCase::class);
    }

    /** @return BelongsTo<Report, $this> */
    public function duplicateOf(): BelongsTo
    {
        return $this->belongsTo(self::class, 'duplicate_of_report_id');
    }

    /** @return HasMany<ReportEvidence, $this> */
    public function evidence(): HasMany
    {
        return $this->hasMany(ReportEvidence::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['status' => ReportStatus::class, 'priority' => ReportPriority::class, 'safe_metadata' => 'array', 'submitted_at' => 'immutable_datetime', 'withdrawn_at' => 'immutable_datetime', 'closed_at' => 'immutable_datetime'];
    }
}
