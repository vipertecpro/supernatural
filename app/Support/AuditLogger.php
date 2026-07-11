<?php

namespace App\Support;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AuditLogger
{
    /** @var list<string> */
    private const SENSITIVE_KEY_FRAGMENTS = [
        'authorization',
        'cookie',
        'credential',
        'password',
        'privatekey',
        'recoverycode',
        'secret',
        'session',
        'token',
    ];

    /**
     * Record a security-relevant action using sanitized metadata.
     *
     * @param  array<string, mixed>  $metadata
     */
    public function record(
        string $event,
        ?Model $auditable = null,
        array $metadata = [],
        ?User $actor = null,
        ?string $requestId = null,
    ): AuditLog {
        return AuditLog::query()->create([
            'actor_user_id' => $actor?->getKey(),
            'event' => $event,
            'auditable_type' => $auditable?->getMorphClass(),
            'auditable_id' => $auditable?->getKey(),
            'metadata' => $this->sanitize($metadata),
            'request_id' => $requestId ?? $this->currentRequestId(),
        ]);
    }

    /**
     * Remove sensitive values from audit metadata recursively.
     *
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    public function sanitize(array $metadata): array
    {
        $safe = [];

        foreach ($metadata as $key => $value) {
            $normalizedKey = str($key)->lower()->replaceMatches('/[^a-z0-9]/', '')->toString();

            if (collect(self::SENSITIVE_KEY_FRAGMENTS)->contains(
                fn (string $fragment): bool => str_contains($normalizedKey, $fragment),
            )) {
                continue;
            }

            $safe[$key] = is_array($value) ? $this->sanitize($value) : $value;
        }

        return $safe;
    }

    /** Resolve a correlation identifier without retaining request state. */
    private function currentRequestId(): string
    {
        if (app()->bound('request')) {
            $requestId = request()->attributes->get('request_id')
                ?? request()->headers->get('X-Request-ID');

            if (is_string($requestId) && $requestId !== '') {
                return str($requestId)->limit(64, '')->toString();
            }
        }

        return (string) Str::uuid();
    }
}
