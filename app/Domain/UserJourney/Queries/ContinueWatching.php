<?php

namespace App\Domain\UserJourney\Queries;

use App\Enums\ProgressStatus;
use App\Enums\PublicationStatus;
use App\Models\User;
use App\Models\ViewingProgress;
use Illuminate\Support\Collection;

class ContinueWatching
{
    /** @return Collection<int, ViewingProgress> */
    public function forUser(User $user, int $limit = 20): Collection
    {
        return ViewingProgress::query()
            ->with(['work', 'season', 'episode', 'journey.currentItem'])
            ->where('user_id', $user->id)
            ->where('status', ProgressStatus::InProgress)
            ->whereHas('work', fn ($query) => $query->where('status', PublicationStatus::Published)->where('is_public', true)->whereNull('archived_at'))
            ->where(fn ($query) => $query->whereNull('episode_id')->orWhereHas('episode', fn ($episode) => $episode->where('status', PublicationStatus::Published)->where('is_public', true)->whereNull('archived_at')))
            ->orderByDesc('last_watched_at')
            ->orderByRaw("CASE scope_type WHEN 'episode' THEN 0 WHEN 'season' THEN 1 ELSE 2 END")
            ->orderByDesc('id')
            ->limit(min(max($limit, 1), 50))
            ->get();
    }
}
