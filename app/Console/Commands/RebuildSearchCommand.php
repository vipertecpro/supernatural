<?php

namespace App\Console\Commands;

use App\Domain\Search\Services\SearchProjector;
use App\Models\Episode;
use App\Models\SearchDocument;
use App\Models\Season;
use App\Models\Universe;
use App\Support\AuditLogger;
use Illuminate\Console\Command;

class RebuildSearchCommand extends Command
{
    protected $signature = 'search:rebuild {--type=} {--universe=} {--locale=} {--chunk=100} {--prune} {--dry-run}';

    protected $description = 'Rebuild relational search projections from authoritative public records';

    public function handle(SearchProjector $projector, AuditLogger $auditLogger): int
    {
        $types = collect(SearchProjector::SOURCE_TYPES)->mapWithKeys(fn ($type, $class) => [$type->value => $class]);
        $requestedType = $this->option('type');
        if (is_string($requestedType) && $requestedType !== '' && ! $types->has($requestedType)) {
            $this->error('Unsupported search document type.');

            return self::INVALID;
        }
        $universeId = $this->option('universe');
        if ($universeId !== null && (! ctype_digit((string) $universeId) || ! Universe::query()->whereKey((int) $universeId)->exists())) {
            $this->error('The universe identifier is invalid.');

            return self::INVALID;
        }
        $chunk = min(max((int) $this->option('chunk'), 1), 1000);
        $dryRun = (bool) $this->option('dry-run');
        $counts = ['created' => 0, 'updated' => 0, 'unchanged' => 0, 'skipped' => 0, 'removed' => 0];
        $selected = $requestedType ? collect([$requestedType => $types->get($requestedType)]) : $types;
        foreach ($selected as $class) {
            $query = $class::query();
            if ($universeId !== null) {
                if ($class === Universe::class) {
                    $query->whereKey((int) $universeId);
                } elseif (in_array($class, [Season::class, Episode::class], true)) {
                    $query->whereHas('work', fn ($work) => $work->where('universe_id', (int) $universeId));
                } else {
                    $query->where('universe_id', (int) $universeId);
                }
            }
            $query->chunkById($chunk, function ($sources) use ($projector, &$counts, $dryRun): void {
                foreach ($sources as $source) {
                    foreach ($projector->project($source, is_string($this->option('locale')) ? $this->option('locale') : null, $dryRun) as $key => $value) {
                        $counts[$key] += $value;
                    }
                }
            });
        }
        if ((bool) $this->option('prune')) {
            $valid = [];
            $selectedMorphTypes = [];
            foreach ($selected as $class) {
                $selectedMorphTypes[] = (new $class)->getMorphClass();
                foreach ($class::query()->pluck('id') as $id) {
                    $valid[] = [(new $class)->getMorphClass(), (int) $id];
                }
            }
            $stale = SearchDocument::query()->whereIn('source_type', $selectedMorphTypes)->get()->filter(fn (SearchDocument $document): bool => ! collect($valid)->contains(fn (array $key): bool => $key[0] === $document->source_type && $key[1] === $document->source_id));
            $counts['removed'] += $stale->count();
            if (! $dryRun) {
                SearchDocument::query()->whereKey($stale->pluck('id'))->delete();
            }
        }
        $this->table(array_keys($counts), [array_values($counts)]);
        $auditLogger->record('search.projection_rebuild_completed', metadata: [...$counts, 'dry_run' => $dryRun, 'prune' => (bool) $this->option('prune')]);

        return self::SUCCESS;
    }
}
