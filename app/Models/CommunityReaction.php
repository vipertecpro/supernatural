<?php

namespace App\Models;

use App\Enums\CommunityReactionType;
use Database\Factories\CommunityReactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $reactable_type
 * @property int $reactable_id
 * @property CommunityReactionType $type
 */
class CommunityReaction extends Model
{
    /** @use HasFactory<CommunityReactionFactory> */
    use HasFactory;

    protected $table = 'reactions';

    protected $fillable = ['user_id', 'reactable_type', 'reactable_id', 'type'];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return MorphTo<Model, $this> */
    public function reactable(): MorphTo
    {
        return $this->morphTo();
    }

    protected function casts(): array
    {
        return ['type' => CommunityReactionType::class];
    }
}
