<?php

namespace App\Models;

use App\Concerns\HasEditorialRevisions;
use App\Concerns\HasSpoilerConstraints;
use App\Enums\LoreAliasType;
use App\Enums\PublicationStatus;
use Database\Factories\LoreAliasFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $lore_entity_id
 * @property string $name
 * @property string $normalized_name
 * @property LoreAliasType $type
 * @property string|null $locale
 * @property bool $spoiler_sensitive
 * @property PublicationStatus $status
 * @property int $lock_version
 * @property LoreEntity $loreEntity
 */
class LoreAlias extends Model
{
    /** @use HasFactory<LoreAliasFactory> */
    use HasEditorialRevisions, HasFactory, HasSpoilerConstraints;

    protected $fillable = ['lore_entity_id', 'name', 'normalized_name', 'type', 'locale', 'spoiler_sensitive', 'status', 'created_by', 'updated_by', 'published_at', 'archived_at', 'lock_version'];

    /** @return BelongsTo<LoreEntity, $this> */
    public function loreEntity(): BelongsTo
    {
        return $this->belongsTo(LoreEntity::class);
    }

    protected function casts(): array
    {
        return ['type' => LoreAliasType::class, 'status' => PublicationStatus::class, 'spoiler_sensitive' => 'boolean', 'published_at' => 'immutable_datetime', 'archived_at' => 'immutable_datetime', 'lock_version' => 'integer'];
    }
}
