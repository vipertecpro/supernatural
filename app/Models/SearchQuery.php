<?php

namespace App\Models;

use App\Enums\SearchDocumentType;
use Database\Factories\SearchQueryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @property int $id */
class SearchQuery extends Model
{
    /** @use HasFactory<SearchQueryFactory> */
    use HasFactory;

    protected $fillable = ['universe_id', 'query_hash', 'query_length', 'locale', 'document_type', 'result_count_bucket', 'occurred_at'];

    /** @return BelongsTo<Universe, $this> */
    public function universe(): BelongsTo
    {
        return $this->belongsTo(Universe::class);
    }

    protected function casts(): array
    {
        return ['document_type' => SearchDocumentType::class, 'occurred_at' => 'immutable_datetime'];
    }
}
