<?php

namespace App\Models;

use App\Enums\PersonalVisibility;
use Database\Factories\PersonalNoteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** @property int $id @property int $user_id @property int $universe_id @property string $target_type @property int $target_id @property string|null $title @property string $body @property string $format @property PersonalVisibility $visibility @property bool $is_pinned @property bool $is_spoiler_sensitive @property int $lock_version */
#[Fillable(['user_id', 'universe_id', 'target_type', 'target_id', 'title', 'body', 'format', 'visibility', 'is_pinned', 'is_spoiler_sensitive', 'lock_version'])]
class PersonalNote extends Model
{
    /** @use HasFactory<PersonalNoteFactory> */
    use HasFactory;

    use SoftDeletes;

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return MorphTo<Model, $this> */
    public function target(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['visibility' => PersonalVisibility::class, 'is_pinned' => 'boolean', 'is_spoiler_sensitive' => 'boolean', 'lock_version' => 'integer'];
    }
}
