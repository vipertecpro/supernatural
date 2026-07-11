<?php

namespace App\Models;

use Database\Factories\ContentLicenseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'reference_url', 'attribution_required', 'commercial_use_allowed', 'derivative_use_allowed', 'notes'])]
class ContentLicense extends Model
{
    /** @use HasFactory<ContentLicenseFactory> */
    use HasFactory;

    /** @return HasMany<Source, $this> */
    public function sources(): HasMany
    {
        return $this->hasMany(Source::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'attribution_required' => 'boolean',
            'commercial_use_allowed' => 'boolean',
            'derivative_use_allowed' => 'boolean',
        ];
    }
}
