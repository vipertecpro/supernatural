<?php

namespace App\Models;

use App\Enums\LoreEntityType;
use Database\Factories\RelationshipTypeRuleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $relationship_type_id
 * @property LoreEntityType $source_entity_type
 * @property LoreEntityType $target_entity_type
 */
class RelationshipTypeRule extends Model
{
    /** @use HasFactory<RelationshipTypeRuleFactory> */
    use HasFactory;

    protected $fillable = ['relationship_type_id', 'source_entity_type', 'target_entity_type'];

    /** @return BelongsTo<RelationshipType, $this> */
    public function relationshipType(): BelongsTo
    {
        return $this->belongsTo(RelationshipType::class);
    }

    protected function casts(): array
    {
        return ['source_entity_type' => LoreEntityType::class, 'target_entity_type' => LoreEntityType::class];
    }
}
