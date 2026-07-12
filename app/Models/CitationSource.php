<?php

namespace App\Models;

use Database\Factories\CitationSourceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['citation_id', 'source_id', 'relationship', 'position'])]
class CitationSource extends Model
{
    /** @use HasFactory<CitationSourceFactory> */
    use HasFactory;

    /** @return BelongsTo<Citation, $this> */
    public function citation(): BelongsTo
    {
        return $this->belongsTo(Citation::class);
    }

    /** @return BelongsTo<Source, $this> */
    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }
}
