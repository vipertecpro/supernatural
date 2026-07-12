<?php

use App\Domain\Editorial\Actions\ApplyEditorialRevision;
use App\Domain\Editorial\Actions\AssignEditorialReview;
use App\Domain\Editorial\Actions\CreateCitation;
use App\Domain\Editorial\Actions\CreateEditorialRevision;
use App\Domain\Editorial\Actions\DecideEditorialRevision;
use App\Domain\Editorial\Actions\RecordSourceRightsReview;
use App\Domain\Editorial\Actions\TransitionEditorialRevision;
use App\Domain\Editorial\Actions\UpsertRevisionBlock;
use App\Domain\Editorial\Actions\UpsertRevisionItem;
use App\Domain\Editorial\Exceptions\InvalidEditorialOperation;
use App\Domain\Editorial\Exceptions\OptimisticLockConflict;
use App\Domain\Spoilers\Actions\UpsertSpoilerBoundary;
use App\Enums\CanonClassification;
use App\Enums\CitationEvidenceStrength;
use App\Enums\CitationReviewStatus;
use App\Enums\EditorialRevisionStatus;
use App\Enums\RightsDecision;
use App\Enums\RightsUseType;
use App\Enums\RoleName;
use App\Enums\SpoilerClassificationStatus;
use App\Enums\SpoilerSeverity;
use App\Models\AuditLog;
use App\Models\Franchise;
use App\Models\RevisionItem;
use App\Models\Source;
use App\Models\SourceRightsReview;
use App\Models\Universe;
use App\Models\WorkTranslation;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('an attributable revision is approved and applied transactionally once', function () {
    $author = editorialUser(RoleName::Contributor);
    $reviewer = editorialUser(RoleName::Administrator);
    $applier = editorialUser(RoleName::Administrator);
    $franchise = Franchise::factory()->for(Universe::factory())->create(['created_by' => $author->id, 'position' => 1]);
    $revision = app(CreateEditorialRevision::class)->handle($franchise, ['summary' => 'Correct ordering metadata.'], $author);
    app(UpsertRevisionItem::class)->handle($revision, 'position', 4);
    app(TransitionEditorialRevision::class)->submit($revision->fresh(), $author);
    app(AssignEditorialReview::class)->handle($revision->fresh(), $reviewer, $applier);
    app(DecideEditorialRevision::class)->beginReview($revision->fresh(), $reviewer);
    app(DecideEditorialRevision::class)->approve($revision->fresh(), $reviewer, 'The proposed ordering is ready.');
    $applied = app(ApplyEditorialRevision::class)->handle($revision->fresh(), $applier);

    expect($applied->status)->toBe(EditorialRevisionStatus::Applied)
        ->and($franchise->fresh()->position)->toBe(4)
        ->and($franchise->fresh()->lock_version)->toBe(1)
        ->and($applied->actions()->where('type', 'approved')->count())->toBe(1)
        ->and($applied->actions()->where('type', 'applied')->count())->toBe(1)
        ->and(AuditLog::query()->where('event', 'editorial.revision_applied')->exists())->toBeTrue();

    expect(fn () => app(ApplyEditorialRevision::class)->handle($applied, $applier))->toThrow(InvalidEditorialOperation::class);
});

test('unsupported and protected fields cannot enter a revision', function () {
    $author = editorialUser(RoleName::Contributor);
    $franchise = Franchise::factory()->create(['created_by' => $author->id]);
    $revision = app(CreateEditorialRevision::class)->handle($franchise, ['summary' => 'Attempt protected mutation.'], $author);

    expect(fn () => app(UpsertRevisionItem::class)->handle($revision, 'created_by', 999))
        ->toThrow(InvalidEditorialOperation::class);
});

