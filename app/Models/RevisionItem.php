<?php

namespace App\Models;

use App\Enums\RevisionOperation;
use Database\Factories\RevisionItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property int $id
 * @property int $editorial_revision_id
 * @property string $field
 * @property RevisionOperation $operation
 * @property string|null $previous_value_hash
 * @property array{value: mixed}|null $proposed_value
 * @property int $position
 */
#[Fillable(['editorial_revision_id', 'field', 'operation', 'previous_value_hash', 'proposed_value', 'position', 'validation_metadata'])]
class RevisionItem extends Model
{
    /** @use HasFactory<RevisionItemFactory> */
    use HasFactory;

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
            'operation' => RevisionOperation::class,
            'proposed_value' => 'array',
            'validation_metadata' => 'array',
        ];
    }
}
