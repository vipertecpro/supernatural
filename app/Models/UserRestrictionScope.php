<?php

namespace App\Models;

use App\Enums\RestrictionScope;
use Database\Factories\UserRestrictionScopeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_restriction_id
 * @property RestrictionScope $scope
 */
class UserRestrictionScope extends Model
{
    /** @use HasFactory<UserRestrictionScopeFactory> */
    use HasFactory;

    protected $fillable = ['user_restriction_id', 'scope'];

    /** @return BelongsTo<UserRestriction, $this> */
    public function restriction(): BelongsTo
    {
        return $this->belongsTo(UserRestriction::class, 'user_restriction_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['scope' => RestrictionScope::class];
    }
}