test('a concurrent Catalog update invalidates an approved revision', function () {
    $author = editorialUser(RoleName::Contributor);
    $reviewer = editorialUser(RoleName::Administrator);
    $franchise = Franchise::factory()->create(['created_by' => $author->id, 'position' => 1]);
    $revision = app(CreateEditorialRevision::class)->handle($franchise, ['summary' => 'Change ordering.'], $author);
    app(UpsertRevisionItem::class)->handle($revision, 'position', 2);
    app(TransitionEditorialRevision::class)->submit($revision->fresh(), $author);
    app(AssignEditorialReview::class)->handle($revision->fresh(), $reviewer, $reviewer);
    app(DecideEditorialRevision::class)->approve($revision->fresh(), $reviewer, 'Approved.');
    $franchise->update(['position' => 3, 'lock_version' => 1]);

    expect(fn () => app(ApplyEditorialRevision::class)->handle($revision->fresh(), $reviewer))
        ->toThrow(OptimisticLockConflict::class)
        ->and($franchise->fresh()->position)->toBe(3)
        ->and($revision->fresh()->status)->toBe(EditorialRevisionStatus::Approved);
});

test('review assignment prevents self review and preserves reassignment history', function () {
    $author = editorialUser(RoleName::Administrator);
    $firstReviewer = editorialUser(RoleName::Administrator);
    $secondReviewer = editorialUser(RoleName::Administrator);
    $revision = app(CreateEditorialRevision::class)->handle(Franchise::factory()->create(['created_by' => $author->id]), ['summary' => 'Review assignment.'], $author);
    app(UpsertRevisionItem::class)->handle($revision, 'position', 2);
    app(TransitionEditorialRevision::class)->submit($revision->fresh(), $author);

    expect(fn () => app(AssignEditorialReview::class)->handle($revision->fresh(), $author, $author))
        ->toThrow(InvalidEditorialOperation::class);

    app(AssignEditorialReview::class)->handle($revision->fresh(), $firstReviewer, $author);
    $active = app(AssignEditorialReview::class)->handle($revision->fresh(), $secondReviewer, $author);

    expect($revision->assignments()->count())->toBe(2)
        ->and($revision->assignments()->where('status', 'cancelled')->count())->toBe(1)
        ->and($revision->assignments()->whereNotNull('active_primary_key')->value('reviewer_user_id'))->toBe($secondReviewer->id);

    app(AssignEditorialReview::class)->cancel($active, $author);
    expect($revision->assignments()->whereNotNull('active_primary_key')->exists())->toBeFalse()
        ->and($revision->assignments()->where('status', 'cancelled')->count())->toBe(2);
});

test('changes requested decisions remain immutable through resubmission', function () {
    $author = editorialUser(RoleName::Contributor);
    $reviewer = editorialUser(RoleName::Administrator);
    $revision = app(CreateEditorialRevision::class)->handle(Franchise::factory()->create(['created_by' => $author->id]), ['summary' => 'Initial proposal.'], $author);
    app(UpsertRevisionItem::class)->handle($revision, 'position', 2);
    app(TransitionEditorialRevision::class)->submit($revision->fresh(), $author);
    app(AssignEditorialReview::class)->handle($revision->fresh(), $reviewer, $reviewer);
    app(DecideEditorialRevision::class)->requestChanges($revision->fresh(), $reviewer, 'Clarify the ordering basis.', 'Private reviewer context.');
    app(UpsertRevisionItem::class)->handle($revision->fresh(), 'position', 3);
    app(TransitionEditorialRevision::class)->resubmit($revision->fresh(), $author);

    expect($revision->fresh()->status)->toBe(EditorialRevisionStatus::Submitted)
        ->and($revision->actions()->where('type', 'changes_requested')->count())->toBe(1)
        ->and($revision->actions()->where('type', 'resubmitted')->count())->toBe(1);
});

test('source rights decisions are tri-state attributable and historical', function () {
    $actor = editorialUser(RoleName::Administrator);
    $source = Source::factory()->create();
    $unknown = app(RecordSourceRightsReview::class)->handle($source, RightsUseType::Hosting, RightsDecision::Unknown, ['basis' => 'No permission evidence.'], $actor);
    $allowed = app(RecordSourceRightsReview::class)->handle($source, RightsUseType::Hosting, RightsDecision::Allowed, ['basis' => 'Written permission reviewed.'], $actor);

    expect($unknown->isEffective())->toBeFalse()
        ->and($allowed->isEffective())->toBeTrue()
        ->and($allowed->supersedes_review_id)->toBe($unknown->id)
        ->and(SourceRightsReview::query()->count())->toBe(2);
});

