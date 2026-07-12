<?php

namespace App\Models;

use Database\Factories\OrganizationDetailFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationDetail extends Model
{
    /** @use HasFactory<OrganizationDetailFactory> */
    use HasFactory;

    protected $fillable = ['lore_entity_id', 'organization_type', 'lifecycle_status', 'founded_description'];

    /** @return BelongsTo<LoreEntity, $this> */
    public function loreEntity(): BelongsTo
    {
        return $this->belongsTo(LoreEntity::class);
    }
}
