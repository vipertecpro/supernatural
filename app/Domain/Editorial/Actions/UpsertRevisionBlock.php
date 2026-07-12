<?php

namespace App\Domain\Editorial\Actions;

use App\Domain\Editorial\Exceptions\InvalidEditorialOperation;
use App\Domain\Editorial\Services\CatalogEditorialFieldRegistry;
use App\Models\EditorialRevision;
use App\Models\RevisionBlock;

class UpsertRevisionBlock
{
    public function __construct(private readonly CatalogEditorialFieldRegistry $registry) {}

    public function handle(EditorialRevision $revision, string $field, ?string $locale, string $text, int $position = 0): RevisionBlock
    {
        if (! $revision->status->isEditable()) {
            throw new InvalidEditorialOperation('Only draft or changes-requested revisions may be edited.');
        }

        $target = $revision->revisable()->firstOrFail();
        $definition = $this->registry->definition($target, $field);
        if ($definition['kind'] !== 'text') {
            throw new InvalidEditorialOperation('Scalar fields must use revision items.', 'revision_item_required');
        }

        $normalized = $this->registry->normalize($target, $field, $text);
        $current = $target->getAttribute($field);

        return $revision->blocks()->updateOrCreate(['field' => $field, 'locale' => $locale], [
            'original_text_checksum' => $this->registry->fingerprint($current),
            'proposed_text' => $normalized,
            'format' => 'plain_text',
            'position' => $position,
            'source_required' => $definition['source'],
            'rights_required' => $definition['rights'],
        ]);
    }
}
