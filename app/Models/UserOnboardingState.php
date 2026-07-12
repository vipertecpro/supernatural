<?php

namespace App\Models;

use App\Enums\OnboardingStep;
use Database\Factories\UserOnboardingStateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property OnboardingStep $current_step
 * @property Carbon|null $started_at
 * @property Carbon|null $last_activity_at
 * @property Carbon|null $completed_at
 * @property int $lock_version
 */
#[Fillable(['user_id', 'current_step', 'started_at', 'last_activity_at', 'completed_at', 'lock_version'])]
class UserOnboardingState extends Model
{
    /** @use HasFactory<UserOnboardingStateFactory> */
    use HasFactory;

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isCompleted(): bool
    {
        return $this->current_step === OnboardingStep::Completed && $this->completed_at !== null;
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'current_step' => OnboardingStep::class,
            'started_at' => 'immutable_datetime',
            'last_activity_at' => 'immutable_datetime',
            'completed_at' => 'immutable_datetime',
            'lock_version' => 'integer',
        ];
    }
}
