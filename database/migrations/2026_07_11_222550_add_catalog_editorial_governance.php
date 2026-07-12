<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['franchises', 'work_translations', 'seasons', 'episodes'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->unsignedInteger('lock_version')->default(0)->index();
            });
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE spoiler_constraints MODIFY severity ENUM('none','mild','minor','moderate','major','critical','finale') NOT NULL");
            DB::table('spoiler_constraints')->where('severity', 'mild')->update(['severity' => 'minor']);
            DB::table('spoiler_constraints')->where('severity', 'critical')->update(['severity' => 'finale']);
            DB::statement("ALTER TABLE spoiler_constraints MODIFY severity ENUM('none','minor','moderate','major','finale') NOT NULL");
        } else {
            DB::table('spoiler_constraints')->where('severity', 'mild')->update(['severity' => 'minor']);
            DB::table('spoiler_constraints')->where('severity', 'critical')->update(['severity' => 'finale']);
        }

        Schema::table('spoiler_constraints', function (Blueprint $table): void {
            $table->string('classification_status', 32)->default('draft')->after('severity')->index();
            $table->foreignId('classified_by')->nullable()->after('warning')->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->after('classified_by')->constrained('users')->nullOnDelete();
            $table->timestamp('classified_at')->nullable()->after('reviewed_by');
            $table->timestamp('reviewed_at')->nullable()->after('classified_at');
        });

        Schema::create('editorial_revisions', function (Blueprint $table): void {
            $table->id();
            $table->morphs('revisable');
            $table->foreignId('author_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('parent_revision_id')->nullable()->constrained('editorial_revisions')->restrictOnDelete();
            $table->unsignedInteger('revision_number');
            $table->unsignedInteger('base_version');
            $table->string('status', 32)->default('draft');
            $table->string('summary', 500);
            $table->json('metadata')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('review_started_at')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->timestamp('withdrawn_at')->nullable();
            $table->timestamp('superseded_at')->nullable();
            $table->timestamps();

            $table->unique(['revisable_type', 'revisable_id', 'revision_number'], 'editorial_revisions_target_number_unique');
            $table->index(['status', 'submitted_at', 'id']);
            $table->index(['author_user_id', 'status', 'updated_at']);
        });

        Schema::create('revision_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('editorial_revision_id')->constrained()->restrictOnDelete();
            $table->string('field', 64);
            $table->string('operation', 16)->default('replace');
            $table->string('previous_value_hash', 64)->nullable();
            $table->json('proposed_value')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->json('validation_metadata')->nullable();
            $table->timestamps();

            $table->unique(['editorial_revision_id', 'field'], 'revision_items_revision_field_unique');
            $table->index(['editorial_revision_id', 'position', 'id']);
        });

        Schema::create('revision_blocks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('editorial_revision_id')->constrained()->restrictOnDelete();
            $table->string('field', 64);
            $table->string('locale', 35)->nullable();
            $table->string('original_text_checksum', 64)->nullable();
            $table->longText('proposed_text');
            $table->string('format', 24)->default('plain_text');
            $table->unsignedInteger('position')->default(0);
            $table->boolean('source_required')->default(false);
            $table->boolean('rights_required')->default(false);
            $table->timestamps();

            $table->unique(['editorial_revision_id', 'field', 'locale'], 'revision_blocks_revision_field_locale_unique');
            $table->index(['editorial_revision_id', 'position', 'id']);
        });

        Schema::create('review_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('editorial_revision_id')->constrained()->restrictOnDelete();
            $table->foreignId('reviewer_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('assigned_by_user_id')->constrained('users')->restrictOnDelete();
            $table->string('status', 24)->default('assigned');
            $table->boolean('is_primary')->default(true);
            $table->string('active_primary_key', 16)->nullable();
            $table->timestamp('assigned_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->date('due_at')->nullable();
            $table->string('internal_note', 1000)->nullable();
            $table->timestamps();

            $table->unique(['editorial_revision_id', 'active_primary_key'], 'review_assignments_one_active_primary_unique');
            $table->index(['reviewer_user_id', 'status', 'due_at']);
            $table->index(['editorial_revision_id', 'status']);
        });

        Schema::create('editorial_actions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('editorial_revision_id')->constrained()->restrictOnDelete();
            $table->foreignId('actor_user_id')->constrained('users')->restrictOnDelete();
            $table->string('type', 32);
            $table->string('public_explanation', 2000)->nullable();
            $table->text('private_note')->nullable();
            $table->string('source_result', 24)->nullable();
            $table->string('rights_result', 24)->nullable();
            $table->string('spoiler_result', 24)->nullable();
            $table->json('findings')->nullable();
            $table->timestamp('acted_at');
            $table->timestamps();

            $table->index(['editorial_revision_id', 'acted_at', 'id']);
            $table->index(['actor_user_id', 'type', 'acted_at']);
        });

        Schema::create('citations', function (Blueprint $table): void {
            $table->id();
            $table->morphs('citable');
            $table->string('field_key', 64)->nullable();
            $table->string('locator', 500)->nullable();
            $table->string('page_number', 32)->nullable();
            $table->string('timecode', 32)->nullable();
            $table->string('chapter', 255)->nullable();
            $table->string('section', 255)->nullable();
            $table->string('quotation_excerpt', 500)->nullable();
            $table->string('note', 1000)->nullable();
            $table->string('evidence_strength', 24)->default('supporting');
            $table->string('canon_classification', 24)->default('unknown');
            $table->foreignId('added_by_user_id')->constrained('users')->restrictOnDelete();
            $table->string('review_status', 24)->default('pending');
            $table->timestamps();

            $table->index(['citable_type', 'citable_id', 'field_key'], 'citations_target_field_index');
            $table->index(['review_status', 'evidence_strength', 'id']);
        });

        Schema::create('citation_sources', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('citation_id')->constrained()->restrictOnDelete();
            $table->foreignId('source_id')->constrained()->restrictOnDelete();
            $table->string('relationship', 24)->default('supports');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['citation_id', 'source_id']);
            $table->index(['source_id', 'relationship']);
        });

        Schema::create('source_rights_reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('source_id')->constrained()->restrictOnDelete();
            $table->string('use_type', 32);
            $table->string('decision', 24)->default('unknown');
            $table->string('basis', 1000);
            $table->foreignId('content_license_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('rights_holder')->nullable();
            $table->string('permission_reference', 1000)->nullable();
            $table->foreignId('assessed_by_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('supersedes_review_id')->nullable()->constrained('source_rights_reviews')->restrictOnDelete();
            $table->timestamp('assessed_at');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestamps();

            $table->index(['source_id', 'use_type', 'assessed_at', 'id'], 'source_rights_reviews_lookup_index');
            $table->index(['decision', 'expires_at']);
        });

        Schema::create('spoiler_boundaries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('spoiler_constraint_id')->constrained()->restrictOnDelete();
            $table->foreignId('work_id')->constrained()->restrictOnDelete();
            $table->foreignId('season_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('episode_id')->nullable()->constrained()->restrictOnDelete();
            $table->timestamps();

            $table->unique(['spoiler_constraint_id', 'work_id', 'season_id', 'episode_id'], 'spoiler_boundaries_path_unique');
            $table->index(['work_id', 'season_id', 'episode_id']);
        });

        Schema::create('spoiler_corrections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('spoiler_constraint_id')->constrained()->restrictOnDelete();
            $table->foreignId('corrected_by_user_id')->constrained('users')->restrictOnDelete();
            $table->string('reason', 1000);
            $table->json('previous_classification');
            $table->timestamp('corrected_at');
            $table->timestamps();

            $table->index(['spoiler_constraint_id', 'corrected_at', 'id']);
        });

        Schema::create('user_spoiler_preferences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('universe_id')->constrained()->cascadeOnDelete();
            $table->string('tolerance', 24)->default('strict');
            $table->boolean('show_warnings')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'universe_id']);
        });

        Schema::create('viewing_progress', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('universe_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_id')->constrained()->restrictOnDelete();
            $table->foreignId('season_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('episode_id')->nullable()->constrained()->restrictOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'work_id']);
            $table->index(['user_id', 'universe_id', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('viewing_progress');
        Schema::dropIfExists('user_spoiler_preferences');
        Schema::dropIfExists('spoiler_corrections');
        Schema::dropIfExists('spoiler_boundaries');
        Schema::dropIfExists('source_rights_reviews');
        Schema::dropIfExists('citation_sources');
        Schema::dropIfExists('citations');
        Schema::dropIfExists('editorial_actions');
        Schema::dropIfExists('review_assignments');
        Schema::dropIfExists('revision_blocks');
        Schema::dropIfExists('revision_items');
        Schema::dropIfExists('editorial_revisions');

        Schema::table('spoiler_constraints', function (Blueprint $table): void {
            $table->dropIndex('spoiler_constraints_classification_status_index');
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropConstrainedForeignId('classified_by');
            $table->dropColumn(['classification_status', 'classified_at', 'reviewed_at']);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE spoiler_constraints MODIFY severity ENUM('none','mild','minor','moderate','major','critical','finale') NOT NULL");
            DB::table('spoiler_constraints')->where('severity', 'minor')->update(['severity' => 'mild']);
            DB::table('spoiler_constraints')->whereIn('severity', ['moderate', 'finale'])->update(['severity' => 'critical']);
            DB::statement("ALTER TABLE spoiler_constraints MODIFY severity ENUM('none','mild','major','critical') NOT NULL");
        } else {
            DB::table('spoiler_constraints')->where('severity', 'minor')->update(['severity' => 'mild']);
            DB::table('spoiler_constraints')->whereIn('severity', ['moderate', 'finale'])->update(['severity' => 'critical']);
        }

        foreach (['episodes', 'seasons', 'work_translations', 'franchises'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                $table->dropIndex($tableName.'_lock_version_index');
                $table->dropColumn('lock_version');
            });
        }
    }
};
