<?php

use App\Actions\Authorization\AssignRole;
use App\Domain\Editorial\Actions\AssignEditorialReview;
use App\Domain\Editorial\Actions\CreateEditorialRevision;
use App\Domain\Editorial\Actions\TransitionEditorialRevision;
use App\Domain\Editorial\Actions\UpsertRevisionItem;
use App\Enums\RoleName;
use App\Models\Franchise;
use App\Models\User;
use App\Models\Work;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('a verified contributor creates edits and submits an own Catalog revision', function () {
    $contributor = User::factory()->create();
    app(AssignRole::class)->handle($contributor, RoleName::Contributor);
    $franchise = Franchise::factory()->create(['created_by' => $contributor->id]);

    $created = $this->actingAs($contributor)->postJson('/api/v1/editorial/revisions', [
        'target_type' => 'franchise',
        'target_id' => $franchise->id,
        'summary' => 'Adjust the editorial ordering.',
    ])->assertCreated()->assertJsonPath('data.status', 'draft')->assertJsonPath('data.base_version', 0);

    $revisionId = $created->json('data.id');
    $this->actingAs($contributor)->postJson("/api/v1/editorial/revisions/{$revisionId}/items", [
        'field' => 'position',
        'operation' => 'replace',
        'value' => 7,
    ])->assertCreated()->assertJsonPath('data.proposed_value', 7);
    $this->actingAs($contributor)->postJson("/api/v1/editorial/revisions/{$revisionId}/submit")
        ->assertSuccessful()->assertJsonPath('data.status', 'submitted');
});

test('fans and unverified contributors cannot create revisions', function () {
    $fan = User::factory()->create();
    app(AssignRole::class)->handle($fan, RoleName::Fan);
    $unverified = User::factory()->unverified()->create();
    app(AssignRole::class)->handle($unverified, RoleName::Contributor);
    $franchise = Franchise::factory()->create(['created_by' => $unverified->id]);
    $payload = ['target_type' => 'franchise', 'target_id' => $franchise->id, 'summary' => 'Draft correction.'];

    $this->actingAs($fan)->postJson('/api/v1/editorial/revisions', $payload)->assertForbidden();
    $this->actingAs($unverified)->postJson('/api/v1/editorial/revisions', $payload)
        ->assertForbidden()->assertJsonPath('error.code', 'email_unverified');
});

test('a contributor cannot view or edit another contributors revision', function () {
    $owner = editorialUser(RoleName::Contributor);
    $other = editorialUser(RoleName::Contributor);
    $revision = app(CreateEditorialRevision::class)->handle(Franchise::factory()->create(['created_by' => $owner->id]), ['summary' => 'Owner proposal.'], $owner);

    $this->actingAs($other)->getJson("/api/v1/editorial/revisions/{$revision->id}")->assertForbidden();
    $this->actingAs($other)->patchJson("/api/v1/editorial/revisions/{$revision->id}", ['summary' => 'Unauthorized rewrite.'])->assertForbidden();
});

test('approval reports missing source checks without exposing private notes', function () {
    $author = editorialUser(RoleName::Contributor);
    $reviewer = editorialUser(RoleName::Administrator);
    $work = Work::factory()->create(['created_by' => $author->id]);
    $revision = app(CreateEditorialRevision::class)->handle($work, ['summary' => 'Correct the canonical title.'], $author);
    app(UpsertRevisionItem::class)->handle($revision, 'original_title', 'A Revised Original Title');
    app(TransitionEditorialRevision::class)->submit($revision->fresh(), $author);
    app(AssignEditorialReview::class)->handle($revision->fresh(), $reviewer, $reviewer, 'Assignment note must remain private.');

    $this->actingAs($reviewer)->postJson("/api/v1/editorial/revisions/{$revision->id}/approve", [
        'explanation' => 'Evidence reviewed.',
        'private_note' => 'Private legal and reviewer context.',
    ])->assertConflict()
        ->assertJsonPath('error.code', 'editorial_checks_incomplete')
        ->assertJsonMissing(['private_note' => 'Private legal and reviewer context.']);
});

test('stale direct Catalog updates return the stable conflict envelope', function () {
    $administrator = editorialUser(RoleName::Administrator);
    $franchise = Franchise::factory()->create(['lock_version' => 2]);

    $this->actingAs($administrator)->patchJson("/api/v1/franchises/{$franchise->id}", [
        'expected_version' => 1,
        'name' => 'Stale overwrite attempt',
    ])->assertConflict()
        ->assertJsonPath('error.code', 'optimistic_lock_conflict')
        ->assertJsonPath('data', null);
});

test('editorial resources never serialize private assignment or reviewer notes', function () {
    $author = editorialUser(RoleName::Contributor);
    $reviewer = editorialUser(RoleName::Administrator);
    $revision = app(CreateEditorialRevision::class)->handle(Franchise::factory()->create(['created_by' => $author->id]), ['summary' => 'Ordering update.'], $author);
    app(UpsertRevisionItem::class)->handle($revision, 'position', 6);
    app(TransitionEditorialRevision::class)->submit($revision->fresh(), $author);
    app(AssignEditorialReview::class)->handle($revision->fresh(), $reviewer, $reviewer, 'Private assignment note.');

    $response = $this->actingAs($reviewer)->getJson("/api/v1/editorial/revisions/{$revision->id}")->assertSuccessful();
    expect($response->getContent())->not->toContain('Private assignment note');
});

test('role permission seeding remains idempotent with editorial permissions', function () {
    $before = DB::table('permission_role')->count();
    $this->seed(RolePermissionSeeder::class);

    expect(DB::table('permission_role')->count())->toBe($before);
});
