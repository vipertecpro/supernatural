<?php

namespace App\Domain\Editorial\Actions;

use App\Domain\Editorial\Exceptions\InvalidEditorialOperation;
use App\Domain\Editorial\Services\CatalogEditorialFieldRegistry;
use App\Enums\RevisionOperation;
use App\Models\EditorialRevision;
use App\Models\RevisionItem;

class UpsertRevisionItem
{
    public function __construct(private readonly CatalogEditorialFieldRegistry $registry) {}

    public function handle(EditorialRevision $revision, string $field, mixed $value, RevisionOperation $operation = RevisionOperation::Replace, int $position = 0): RevisionItem
    {
        if (! $revision->status->isEditable()) {
            throw new InvalidEditorialOperation('Only draft or changes-requested revisions may be edited.');
        }

        $target = $revision->revisable()->firstOrFail();
        if ($this->registry->isText($target, $field)) {
            throw new InvalidEditorialOperation('Large text fields must use revision blocks.', 'revision_block_required');
        }

        $normalized = $operation === RevisionOperation::Remove ? null : $this->registry->normalize($target, $field, $value);
        $current = $target->getAttribute($field);
        $current = $current instanceof \BackedEnum ? $current->value : $current;

        return $revision->items()->updateOrCreate(['field' => $field], [
            'operation' => $operation,
            'previous_value_hash' => $this->registry->fingerprint($current),
            'proposed_value' => ['value' => $normalized],
            'position' => $position,
            'validation_metadata' => ['registry_version' => 1],
        ]);
    }
}
