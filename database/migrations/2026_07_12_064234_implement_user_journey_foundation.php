<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('viewing_orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('universe_id')->constrained()->restrictOnDelete();
            $table->foreignId('franchise_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('type', 32);
            $table->string('status', 32)->default('draft');
            $table->string('visibility', 24)->default('private');
            $table->boolean('is_default')->default(false);
            $table->string('default_key', 16)->nullable();
            $table->string('locale', 35)->default('en');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();

            $table->unique(['universe_id', 'slug']);
            $table->unique(['universe_id', 'default_key'], 'viewing_orders_one_default_per_universe');
            $table->index(['universe_id', 'status', 'visibility', 'published_at'], 'viewing_orders_public_index');
        });

        Schema::create('viewing_order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('viewing_order_id')->constrained()->restrictOnDelete();
            $table->string('target_type', 64);
            $table->unsignedBigInteger('target_id');
            $table->unsignedInteger('position');
            $table->string('group_label')->nullable();
            $table->string('display_title')->nullable();
            $table->text('explanation')->nullable();
            $table->boolean('is_optional')->default(false);
            $table->boolean('is_skippable')->default(false);
            $table->foreignId('spoiler_constraint_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();

            $table->unique(['viewing_order_id', 'position']);
            $table->unique(['viewing_order_id', 'target_type', 'target_id'], 'viewing_order_items_target_unique');
            $table->index(['target_type', 'target_id']);
        });

        Schema::create('rewatch_cycles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('universe_id')->constrained()->restrictOnDelete();
            $table->foreignId('work_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('viewing_order_id')->nullable()->constrained()->restrictOnDelete();
            $table->unsignedInteger('cycle_number');
            $table->string('status', 24)->default('active');
            $table->string('active_key', 16)->nullable();
            $table->string('visibility', 24)->default('private');
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('abandoned_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'universe_id', 'work_id', 'cycle_number'], 'rewatch_cycles_number_unique');
            $table->unique(['user_id', 'universe_id', 'work_id', 'active_key'], 'rewatch_cycles_one_active_unique');
            $table->index(['user_id', 'status', 'started_at']);
        });

        Schema::create('user_viewing_journeys', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('universe_id')->constrained()->restrictOnDelete();
            $table->foreignId('viewing_order_id')->constrained()->restrictOnDelete();
            $table->foreignId('rewatch_cycle_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('status', 24)->default('active');
            $table->string('active_key', 16)->nullable();
            $table->foreignId('current_item_id')->nullable()->constrained('viewing_order_items')->nullOnDelete();
            $table->foreignId('current_work_id')->nullable()->constrained('works')->restrictOnDelete();
            $table->foreignId('current_season_id')->nullable()->constrained('seasons')->restrictOnDelete();
            $table->foreignId('current_episode_id')->nullable()->constrained('episodes')->restrictOnDelete();
            $table->string('visibility', 24)->default('private');
            $table->timestamp('started_at');
            $table->timestamp('paused_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('abandoned_at')->nullable();
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'universe_id', 'active_key'], 'user_viewing_journeys_one_active_unique');
            $table->index(['user_id', 'status', 'updated_at']);
            $table->index(['viewing_order_id', 'status']);
        });

        Schema::create('viewing_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_viewing_journey_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('rewatch_cycle_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('work_id')->constrained()->restrictOnDelete();
            $table->foreignId('season_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('episode_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('status', 24)->default('active');
            $table->string('source', 24)->default('manual');
            $table->string('client_session_id', 100);
            $table->timestamp('started_at');
            $table->timestamp('last_activity_at');
            $table->timestamp('ended_at')->nullable();
            $table->unsignedInteger('starting_position_seconds')->default(0);
            $table->unsignedInteger('ending_position_seconds')->default(0);
            $table->unsignedInteger('watched_seconds')->default(0);
            $table->json('safe_metadata')->nullable();
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'client_session_id']);
            $table->index(['user_id', 'status', 'last_activity_at']);
            $table->index(['episode_id', 'status']);
        });

        Schema::table('viewing_progress', function (Blueprint $table): void {
            $table->dropUnique('viewing_progress_user_id_work_id_unique');
            $table->foreignId('user_viewing_journey_id')->nullable()->after('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('rewatch_cycle_id')->nullable()->after('user_viewing_journey_id')->constrained()->restrictOnDelete();
            $table->string('scope_type', 24)->nullable()->after('rewatch_cycle_id');
            $table->string('scope_key', 64)->nullable()->after('scope_type');
            $table->unsignedBigInteger('cycle_key')->default(0)->after('scope_key');
            $table->string('status', 24)->default('completed')->after('episode_id');
            $table->unsignedSmallInteger('progress_basis_points')->default(10000)->after('status');
            $table->unsignedInteger('runtime_position_seconds')->nullable()->after('progress_basis_points');
            $table->timestamp('started_at')->nullable()->after('runtime_position_seconds');
            $table->timestamp('last_watched_at')->nullable()->after('started_at');
            $table->timestamp('completed_at')->nullable()->after('last_watched_at');
            $table->string('source', 24)->default('legacy')->after('completed_at');
            $table->boolean('is_manual_override')->default(false)->after('source');
            $table->boolean('is_legacy_projection')->default(true)->after('is_manual_override');
            $table->string('last_request_id', 100)->nullable()->after('is_legacy_projection');
            $table->unsignedInteger('lock_version')->default(0)->after('last_request_id');
        });

        DB::table('viewing_progress')->orderBy('id')->each(function (object $progress): void {
            $scopeType = $progress->episode_id !== null ? 'episode' : ($progress->season_id !== null ? 'season' : 'work');
            $scopeId = $progress->{$scopeType.'_id'};

            DB::table('viewing_progress')->where('id', $progress->id)->update([
                'scope_type' => $scopeType,
                'scope_key' => $scopeType.':'.$scopeId,
                'started_at' => $progress->created_at,
                'last_watched_at' => $progress->updated_at,
                'completed_at' => $progress->updated_at,
            ]);
        });

        Schema::table('viewing_progress', function (Blueprint $table): void {
            $table->string('scope_type', 24)->nullable(false)->change();
            $table->string('scope_key', 64)->nullable(false)->change();
            $table->unique(['user_id', 'cycle_key', 'scope_key'], 'viewing_progress_owner_cycle_scope_unique');
            $table->index(['user_id', 'status', 'last_watched_at'], 'viewing_progress_continue_index');
            $table->index(['user_id', 'work_id', 'status'], 'viewing_progress_spoiler_index');
        });

        Schema::create('viewing_progress_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('viewing_progress_id')->constrained('viewing_progress')->cascadeOnDelete();
            $table->foreignId('user_viewing_journey_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('rewatch_cycle_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('event_type', 32);
            $table->string('previous_status', 24)->nullable();
            $table->string('new_status', 24);
            $table->unsignedInteger('previous_position_seconds')->nullable();
            $table->unsignedInteger('new_position_seconds')->nullable();
            $table->string('client_request_id', 100)->nullable();
            $table->string('source', 24);
            $table->json('safe_metadata')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->unique(['user_id', 'client_request_id']);
            $table->index(['viewing_progress_id', 'occurred_at', 'id'], 'viewing_progress_events_history_index');
        });

        Schema::create('watchlists', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('universe_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('visibility', 24)->default('private');
            $table->boolean('is_default')->default(false);
            $table->string('default_key', 16)->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'slug']);
            $table->unique(['user_id', 'default_key'], 'watchlists_one_default_per_user');
            $table->index(['user_id', 'position', 'id']);
        });

        Schema::create('watchlist_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('watchlist_id')->constrained()->cascadeOnDelete();
            $table->string('target_type', 64);
            $table->unsignedBigInteger('target_id');
            $table->unsignedInteger('position');
            $table->timestamp('added_at');
            $table->string('private_note', 1000)->nullable();
            $table->timestamps();

            $table->unique(['watchlist_id', 'target_type', 'target_id'], 'watchlist_items_target_unique');
            $table->unique(['watchlist_id', 'position']);
            $table->index(['target_type', 'target_id']);
        });

        Schema::create('favourites', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('universe_id')->constrained()->restrictOnDelete();
            $table->string('target_type', 64);
            $table->unsignedBigInteger('target_id');
            $table->timestamps();

            $table->unique(['user_id', 'target_type', 'target_id']);
            $table->index(['user_id', 'universe_id', 'created_at']);
        });

        Schema::create('ratings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('universe_id')->constrained()->restrictOnDelete();
            $table->string('target_type', 64);
            $table->unsignedBigInteger('target_id');
            $table->unsignedTinyInteger('rating');
            $table->timestamps();

            $table->unique(['user_id', 'target_type', 'target_id']);
            $table->index(['user_id', 'universe_id', 'updated_at']);
        });

        Schema::create('personal_notes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('universe_id')->constrained()->restrictOnDelete();
            $table->string('target_type', 64);
            $table->unsignedBigInteger('target_id');
            $table->string('title')->nullable();
            $table->text('body');
            $table->string('format', 24)->default('plain_text');
            $table->string('visibility', 24)->default('private');
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_spoiler_sensitive')->default(false);
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'updated_at', 'id']);
            $table->index(['target_type', 'target_id']);
        });

        Schema::create('user_fandom_preferences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('universe_id')->constrained()->restrictOnDelete();
            $table->foreignId('preferred_viewing_order_id')->nullable()->constrained('viewing_orders')->restrictOnDelete();
            $table->string('default_locale', 35)->default('en');
            $table->boolean('auto_complete_progress')->default(false);
            $table->boolean('auto_remove_completed_watchlist_items')->default(false);
            $table->string('continue_watching_visibility', 24)->default('private');
            $table->string('rating_visibility', 24)->default('private');
            $table->string('favourite_visibility', 24)->default('private');
            $table->string('journey_visibility', 24)->default('private');
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'universe_id']);
        });

        Schema::table('user_spoiler_preferences', function (Blueprint $table): void {
            $table->string('rewatch_behavior', 24)->default('historical')->after('show_warnings');
            $table->unsignedInteger('lock_version')->default(0)->after('rewatch_behavior');
        });
    }

    public function down(): void
    {
        $duplicateProgress = DB::table('viewing_progress')
            ->select(['user_id', 'work_id'])
            ->groupBy(['user_id', 'work_id'])
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($duplicateProgress) {
            throw new RuntimeException('Prompt 8 rollback requires progress consolidation because the legacy schema permits only one row per user and work.');
        }

        Schema::table('user_spoiler_preferences', function (Blueprint $table): void {
            $table->dropColumn(['rewatch_behavior', 'lock_version']);
        });

        Schema::dropIfExists('user_fandom_preferences');
        Schema::dropIfExists('personal_notes');
        Schema::dropIfExists('ratings');
        Schema::dropIfExists('favourites');
        Schema::dropIfExists('watchlist_items');
        Schema::dropIfExists('watchlists');
        Schema::dropIfExists('viewing_progress_events');

        Schema::table('viewing_progress', function (Blueprint $table): void {
            $table->dropUnique('viewing_progress_owner_cycle_scope_unique');
            $table->dropIndex('viewing_progress_continue_index');
            $table->dropIndex('viewing_progress_spoiler_index');
            $table->dropConstrainedForeignId('user_viewing_journey_id');
            $table->dropConstrainedForeignId('rewatch_cycle_id');
            $table->dropColumn([
                'scope_type',
                'scope_key',
                'cycle_key',
                'status',
                'progress_basis_points',
                'runtime_position_seconds',
                'started_at',
                'last_watched_at',
                'completed_at',
                'source',
                'is_manual_override',
                'is_legacy_projection',
                'last_request_id',
                'lock_version',
            ]);
            $table->unique(['user_id', 'work_id']);
        });

        Schema::dropIfExists('viewing_sessions');
        Schema::dropIfExists('user_viewing_journeys');
        Schema::dropIfExists('rewatch_cycles');
        Schema::dropIfExists('viewing_order_items');
        Schema::dropIfExists('viewing_orders');
    }
};
