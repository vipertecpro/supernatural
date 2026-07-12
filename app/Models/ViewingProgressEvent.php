<?php

namespace App\Models;

use App\Enums\ProgressEventType;
use App\Enums\ProgressSource;
use App\Enums\ProgressStatus;
use Database\Factories\ViewingProgressEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $viewing_progress_id
 * @property int|null $user_viewing_journey_id
 * @property int|null $rewatch_cycle_id
 * @property ProgressEventType $event_type
 * @property ProgressStatus|null $previous_status
 * @property ProgressStatus $new_status
 * @property int|null $previous_position_seconds
 * @property int|null $new_position_seconds
 * @property string|null $client_request_id
 * @property ProgressSource $source
 * @property array<string, mixed>|null $safe_metadata
 * @property mixed $occurred_at
 */
#[Fillable(['user_id', 'viewing_progress_id', 'user_viewing_journey_id', 'rewatch_cycle_id', 'event_type', 'previous_status', 'new_status', 'previous_position_seconds', 'new_position_seconds', 'client_request_id', 'source', 'safe_metadata', 'occurred_at'])]
class ViewingProgressEvent extends Model
{
    /** @use HasFactory<ViewingProgressEventFactory> */
    use HasFactory;

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<ViewingProgress, $this> */
    public function progress(): BelongsTo
    {
        return $this->belongsTo(ViewingProgress::class, 'viewing_progress_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['event_type' => ProgressEventType::class, 'previous_status' => ProgressStatus::class, 'new_status' => ProgressStatus::class, 'source' => ProgressSource::class, 'safe_metadata' => 'array', 'occurred_at' => 'immutable_datetime'];
    }
}
