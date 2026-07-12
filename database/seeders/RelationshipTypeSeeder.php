<?php

namespace Database\Seeders;

use App\Enums\LoreEntityType;
use App\Enums\RelationshipDirection;
use App\Models\RelationshipType;
use Illuminate\Database\Seeder;

class RelationshipTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $definitions = [
            'related_to' => ['forward_label' => 'related to', 'inverse_label' => 'related to', 'direction' => RelationshipDirection::Undirected, 'is_symmetric' => true, 'rules' => collect(LoreEntityType::cases())->flatMap(fn (LoreEntityType $source) => collect(LoreEntityType::cases())->map(fn (LoreEntityType $target): array => [$source, $target]))->all()],
            'portrayed_by' => ['forward_label' => 'portrayed by', 'inverse_label' => 'portrays', 'direction' => RelationshipDirection::Directed, 'is_symmetric' => false, 'rules' => [[LoreEntityType::Character, LoreEntityType::Performer]]],
            'located_in' => ['forward_label' => 'located in', 'inverse_label' => 'contains', 'direction' => RelationshipDirection::Directed, 'is_symmetric' => false, 'rules' => collect(LoreEntityType::cases())->map(fn (LoreEntityType $source): array => [$source, LoreEntityType::Location])->all()],
        ];

        foreach ($definitions as $key => $definition) {
            $rules = $definition['rules'];
            unset($definition['rules']);
            $type = RelationshipType::query()->updateOrCreate(['key' => $key], [...$definition, 'is_transitive' => false, 'allows_self' => false, 'allows_duplicates' => false, 'allows_temporal_bounds' => true, 'requires_catalog_boundary' => false, 'requires_citation' => true, 'requires_spoiler_classification' => true, 'requires_editorial_approval' => true, 'is_active' => true, 'metadata' => []]);
            $type->rules()->delete();
            foreach ($rules as [$source, $target]) {
                $type->rules()->create(['source_entity_type' => $source, 'target_entity_type' => $target]);
            }
        }
    }
}
