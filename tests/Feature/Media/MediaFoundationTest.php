<?php

use App\Domain\Media\Actions\AttachMedia;
use App\Domain\Media\Actions\CreateMediaAsset;
use App\Domain\Media\Actions\TransitionMedia;
use App\Domain\Media\Exceptions\InvalidMediaOperation;
use App\Domain\Media\Services\ExternalEmbedNormalizer;
use App\Enums\ExternalMediaProvider;
use App\Enums\MediaModerationStatus;
use App\Enums\MediaOrigin;
use App\Enums\MediaStatus;
use App\Enums\RightsDecision;
use App\Enums\RightsUseType;
use App\Enums\RoleName;
use App\Models\ExternalEmbed;
use App\Models\MediaAsset;
use App\Models\MediaAttachment;
use App\Models\Source;
use App\Models\SourceRightsReview;
use App\Models\Universe;
use App\Models\User;
use App\Models\Work;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('hosted uploads enter private quarantine with server owned metadata and hidden paths', function () {
    Storage::fake('local');
    $actor = editorialUser(RoleName::Contributor);
    $file = UploadedFile::fake()->image('original.png', 32, 24);

    $asset = app(CreateMediaAsset::class)->handle($file, ['kind' => 'image', 'origin' => MediaOrigin::ProjectOriginal->value, 'alt_text' => 'Original geometric art'], $actor);

    expect($asset->status)->toBe(MediaStatus::Pending)
        ->and($asset->moderation_status)->toBe(MediaModerationStatus::Pending)
        ->and($asset->storage_key)->toStartWith('media/quarantine/')
        ->and($asset->storage_key)->not->toContain('original')
        ->and($asset->width)->toBe(32)
        ->and($asset->height)->toBe(24);
    Storage::disk('local')->assertExists($asset->storage_key);

    $this->actingAs($actor)->getJson(route('api.v1.media.assets.show', $asset))
        ->assertSuccessful()->assertJsonMissingPath('data.storage_key')->assertJsonMissingPath('data.disk')->assertJsonMissingPath('data.original_filename');
});

test('MIME confusion and active document formats are rejected', function () {
    Storage::fake('local');
    $actor = User::factory()->create();
    $file = UploadedFile::fake()->createWithContent('picture.jpg', '<script>alert(1)</script>');

    expect(fn () => app(CreateMediaAsset::class)->handle($file, ['kind' => 'image', 'origin' => 'project_original'], $actor))
        ->toThrow(InvalidMediaOperation::class);
});

test('external embeds normalize only allowlisted provider URLs without HTML', function () {
    $normalizer = app(ExternalEmbedNormalizer::class);
    $youtube = $normalizer->normalize(ExternalMediaProvider::YouTube, 'https://www.youtube.com/watch?v=abc123XYZ');

    expect($youtube['provider_content_id'])->toBe('abc123XYZ')
        ->and($youtube['embed_url'])->toBe('https://www.youtube-nocookie.com/embed/abc123XYZ')
        ->and(fn () => $normalizer->normalize(ExternalMediaProvider::YouTube, 'https://evil.example.test/watch?v=abc'))
        ->toThrow(InvalidMediaOperation::class)
        ->and(fn () => $normalizer->normalize(ExternalMediaProvider::YouTube, '<iframe src="https://www.youtube.com/watch?v=abc"></iframe>'))
        ->toThrow(InvalidMediaOperation::class);
});

test('hosting and embedding permissions remain independent and unknown rights block publication', function () {
    $administrator = editorialUser(RoleName::Administrator);
    $source = Source::factory()->create();
    $asset = MediaAsset::factory()->create(['source_id' => $source->id, 'origin' => MediaOrigin::Licensed, 'moderation_status' => MediaModerationStatus::Approved]);
    $embed = ExternalEmbed::factory()->create(['source_id' => $source->id, 'moderation_status' => MediaModerationStatus::Approved]);
    SourceRightsReview::factory()->create(['source_id' => $source->id, 'use_type' => RightsUseType::Hosting, 'decision' => RightsDecision::Allowed, 'assessed_at' => now()]);

    expect(fn () => app(TransitionMedia::class)->publish($embed, $administrator, 0))->toThrow(InvalidMediaOperation::class);
    $publishedAsset = app(TransitionMedia::class)->publish($asset, $administrator, 0);
    expect($publishedAsset->status)->toBe(MediaStatus::Published);

    SourceRightsReview::factory()->create(['source_id' => $source->id, 'use_type' => RightsUseType::Embedding, 'decision' => RightsDecision::Allowed, 'assessed_at' => now()->addSecond()]);
    expect(app(TransitionMedia::class)->publish($embed, $administrator, 0)->status)->toBe(MediaStatus::Published);
});

test('attachments enforce one source allowlisted targets and universe compatibility', function () {
    $actor = editorialUser(RoleName::Contributor);
    $universe = Universe::factory()->create();
    $work = Work::factory()->for($universe)->create();
    $asset = MediaAsset::factory()->create(['universe_id' => $universe->id]);
    $foreign = MediaAsset::factory()->create(['universe_id' => Universe::factory()]);
    $attributes = ['media_asset_id' => $asset->id, 'attachable_type' => 'work', 'attachable_id' => $work->id, 'role' => 'gallery'];

    expect(app(AttachMedia::class)->create($attributes, $actor))->toBeInstanceOf(MediaAttachment::class)
        ->and(fn () => app(AttachMedia::class)->create($attributes, $actor))->toThrow(InvalidMediaOperation::class)
        ->and(fn () => app(AttachMedia::class)->create([...$attributes, 'media_asset_id' => $foreign->id], $actor))->toThrow(InvalidMediaOperation::class)
        ->and(fn () => app(AttachMedia::class)->create([...$attributes, 'attachable_type' => 'user'], $actor))->toThrow(InvalidMediaOperation::class);
});

test('media write routes enforce authentication verification roles and optimistic locking', function () {
    Storage::fake('local');
    $payload = ['file' => UploadedFile::fake()->image('fixture.png'), 'kind' => 'image', 'origin' => 'project_original'];

    $this->postJson(route('api.v1.media.assets.store'), $payload)->assertUnauthorized();
    $this->actingAs(editorialUser(RoleName::Fan))->postJson(route('api.v1.media.assets.store'), $payload)->assertForbidden();
    $this->actingAs(User::factory()->unverified()->create())->postJson(route('api.v1.media.assets.store'), $payload)->assertForbidden()->assertJsonPath('error.code', 'email_unverified');

    $contributor = editorialUser(RoleName::Contributor);
    $assetId = $this->actingAs($contributor)->postJson(route('api.v1.media.assets.store'), $payload)->assertCreated()->json('data.id');
    $this->actingAs($contributor)->patchJson(route('api.v1.media.assets.update', $assetId), ['expected_version' => 9, 'alt_text' => 'Stale'])->assertConflict()->assertJsonPath('error.code', 'optimistic_lock_conflict');
});
