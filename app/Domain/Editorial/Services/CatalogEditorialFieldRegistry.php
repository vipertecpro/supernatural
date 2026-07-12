<?php

namespace App\Domain\Editorial\Services;

use App\Domain\Editorial\Exceptions\InvalidEditorialOperation;
use App\Enums\AppearanceKind;
use App\Enums\AppearanceSignificance;
use App\Enums\CanonClassification;
use App\Enums\CanonStatus;
use App\Enums\DatePrecision;
use App\Enums\EpisodeType;
use App\Enums\LoreAliasType;
use App\Enums\RelationshipConfidence;
use App\Enums\SeasonType;
use App\Enums\TimelineEntryType;
use App\Enums\TimelineType;
use App\Enums\WorkReleaseStatus;
use App\Enums\WorkType;
use App\Models\Episode;
use App\Models\Franchise;
use App\Models\Season;
use App\Models\Work;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CatalogEditorialFieldRegistry
{
    /**
     * Return an allowlisted field definition for a Catalog target.
     *
     * @return array{kind: string, rules: array<mixed>, source: bool, source_types: list<string>, rights: bool, spoiler: bool, public: bool}
     */
    public function definition(Model $target, string $field): array
    {
        $definition = $this->definitions()[$target->getMorphClass()][$field] ?? null;

        if (! is_array($definition)) {
            throw new InvalidEditorialOperation('The requested field is not editable through editorial revisions.', 'unsupported_revision_field');
        }

        return $definition;
    }

    /** @return list<string> */
    public function fieldsFor(Model $target): array
    {
        return array_keys($this->definitions()[$target->getMorphClass()] ?? []);
    }

    public function isText(Model $target, string $field): bool
    {
        return $this->definition($target, $field)['kind'] === 'text';
    }

    public function normalize(Model $target, string $field, mixed $value): mixed
    {
        $definition = $this->definition($target, $field);
        $validator = Validator::make(['value' => $value], ['value' => $definition['rules']]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return match ($definition['kind']) {
            'string' => is_string($value) ? trim(preg_replace('/\s+/u', ' ', $value) ?? $value) : $value,
            'text' => is_string($value) ? trim(str_replace(["\r\n", "\r"], "\n", $value)) : $value,
            'locale' => is_string($value) ? str($value)->replace('_', '-')->lower()->toString() : $value,
            'integer' => $value === null ? null : (int) $value,
            'boolean' => (bool) $value,
            default => $value,
        };
    }

    public function fingerprint(mixed $value): string
    {
        return hash('sha256', json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    /**
     * @return array<string, array<string, array{kind: string, rules: array<mixed>, source: bool, source_types: list<string>, rights: bool, spoiler: bool, public: bool}>>
     */
    private function definitions(): array
    {
        $plain = fn (string $kind, array $rules, bool $source = false, bool $rights = false, bool $spoiler = false, array $sourceTypes = ['official', 'reference']): array => [
            'kind' => $kind,
            'rules' => $rules,
            'source' => $source,
            'source_types' => $source ? array_values($sourceTypes) : [],
            'rights' => $rights,
            'spoiler' => $spoiler,
            'public' => true,
        ];

        return [
            'franchise' => [
                'name' => $plain('string', ['required', 'string', 'max:255'], true),
                'description' => $plain('text', ['nullable', 'string', 'max:5000'], false, false, true),
                'position' => $plain('integer', ['required', 'integer', 'min:0', 'max:4294967295']),
            ],
            'work' => [
                'franchise_id' => $plain('integer', ['nullable', 'integer', Rule::exists(Franchise::class, 'id')]),
                'type' => $plain('enum', ['required', Rule::enum(WorkType::class)], true),
                'original_title' => $plain('string', ['required', 'string', 'max:255'], true),
                'original_language' => $plain('locale', ['required', 'string', 'max:35'], true),
                'summary' => $plain('text', ['nullable', 'string', 'max:5000'], false, false, true),
                'runtime_minutes' => $plain('integer', ['nullable', 'integer', 'min:1', 'max:14400'], true),
                'release_status' => $plain('enum', ['required', Rule::enum(WorkReleaseStatus::class)], true),
                'canon_status' => $plain('enum', ['required', Rule::enum(CanonStatus::class)], true),
                'original_release_date' => $plain('date', ['nullable', 'date_format:Y-m-d'], true),
                'release_date_precision' => $plain('enum', ['nullable', Rule::enum(DatePrecision::class)], true),
            ],
            'work_translation' => [
                'title' => $plain('string', ['required', 'string', 'max:255'], true),
                'short_title' => $plain('string', ['nullable', 'string', 'max:255']),
                'summary' => $plain('text', ['nullable', 'string', 'max:5000'], false, false, true),
                'synopsis' => $plain('text', ['nullable', 'string', 'max:20000'], true, true, true),
                'translated_from_locale' => $plain('locale', ['nullable', 'string', 'max:35']),
            ],
            'season' => [
                'type' => $plain('enum', ['required', Rule::enum(SeasonType::class)], true),
                'number' => $plain('integer', ['nullable', 'integer', 'min:0']),
                'display_number' => $plain('string', ['nullable', 'string', 'max:255']),
                'title' => $plain('string', ['required', 'string', 'max:255'], true),
                'summary' => $plain('text', ['nullable', 'string', 'max:5000'], false, false, true),
                'position' => $plain('integer', ['required', 'integer', 'min:0']),
                'original_release_date' => $plain('date', ['nullable', 'date_format:Y-m-d'], true),
                'release_date_precision' => $plain('enum', ['nullable', Rule::enum(DatePrecision::class)], true),
            ],
            'episode' => [
                'season_id' => $plain('integer', ['nullable', 'integer', Rule::exists(Season::class, 'id')]),
                'episode_number' => $plain('integer', ['nullable', 'integer', 'min:0']),
                'display_number' => $plain('string', ['nullable', 'string', 'max:255']),
                'absolute_number' => $plain('integer', ['nullable', 'integer', 'min:0']),
                'production_code' => $plain('string', ['nullable', 'string', 'max:255'], true),
                'type' => $plain('enum', ['required', Rule::enum(EpisodeType::class)], true),
                'title' => $plain('string', ['required', 'string', 'max:255'], true),
                'summary' => $plain('text', ['nullable', 'string', 'max:5000'], false, false, true),
                'synopsis' => $plain('text', ['nullable', 'string', 'max:20000'], true, true, true),
                'runtime_minutes' => $plain('integer', ['nullable', 'integer', 'min:1', 'max:14400'], true),
                'original_release_date' => $plain('date', ['nullable', 'date_format:Y-m-d'], true),
                'release_date_precision' => $plain('enum', ['nullable', Rule::enum(DatePrecision::class)], true),
                'position' => $plain('integer', ['required', 'integer', 'min:0']),
            ],
            'lore_entity' => [
                'canonical_name' => $plain('string', ['required', 'string', 'max:255'], true),
                'short_description' => $plain('text', ['nullable', 'string', 'max:1000'], false, false, true),
                'summary' => $plain('text', ['nullable', 'string', 'max:20000'], false, false, true),
                'original_language' => $plain('locale', ['required', 'string', 'max:35'], true),
                'canon_classification' => $plain('enum', ['required', Rule::enum(CanonClassification::class)], true),
            ],
            'lore_entity_translation' => [
                'name' => $plain('string', ['required', 'string', 'max:255'], true),
                'short_name' => $plain('string', ['nullable', 'string', 'max:255']),
                'short_description' => $plain('text', ['nullable', 'string', 'max:1000'], false, false, true),
                'summary' => $plain('text', ['nullable', 'string', 'max:20000'], false, false, true),
                'source_locale' => $plain('locale', ['nullable', 'string', 'max:35']),
            ],
            'lore_alias' => [
                'name' => $plain('string', ['required', 'string', 'max:255'], true),
                'type' => $plain('enum', ['required', Rule::enum(LoreAliasType::class)]),
                'locale' => $plain('locale', ['nullable', 'string', 'max:35']),
                'spoiler_sensitive' => $plain('boolean', ['required', 'boolean'], false, false, true),
            ],
            'entity_appearance' => [
                'work_id' => $plain('integer', ['required', 'integer', Rule::exists(Work::class, 'id')], true),
                'season_id' => $plain('integer', ['nullable', 'integer', Rule::exists(Season::class, 'id')]),
                'episode_id' => $plain('integer', ['nullable', 'integer', Rule::exists(Episode::class, 'id')]),
                'kind' => $plain('enum', ['required', Rule::enum(AppearanceKind::class)], true),
                'significance' => $plain('enum', ['required', Rule::enum(AppearanceSignificance::class)]),
                'is_credited' => $plain('boolean', ['nullable', 'boolean']),
                'position' => $plain('integer', ['required', 'integer', 'min:0']),
                'canon_classification' => $plain('enum', ['required', Rule::enum(CanonClassification::class)], true),
                'notes' => $plain('text', ['nullable', 'string', 'max:5000'], false, false, true),
            ],
            'lore_relationship' => [
                'canon_classification' => $plain('enum', ['required', Rule::enum(CanonClassification::class)], true),
                'confidence' => $plain('enum', ['required', Rule::enum(RelationshipConfidence::class)], true),
                'start_work_id' => $plain('integer', ['nullable', 'integer', Rule::exists(Work::class, 'id')]),
                'start_season_id' => $plain('integer', ['nullable', 'integer', Rule::exists(Season::class, 'id')]),
                'start_episode_id' => $plain('integer', ['nullable', 'integer', Rule::exists(Episode::class, 'id')]),
                'end_work_id' => $plain('integer', ['nullable', 'integer', Rule::exists(Work::class, 'id')]),
                'end_season_id' => $plain('integer', ['nullable', 'integer', Rule::exists(Season::class, 'id')]),
                'end_episode_id' => $plain('integer', ['nullable', 'integer', Rule::exists(Episode::class, 'id')]),
                'starts_on' => $plain('date', ['nullable', 'date_format:Y-m-d']),
                'ends_on' => $plain('date', ['nullable', 'date_format:Y-m-d']),
                'date_precision' => $plain('enum', ['nullable', Rule::enum(DatePrecision::class)]),
                'qualifier' => $plain('string', ['nullable', 'string', 'max:1000'], true, false, true),
            ],
            'timeline' => [
                'name' => $plain('string', ['required', 'string', 'max:255'], true),
                'type' => $plain('enum', ['required', Rule::enum(TimelineType::class)]),
                'description' => $plain('text', ['nullable', 'string', 'max:10000'], false, false, true),
                'canon_classification' => $plain('enum', ['required', Rule::enum(CanonClassification::class)], true),
            ],
            'timeline_entry' => [
                'type' => $plain('enum', ['required', Rule::enum(TimelineEntryType::class)]),
                'title' => $plain('string', ['required', 'string', 'max:255'], true),
                'summary' => $plain('text', ['nullable', 'string', 'max:10000'], false, false, true),
                'sort_key' => $plain('decimal', ['required', 'numeric', 'min:-999999999999', 'max:999999999999']),
                'sequence_number' => $plain('integer', ['nullable', 'integer', 'min:0']),
                'in_universe_date' => $plain('date', ['nullable', 'date_format:Y-m-d']),
                'date_precision' => $plain('enum', ['nullable', Rule::enum(DatePrecision::class)]),
                'relative_order' => $plain('string', ['nullable', 'string', 'max:255']),
                'canon_classification' => $plain('enum', ['required', Rule::enum(CanonClassification::class)], true),
                'confidence' => $plain('enum', ['required', Rule::enum(RelationshipConfidence::class)], true),
            ],
        ];
    }
}
