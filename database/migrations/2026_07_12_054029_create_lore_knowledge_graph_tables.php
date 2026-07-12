<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lore_entities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('universe_id')->constrained()->restrictOnDelete();
            $table->string('type', 32);
            $table->string('slug');
            $table->string('canonical_name');
            $table->string('short_description', 1000)->nullable();
            $table->longText('summary')->nullable();
            $table->string('original_language', 35)->default('en');
            $table->string('status', 24)->default('draft');
            $table->string('canon_classification', 24)->default('unknown');
            $table->string('visibility', 24)->default('restricted');
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();

            $table->unique(['universe_id', 'type', 'slug']);
            $table->index(['universe_id', 'type', 'status', 'published_at', 'id'], 'lore_entities_public_index');
            $table->index(['status', 'visibility', 'archived_at']);
        });

        Schema::create('lore_entity_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lore_entity_id')->constrained()->restrictOnDelete();
            $table->string('locale', 35);
            $table->string('name');
            $table->string('short_name')->nullable();
            $table->string('short_description', 1000)->nullable();
            $table->longText('summary')->nullable();
            $table->string('source_locale', 35)->nullable();
            $table->string('status', 24)->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();

            $table->unique(['lore_entity_id', 'locale']);
            $table->index(['locale', 'status', 'published_at']);
        });

        Schema::create('lore_aliases', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lore_entity_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('normalized_name');
            $table->string('type', 32)->default('alternate_name');
            $table->string('locale', 35)->nullable();
            $table->boolean('spoiler_sensitive')->default(false);
            $table->string('status', 24)->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();

            $table->unique(['lore_entity_id', 'normalized_name', 'type', 'locale'], 'lore_aliases_scope_unique');
            $table->index(['normalized_name', 'status']);
        });

        Schema::create('character_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lore_entity_id')->unique()->constrained()->restrictOnDelete();
            $table->string('category')->nullable();
            $table->string('lifecycle_status')->nullable();
            $table->string('birth_or_origin', 1000)->nullable();
            $table->string('pronouns', 100)->nullable();
            $table->foreignId('species_entity_id')->nullable()->constrained('lore_entities')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('performer_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lore_entity_id')->unique()->constrained()->restrictOnDelete();
            $table->string('professional_name')->nullable();
            $table->text('production_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('location_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lore_entity_id')->unique()->constrained()->restrictOnDelete();
            $table->string('location_type')->nullable();
            $table->foreignId('parent_location_entity_id')->nullable()->constrained('lore_entities')->restrictOnDelete();
            $table->string('classification')->nullable();
            $table->timestamps();
        });

        Schema::create('artifact_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lore_entity_id')->unique()->constrained()->restrictOnDelete();
            $table->string('category')->nullable();
            $table->text('function')->nullable();
            $table->text('usage_constraints')->nullable();
            $table->timestamps();
        });

        Schema::create('organization_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lore_entity_id')->unique()->constrained()->restrictOnDelete();
            $table->string('organization_type')->nullable();
            $table->string('lifecycle_status')->nullable();
            $table->string('founded_description', 1000)->nullable();
            $table->timestamps();
        });

        Schema::create('lore_event_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lore_entity_id')->unique()->constrained()->restrictOnDelete();
            $table->string('event_type')->nullable();
            $table->date('occurred_on')->nullable();
            $table->string('date_precision', 24)->nullable();
            $table->foreignId('work_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('season_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('episode_id')->nullable()->constrained()->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('concept_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lore_entity_id')->unique()->constrained()->restrictOnDelete();
            $table->string('category')->nullable();
            $table->string('classification')->nullable();
            $table->timestamps();
        });

        Schema::create('entity_taxonomies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('universe_id')->constrained()->restrictOnDelete();
            $table->string('scope', 24)->default('general');
            $table->string('key');
            $table->string('name');
            $table->string('description', 1000)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['universe_id', 'scope', 'key']);
        });

        Schema::create('entity_taxonomy_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('entity_taxonomy_id')->constrained()->restrictOnDelete();
            $table->foreignId('lore_entity_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['entity_taxonomy_id', 'lore_entity_id'], 'entity_taxonomy_items_unique');
            $table->index(['lore_entity_id', 'position']);
        });

        Schema::create('entity_appearances', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lore_entity_id')->constrained()->restrictOnDelete();
            $table->foreignId('work_id')->constrained()->restrictOnDelete();
            $table->foreignId('season_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('episode_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('kind', 24);
            $table->string('significance', 24)->default('unknown');
            $table->boolean('is_credited')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->string('canon_classification', 24)->default('unknown');
            $table->text('notes')->nullable();
            $table->string('status', 24)->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();

            $table->unique(['lore_entity_id', 'work_id', 'season_id', 'episode_id', 'kind'], 'entity_appearances_target_unique');
            $table->index(['work_id', 'season_id', 'episode_id', 'position'], 'entity_appearances_catalog_index');
            $table->index(['lore_entity_id', 'status', 'position']);
        });

        Schema::create('relationship_types', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->string('forward_label');
            $table->string('inverse_label');
            $table->string('direction', 24)->default('directed');
            $table->boolean('is_symmetric')->default(false);
            $table->boolean('is_transitive')->default(false);
            $table->boolean('allows_self')->default(false);
            $table->boolean('allows_duplicates')->default(false);
            $table->boolean('allows_temporal_bounds')->default(true);
            $table->boolean('requires_catalog_boundary')->default(false);
            $table->boolean('requires_citation')->default(true);
            $table->boolean('requires_spoiler_classification')->default(true);
            $table->boolean('requires_editorial_approval')->default(true);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('relationship_type_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('relationship_type_id')->constrained()->restrictOnDelete();
            $table->string('source_entity_type', 32);
            $table->string('target_entity_type', 32);
            $table->timestamps();

            $table->unique(['relationship_type_id', 'source_entity_type', 'target_entity_type'], 'relationship_type_rules_unique');
        });

        Schema::create('lore_relationships', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('source_entity_id')->constrained('lore_entities')->restrictOnDelete();
            $table->foreignId('target_entity_id')->constrained('lore_entities')->restrictOnDelete();
            $table->foreignId('relationship_type_id')->constrained()->restrictOnDelete();
            $table->string('canon_classification', 24)->default('unknown');
            $table->string('confidence', 24)->default('unknown');
            $table->string('status', 24)->default('draft');
            $table->foreignId('start_work_id')->nullable()->constrained('works')->restrictOnDelete();
            $table->foreignId('start_season_id')->nullable()->constrained('seasons')->restrictOnDelete();
            $table->foreignId('start_episode_id')->nullable()->constrained('episodes')->restrictOnDelete();
            $table->foreignId('end_work_id')->nullable()->constrained('works')->restrictOnDelete();
            $table->foreignId('end_season_id')->nullable()->constrained('seasons')->restrictOnDelete();
            $table->foreignId('end_episode_id')->nullable()->constrained('episodes')->restrictOnDelete();
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->string('date_precision', 24)->nullable();
            $table->string('qualifier', 1000)->nullable();
            $table->text('editorial_note')->nullable();
            $table->string('dispute_reason', 1000)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();

            $table->unique(['source_entity_id', 'relationship_type_id', 'target_entity_id', 'status'], 'lore_relationships_active_unique');
            $table->index(['source_entity_id', 'relationship_type_id', 'status', 'target_entity_id'], 'lore_relationships_outgoing_index');
            $table->index(['target_entity_id', 'relationship_type_id', 'status', 'source_entity_id'], 'lore_relationships_incoming_index');
        });

        Schema::create('timelines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('universe_id')->constrained()->restrictOnDelete();
            $table->foreignId('lore_entity_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('work_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('type', 32);
            $table->text('description')->nullable();
            $table->string('canon_classification', 24)->default('unknown');
            $table->string('status', 24)->default('draft');
            $table->string('visibility', 24)->default('restricted');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();

            $table->unique(['universe_id', 'slug']);
            $table->index(['universe_id', 'type', 'status', 'published_at', 'id']);
        });

        Schema::create('timeline_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('timeline_id')->constrained()->restrictOnDelete();
            $table->string('type', 32);
            $table->foreignId('work_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('season_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('episode_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('lore_event_entity_id')->nullable()->constrained('lore_entities')->restrictOnDelete();
            $table->foreignId('lore_relationship_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('title');
            $table->text('summary')->nullable();
            $table->decimal('sort_key', 20, 6);
            $table->unsignedInteger('sequence_number')->nullable();
            $table->date('in_universe_date')->nullable();
            $table->string('date_precision', 24)->nullable();
            $table->string('relative_order', 255)->nullable();
            $table->string('canon_classification', 24)->default('unknown');
            $table->string('confidence', 24)->default('unknown');
            $table->string('status', 24)->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();

            $table->unique(['timeline_id', 'sort_key'], 'timeline_entries_order_unique');
            $table->index(['timeline_id', 'status', 'sort_key', 'id']);
        });

        Schema::create('timeline_entry_entities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('timeline_entry_id')->constrained()->restrictOnDelete();
            $table->foreignId('lore_entity_id')->constrained()->restrictOnDelete();
            $table->string('role', 64)->default('subject');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['timeline_entry_id', 'lore_entity_id', 'role'], 'timeline_entry_entities_unique');
            $table->index(['lore_entity_id', 'timeline_entry_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timeline_entry_entities');
        Schema::dropIfExists('timeline_entries');
        Schema::dropIfExists('timelines');
        Schema::dropIfExists('lore_relationships');
        Schema::dropIfExists('relationship_type_rules');
        Schema::dropIfExists('relationship_types');
        Schema::dropIfExists('entity_appearances');
        Schema::dropIfExists('entity_taxonomy_items');
        Schema::dropIfExists('entity_taxonomies');
        Schema::dropIfExists('concept_details');
        Schema::dropIfExists('lore_event_details');
        Schema::dropIfExists('organization_details');
        Schema::dropIfExists('artifact_details');
        Schema::dropIfExists('location_details');
        Schema::dropIfExists('performer_details');
        Schema::dropIfExists('character_details');
        Schema::dropIfExists('lore_aliases');
        Schema::dropIfExists('lore_entity_translations');
        Schema::dropIfExists('lore_entities');
    }
};