test('unknown quotation rights prevent approval of a sourced synopsis', function () {
    $author = editorialUser(RoleName::Contributor);
    $reviewer = editorialUser(RoleName::Administrator);
    $translation = WorkTranslation::factory()->create(['created_by' => $author->id, 'synopsis' => 'Original synopsis.']);
    $revision = app(CreateEditorialRevision::class)->handle($translation, ['summary' => 'Replace a sourced synopsis.'], $author);
    $block = app(UpsertRevisionBlock::class)->handle($revision, 'synopsis', $translation->locale, 'An original proposed synopsis for a fictional work.');
    $source = Source::factory()->create(['universe_id' => $translation->work->universe_id, 'source_type' => 'official']);
    app(CreateCitation::class)->handle($block, [
        'evidence_strength' => CitationEvidenceStrength::Primary,
        'canon_classification' => CanonClassification::Official,
        'review_status' => CitationReviewStatus::Verified,
    ], [$source->id], $reviewer, $revision);
    app(RecordSourceRightsReview::class)->handle($source, RightsUseType::Quotation, RightsDecision::Unknown, ['basis' => 'Permission has not been demonstrated.'], $reviewer);
    app(UpsertSpoilerBoundary::class)->handle($block, $translation->work, null, null, SpoilerSeverity::Major, SpoilerClassificationStatus::Approved, $reviewer);
    app(TransitionEditorialRevision::class)->submit($revision->fresh(), $author);
    app(AssignEditorialReview::class)->handle($revision->fresh(), $reviewer, $reviewer);

    expect(fn () => app(DecideEditorialRevision::class)->approve($revision->fresh(), $reviewer, 'Review complete.'))
        ->toThrow(InvalidEditorialOperation::class)
        ->and($revision->fresh()->status)->toBe(EditorialRevisionStatus::Submitted);
});

test('a failure during application rolls back every proposed change', function () {
    $author = editorialUser(RoleName::Contributor);
    $reviewer = editorialUser(RoleName::Administrator);
    $franchise = Franchise::factory()->create(['created_by' => $author->id, 'position' => 1]);
    $revision = app(CreateEditorialRevision::class)->handle($franchise, ['summary' => 'Atomic application.'], $author);
    app(UpsertRevisionItem::class)->handle($revision, 'position', 9);
    app(TransitionEditorialRevision::class)->submit($revision->fresh(), $author);
    app(AssignEditorialReview::class)->handle($revision->fresh(), $reviewer, $reviewer);
    app(DecideEditorialRevision::class)->approve($revision->fresh(), $reviewer, 'Approved.');
    RevisionItem::factory()->create([
        'editorial_revision_id' => $revision->id,
        'field' => 'created_by',
        'previous_value_hash' => hash('sha256', json_encode($author->id)),
        'proposed_value' => ['value' => $reviewer->id],
    ]);

    expect(fn () => app(ApplyEditorialRevision::class)->handle($revision->fresh(), $reviewer))
        ->toThrow(InvalidEditorialOperation::class)
        ->and($franchise->fresh()->position)->toBe(1)
        ->and($franchise->fresh()->lock_version)->toBe(0)
        ->and($revision->fresh()->status)->toBe(EditorialRevisionStatus::Approved);
});

test('audit metadata does not retain revision or private decision text', function () {
    $author = editorialUser(RoleName::Contributor);
    $revision = app(CreateEditorialRevision::class)->handle(Franchise::factory()->create(['created_by' => $author->id]), ['summary' => 'Sensitive proposed narrative.'], $author);
    app(UpsertRevisionItem::class)->handle($revision, 'position', 5);
    app(TransitionEditorialRevision::class)->submit($revision->fresh(), $author);

    $auditPayload = AuditLog::query()->pluck('metadata')->toJson();
    expect($auditPayload)->not->toContain('Sensitive proposed narrative');
});
