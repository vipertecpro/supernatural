<?php

namespace App\Models;

use App\Concerns\HasEditorialRevisions;
use App\Concerns\HasSpoilerConstraints;
use App\Enums\PublicationStatus;
use Database\Factories\LoreEntityTranslationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $lore_entity_id
 * @property string $locale
 * @property string $name
 * @property string|null $short_description
 * @property string|null $summary
 * @property PublicationStatus $status
 * @property int $lock_version
 * @property LoreEntity $loreEntity
 */
class LoreEntityTranslation extends Model
{
    /** @use HasFactory<LoreEntityTranslationFactory> */
    use HasEditorialRevisions, HasFactory, HasSpoilerConstraints;

    protected $fillable = ['lore_entity_id', 'locale', 'name', 'short_name', 'short_description', 'summary', 'source_locale', 'status', 'created_by', 'updated_by', 'published_at', 'lock_version'];

    /** @return BelongsTo<LoreEntity, $this> */
    public function loreEntity(): BelongsTo
    {
        return $this->belongsTo(LoreEntity::class);
    }

    protected function casts(): array
    {
        return ['status' => PublicationStatus::class, 'published_at' => 'immutable_datetime', 'lock_version' => 'integer'];
    }
}
