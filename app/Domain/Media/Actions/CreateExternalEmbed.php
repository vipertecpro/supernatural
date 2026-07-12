<?php

namespace App\Domain\Media\Actions;

use App\Domain\Media\Services\ExternalEmbedNormalizer;
use App\Enums\ExternalMediaProvider;
use App\Enums\MediaModerationStatus;
use App\Enums\MediaStatus;
use App\Enums\MediaVisibility;
use App\Models\ExternalEmbed;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;

class CreateExternalEmbed
{
    public function __construct(private readonly ExternalEmbedNormalizer $normalizer, private readonly AuditLogger $auditLogger) {}

    /** @param array<string, mixed> $attributes */
    public function handle(array $attributes, User $actor): ExternalEmbed
    {
        $provider = ExternalMediaProvider::from((string) $attributes['provider']);
        $normalized = $this->normalizer->normalize($provider, (string) $attributes['url']);
        unset($attributes['url']);

        return DB::transaction(function () use ($attributes, $normalized, $provider, $actor): ExternalEmbed {
            $embed = ExternalEmbed::query()->create([
                ...$attributes,
                ...$normalized,
                'provider' => $provider,
                'owner_user_id' => $actor->id,
                'status' => MediaStatus::Pending,
                'moderation_status' => MediaModerationStatus::Pending,
                'visibility' => MediaVisibility::Private,
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ]);
            $this->auditLogger->record('media.external_embed_created', $embed, ['provider' => $provider->value], $actor);

            return $embed;
        });
    }
}
