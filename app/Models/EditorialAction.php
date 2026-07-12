<?php

namespace App\Models;

use App\Enums\EditorialActionType;
use App\Enums\ReviewCheckResult;
use Carbon\CarbonImmutable;
use Database\Factories\EditorialActionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $editorial_revision_id
 * @property int $actor_user_id
 * @property EditorialActionType $type
 * @property ReviewCheckResult|null $source_result
 * @property ReviewCheckResult|null $rights_result
 * @property ReviewCheckResult|null $spoiler_result
 * @property string|null $public_explanation
 * @property CarbonImmutable $acted_at
 */
#[Fillable(['editorial_revision_id', 'actor_user_id', 'type', 'public_explanation', 'private_note', 'source_result', 'rights_result', 'spoiler_result', 'findings', 'acted_at'])]
class EditorialAction extends Model
{
    /** @use HasFactory<EditorialActionFactory> */
    use HasFactory;

    /** @return BelongsTo<EditorialRevision, $this> */
    public function revision(): BelongsTo
    {
        return $this->belongsTo(EditorialRevision::class, 'editorial_revision_id');
    }

    /** @return BelongsTo<User, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'type' => EditorialActionType::class,
            'source_result' => ReviewCheckResult::class,
            'rights_result' => ReviewCheckResult::class,
            'spoiler_result' => ReviewCheckResult::class,
            'findings' => 'array',
            'acted_at' => 'immutable_datetime',
        ];
    }
}
