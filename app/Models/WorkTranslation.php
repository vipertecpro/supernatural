<?php

namespace App\Models;

use App\Enums\PublicationStatus;
use Carbon\CarbonImmutable;
use Database\Factories\WorkTranslationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property PublicationStatus $status
 * @property CarbonImmutable|null $published_at
 */
#[Fillable(['work_id', 'locale', 'title', 'short_title', 'summary', 'synopsis', 'translated_from_locale', 'status', 'published_at', 'created_by', 'updated_by'])]
class WorkTranslation extends Model
{
    /** @use HasFactory<WorkTranslationFactory> */
    use HasFactory;

    /** @return BelongsTo<Work, $this> */
    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return BelongsTo<User, $this> */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => PublicationStatus::class,
            'published_at' => 'immutable_datetime',
        ];
    }
}
