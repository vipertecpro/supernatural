<?php

namespace App\Models;

use Database\Factories\TrendingSnapshotFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @property int $id */
class TrendingSnapshot extends Model
{
    /** @use HasFactory<TrendingSnapshotFactory> */
    use HasFactory;

    protected $fillable = ['universe_id', 'subject_type', 'subject_id', 'query_hash', 'score', 'sample_size', 'window_started_at', 'window_ended_at'];

    /** @return BelongsTo<Universe, $this> */
    public function universe(): BelongsTo
    {
        return $this->belongsTo(Universe::class);
    }

    protected function casts(): array
    {
        return ['window_started_at' => 'immutable_datetime', 'window_ended_at' => 'immutable_datetime'];
    }
}
