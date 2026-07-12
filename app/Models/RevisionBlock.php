<?php

namespace App\Models;

use App\Concerns\HasSpoilerConstraints;
use Database\Factories\RevisionBlockFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property int $id
 * @property int $editorial_revision_id
 * @property string $field
 * @property string|null $locale
 * @property string|null $original_text_checksum
 * @property string $proposed_text
 * @property string $format
 * @property int $position
 */
#[Fillable(['editorial_revision_id', 'field', 'locale', 'original_text_checksum', 'proposed_text', 'format', 'position', 'source_required', 'rights_required'])]
class RevisionBlock extends Model
{
    /** @use HasFactory<RevisionBlockFactory> */
    use HasFactory, HasSpoilerConstraints;

    /** @return BelongsTo<EditorialRevision, $this> */
    public function revision(): BelongsTo
    {
        return $this->belongsTo(EditorialRevision::class, 'editorial_revision_id');
    }

    /** @return MorphMany<Citation, $this> */
    public function citations(): MorphMany
    {
        return $this->morphMany(Citation::class, 'citable');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'source_required' => 'boolean',
            'rights_required' => 'boolean',
        ];
    }
}
