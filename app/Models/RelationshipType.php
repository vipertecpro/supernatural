<?php

namespace App\Models;

use App\Enums\RelationshipDirection;
use Database\Factories\RelationshipTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $key
 * @property string $forward_label
 * @property string $inverse_label
 * @property RelationshipDirection $direction
 * @property bool $is_symmetric
 * @property bool $allows_self
 * @property bool $allows_duplicates
 * @property bool $allows_temporal_bounds
 * @property bool $requires_catalog_boundary
 * @property bool $requires_citation
 * @property bool $requires_spoiler_classification
 * @property bool $is_active
 */
class RelationshipType extends Model
{
    /** @use HasFactory<RelationshipTypeFactory> */
    use HasFactory;

    protected $fillable = ['key', 'forward_label', 'inverse_label', 'direction', 'is_symmetric', 'is_transitive', 'allows_self', 'allows_duplicates', 'allows_temporal_bounds', 'requires_catalog_boundary', 'requires_citation', 'requires_spoiler_classification', 'requires_editorial_approval', 'is_active', 'metadata'];

    /** @return HasMany<RelationshipTypeRule, $this> */
    public function rules(): HasMany
    {
        return $this->hasMany(RelationshipTypeRule::class);
    }

    /** @return HasMany<LoreRelationship, $this> */
    public function relationships(): HasMany
    {
        return $this->hasMany(LoreRelationship::class);
    }

    protected function casts(): array
    {
        return ['direction' => RelationshipDirection::class, 'is_symmetric' => 'boolean', 'is_transitive' => 'boolean', 'allows_self' => 'boolean', 'allows_duplicates' => 'boolean', 'allows_temporal_bounds' => 'boolean', 'requires_catalog_boundary' => 'boolean', 'requires_citation' => 'boolean', 'requires_spoiler_classification' => 'boolean', 'requires_editorial_approval' => 'boolean', 'is_active' => 'boolean', 'metadata' => 'array'];
    }
}
