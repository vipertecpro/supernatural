<?php

use App\Domain\UserJourney\Actions\ManagePersonalLibrary;
use App\Enums\RoleName;
use App\Models\AuditLog;
use App\Models\Favourite;
use App\Models\PersonalNote;
use App\Models\Rating;
use App\Models\SearchDocument;
use App\Models\User;
use App\Models\UserFandomPreference;
use App\Models\UserSpoilerPreference;
use App\Models\ViewingProgress;
use App\Models\ViewingSession;
use App\Models\Watchlist;
use App\Models\Work;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('watchlists are private owner scoped default aware and reject duplicates', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $work = Work::factory()->published()->create();

    $response = $this->actingAs($user)->postJson('/api/v1/me/watchlists', ['name' => 'Next Up', 'universe_id' => $work->universe_id, 'is_default' => true])
        ->assertCreated()
        ->assertJsonPath('data.visibility', 'private');
    $watchlistId = $response->json('data.id');
    $this->actingAs($user)->postJson("/api/v1/me/watchlists/{$watchlistId}/items", ['target_type' => 'work', 'target_id' => $work->id])->assertCreated();
    $this->actingAs($user)->postJson("/api/v1/me/watchlists/{$watchlistId}/items", ['target_type' => 'work', 'target_id' => $work->id])->assertConflict()->assertJsonPath('error.code', 'duplicate_watchlist_item');
    $this->actingAs($other)->getJson("/api/v1/me/watchlists/{$watchlistId}")->assertNotFound();

    $second = app(ManagePersonalLibrary::class)->createWatchlist($user, ['name' => 'Later', 'is_default' => true]);
    expect(Watchlist::query()->where('user_id', $user->id)->where('is_default', true)->sole()->id)->toBe($second->id);
});

test('watchlists reject unpublished and cross universe targets and stale writes', function () {
    $user = User::factory()->create();
    $work = Work::factory()->published()->create();
    $other = Work::factory()->published()->create();
    $draft = Work::factory()->for($work->universe)->create();
    $watchlist = app(ManagePersonalLibrary::class)->createWatchlist($user, ['name' => 'Scoped', 'universe_id' => $work->universe_id]);

    $this->actingAs($user)->postJson("/api/v1/me/watchlists/{$watchlist->id}/items", ['target_type' => 'work', 'target_id' => $other->id])->assertConflict()->assertJsonPath('error.code', 'cross_universe_watchlist_item');
    $this->actingAs($user)->postJson("/api/v1/me/watchlists/{$watchlist->id}/items", ['target_type' => 'work', 'target_id' => $draft->id])->assertConflict()->assertJsonPath('error.code', 'journey_target_unavailable');
    $this->actingAs($user)->patchJson("/api/v1/me/watchlists/{$watchlist->id}", ['name' => 'Changed', 'expected_version' => 1])->assertConflict()->assertJsonPath('error.code', 'optimistic_lock_conflict');
});

test('favourites and ratings are private unique constrained and owner scoped', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $work = Work::factory()->published()->create();

    $first = $this->actingAs($user)->postJson('/api/v1/me/favourites', ['target_type' => 'work', 'target_id' => $work->id])->assertCreated()->json('data.id');
    $this->actingAs($user)->postJson('/api/v1/me/favourites', ['target_type' => 'work', 'target_id' => $work->id])->assertCreated();
    expect(Favourite::query()->where('user_id', $user->id)->count())->toBe(1);
    $this->actingAs($other)->deleteJson("/api/v1/me/favourites/{$first}")->assertNotFound();

    $rating = $this->actingAs($user)->putJson("/api/v1/me/ratings/work/{$work->id}", ['rating' => 4])->assertSuccessful()->json('data.id');
    $this->actingAs($user)->putJson("/api/v1/me/ratings/work/{$work->id}", ['rating' => 5])->assertSuccessful()->assertJsonPath('data.rating', 5);
    expect(Rating::query()->where('user_id', $user->id)->count())->toBe(1);
    $this->actingAs($other)->deleteJson("/api/v1/me/ratings/{$rating}")->assertNotFound();
    $this->actingAs($user)->putJson("/api/v1/me/ratings/work/{$work->id}", ['rating' => 6])->assertUnprocessable();
});

