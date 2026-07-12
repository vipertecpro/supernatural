<?php

namespace App\Domain\Media\Actions;

use App\Domain\Media\Exceptions\InvalidMediaOperation;
use App\Enums\MediaModerationStatus;
use App\Enums\MediaProcessingStatus;
use App\Enums\MediaStatus;
use App\Enums\MediaVisibility;
use App\Models\MediaAsset;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CreateMediaAsset
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /**
     * Admit an upload into private quarantine and persist only server-derived file metadata.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function handle(UploadedFile $file, array $attributes, User $actor): MediaAsset
    {
        $mime = (string) $file->getMimeType();
        $extension = strtolower($file->getClientOriginalExtension());
        $allowedExtensions = config("media.mime_types.{$mime}");
        if (! is_array($allowedExtensions) || ! in_array($extension, $allowedExtensions, true)) {
            throw new InvalidMediaOperation('The detected MIME type does not match an approved file extension.', 'unsafe_media_file');
        }

        $disk = (string) config('media.quarantine_disk', 'local');
        $storageKey = 'media/quarantine/'.Str::uuid().'.'.$extension;
        $checksum = hash_file('sha256', $file->getRealPath());
        if ($checksum === false) {
            throw new InvalidMediaOperation('The uploaded file could not be checksummed.', 'media_checksum_failed');
        }
        $dimensions = str_starts_with($mime, 'image/') ? @getimagesize($file->getRealPath()) : false;
        if (str_starts_with($mime, 'image/') && $dimensions === false) {
            throw new InvalidMediaOperation('The uploaded image payload is invalid.', 'unsafe_media_file');
        }
        Storage::disk($disk)->putFileAs(dirname($storageKey), $file, basename($storageKey));

        try {
            return DB::transaction(function () use ($file, $attributes, $actor, $disk, $storageKey, $mime, $extension, $checksum, $dimensions): MediaAsset {
                $displayName = str((string) pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))->ascii()->replaceMatches('/[^A-Za-z0-9._ -]/', '')->squish()->limit(120, '')->toString();
                $asset = MediaAsset::query()->create([
                    ...$attributes,
                    'owner_user_id' => $actor->id,
                    'disk' => $disk,
                    'storage_key' => $storageKey,
                    'original_filename' => $file->getClientOriginalName(),
                    'display_filename' => ($displayName !== '' ? $displayName : 'media').'.'.$extension,
                    'mime_type' => $mime,
                    'extension' => $extension,
                    'size_bytes' => $file->getSize(),
                    'checksum' => $checksum,
                    'width' => is_array($dimensions) ? $dimensions[0] : null,
                    'height' => is_array($dimensions) ? $dimensions[1] : null,
                    'status' => MediaStatus::Pending,
                    'moderation_status' => MediaModerationStatus::Pending,
                    'processing_status' => MediaProcessingStatus::Ready,
                    'visibility' => MediaVisibility::Private,
                    'uploaded_at' => now(),
                    'created_by' => $actor->id,
                    'updated_by' => $actor->id,
                ]);
                $this->auditLogger->record('media.asset_created', $asset, ['kind' => $asset->kind->value, 'size_bytes' => $asset->size_bytes], $actor);

                return $asset;
            });
        } catch (\Throwable $exception) {
            Storage::disk($disk)->delete($storageKey);
            throw $exception;
        }
    }
}
