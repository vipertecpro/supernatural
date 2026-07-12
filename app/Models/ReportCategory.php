<?php

namespace App\Models;

use App\Enums\ReportPriority;
use Database\Factories\ReportCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $key
 * @property string $name
 * @property string $description
 * @property list<string> $applicable_target_types
 * @property ReportPriority $default_priority
 * @property bool $evidence_required
 * @property bool $explanation_required
 * @property bool $rights_review_required
 * @property bool $safety_review_required
 * @property bool $appeals_supported
 * @property bool $is_active
 */
class ReportCategory extends Model
{
    /** @use HasFactory<ReportCategoryFactory> */
    use HasFactory;

    protected $fillable = ['key', 'name', 'description', 'applicable_target_types', 'default_priority', 'evidence_required', 'explanation_required', 'rights_review_required', 'safety_review_required', 'appeals_supported', 'is_active', 'safe_metadata'];

    /** @return HasMany<Report, $this> */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['applicable_target_types' => 'array', 'default_priority' => ReportPriority::class, 'evidence_required' => 'boolean', 'explanation_required' => 'boolean', 'rights_review_required' => 'boolean', 'safety_review_required' => 'boolean', 'appeals_supported' => 'boolean', 'is_active' => 'boolean', 'safe_metadata' => 'array'];
    }
}
