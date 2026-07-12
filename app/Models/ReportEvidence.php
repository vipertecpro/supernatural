<?php

namespace App\Models;

use App\Enums\EvidenceVisibility;
use App\Enums\ReportEvidenceType;
use Database\Factories\ReportEvidenceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $report_id
 * @property int|null $created_by_user_id
 * @property ReportEvidenceType $type
 * @property EvidenceVisibility $visibility
 * @property string|null $description
 * @property int|null $media_asset_id
 * @property int|null $source_id
 * @property int|null $citation_id
 * @property string|null $external_url
 * @property mixed $created_at
 */
class ReportEvidence extends Model
{
    /** @use HasFactory<ReportEvidenceFactory> */
    use HasFactory;

    protected $table = 'report_evidence';

    protected $fillable = ['report_id', 'created_by_user_id', 'type', 'visibility', 'description', 'media_asset_id', 'source_id', 'citation_id', 'external_url', 'checksum', 'safe_metadata'];

    /** @return BelongsTo<Report, $this> */
    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['type' => ReportEvidenceType::class, 'visibility' => EvidenceVisibility::class, 'safe_metadata' => 'array'];
    }
}