test('personal notes strip html hide bodies from lists and never audit note content', function () {
    $user = User::factory()->create();
    $work = Work::factory()->published()->create();
    $secret = '<script>alert(1)</script>Private theory';
    $response = $this->actingAs($user)->postJson('/api/v1/me/notes', ['target_type' => 'work', 'target_id' => $work->id, 'title' => '<b>Thought</b>', 'body' => $secret])->assertCreated();
    $noteId = $response->json('data.id');

    $this->actingAs($user)->getJson('/api/v1/me/notes')->assertSuccessful()->assertJsonMissing(['body' => 'alert(1)Private theory']);
    $this->actingAs($user)->getJson("/api/v1/me/notes/{$noteId}")->assertSuccessful()->assertJsonPath('data.body', 'alert(1)Private theory')->assertJsonPath('data.visibility', 'private');
    $this->actingAs($user)->patchJson("/api/v1/me/notes/{$noteId}", ['body' => 'Updated private body', 'expected_version' => 0])->assertSuccessful();
    $this->actingAs($user)->patchJson("/api/v1/me/notes/{$noteId}", ['body' => 'Stale body', 'expected_version' => 0])->assertConflict();

    expect(AuditLog::query()->where('auditable_type', 'personal_note')->get()->pluck('metadata')->flatten()->all())->not->toContain($secret, 'Updated private body');
});

test('personal notes and records cannot be accessed by contributor moderator or administrator without ownership', function (RoleName $role) {
    $owner = User::factory()->create();
    $other = editorialUser($role);
    $note = PersonalNote::factory()->for($owner)->create();

    $this->actingAs($other)->getJson("/api/v1/me/notes/{$note->id}")->assertNotFound();
})->with([RoleName::Contributor, RoleName::Moderator, RoleName::Administrator]);

test('journey preferences reject unknown keys remain private and integrate spoiler tolerance', function () {
    $user = User::factory()->create();
    $work = Work::factory()->published()->create();

    $this->actingAs($user)->patchJson('/api/v1/me/journey-preferences', ['universe_id' => $work->universe_id, 'tracking_profile' => 'not-allowed'])->assertUnprocessable();
    $this->actingAs($user)->patchJson('/api/v1/me/journey-preferences', ['universe_id' => $work->universe_id, 'default_locale' => 'en-gb', 'auto_complete_progress' => true, 'tolerance' => 'warn', 'show_warnings' => false, 'expected_version' => 0])->assertSuccessful()->assertJsonPath('data.continue_watching_visibility', 'private');

    expect(UserFandomPreference::query()->where('user_id', $user->id)->sole()->default_locale)->toBe('en-gb')
        ->and(UserSpoilerPreference::query()->where('user_id', $user->id)->sole()->tolerance->value)->toBe('warn');
});

test('search adds private progress only for the authenticated viewer and never changes ranking', function () {
    $user = User::factory()->create();
    $work = Work::factory()->published()->create(['original_title' => 'Copper Signal']);
    SearchDocument::factory()->create(['source_type' => 'work', 'source_id' => $work->id, 'universe_id' => $work->universe_id, 'canonical_title' => 'Copper Signal', 'normalized_text' => 'copper signal']);
    ViewingProgress::factory()->create(['user_id' => $user->id, 'universe_id' => $work->universe_id, 'work_id' => $work->id, 'status' => 'in_progress', 'progress_basis_points' => 2500, 'completed_at' => null]);

    $guest = $this->getJson('/api/v1/search?q=Copper')->assertSuccessful();
    $guest->assertJsonMissingPath('data.0.viewing_status')->assertJsonMissingPath('data.0.progress_basis_points');
    $this->actingAs($user)->getJson('/api/v1/search?q=Copper')->assertSuccessful()->assertJsonPath('data.0.viewing_status', 'in_progress')->assertJsonPath('data.0.progress_basis_points', 2500);
});

test('account deletion removes all identifiable User Journey records', function () {
    $user = User::factory()->create();
    $work = Work::factory()->published()->create();
    $watchlist = Watchlist::factory()->for($user)->create();
    Favourite::factory()->for($user)->create(['target_id' => $work->id, 'universe_id' => $work->universe_id]);
    Rating::factory()->for($user)->create(['target_id' => $work->id, 'universe_id' => $work->universe_id]);
    PersonalNote::factory()->for($user)->create(['target_id' => $work->id, 'universe_id' => $work->universe_id]);
    ViewingProgress::factory()->for($user)->create(['universe_id' => $work->universe_id, 'work_id' => $work->id]);
    ViewingSession::factory()->for($user)->create(['work_id' => $work->id]);

    $user->delete();

    expect(Watchlist::query()->whereKey($watchlist)->exists())->toBeFalse()
        ->and(Favourite::query()->where('user_id', $user->id)->exists())->toBeFalse()
        ->and(Rating::query()->where('user_id', $user->id)->exists())->toBeFalse()
        ->and(PersonalNote::withTrashed()->where('user_id', $user->id)->exists())->toBeFalse()
        ->and(ViewingProgress::query()->where('user_id', $user->id)->exists())->toBeFalse()
        ->and(ViewingSession::query()->where('user_id', $user->id)->exists())->toBeFalse();
});
