<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bunker_categories', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique();
            $table->string('name', 100);
            $table->string('description', 500)->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['is_active', 'position', 'id']);
        });

        Schema::create('bunkers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('universe_id')->constrained()->restrictOnDelete();
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name', 120);
            $table->string('slug', 140);
            $table->text('description')->nullable();
            $table->string('rules_summary', 1000)->nullable();
            $table->string('visibility', 32);
            $table->string('status', 32)->default('draft');
            $table->boolean('requires_approval')->default(false);
            $table->boolean('requires_invitation')->default(false);
            $table->string('default_locale', 12)->default('en');
            $table->string('spoiler_severity', 32)->nullable();
            $table->unsignedBigInteger('owner_membership_key')->nullable();
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamp('restricted_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['universe_id', 'slug']);
            $table->unique('owner_membership_key');
            $table->index(['visibility', 'status', 'published_at', 'id']);
        });

        Schema::create('bunker_category', function (Blueprint $table) {
            $table->foreignId('bunker_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bunker_category_id')->constrained()->restrictOnDelete();
            $table->timestamps();
            $table->primary(['bunker_id', 'bunker_category_id']);
        });

        Schema::create('bunker_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bunker_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('role', 32);
            $table->string('status', 32)->default('active');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('active_key', 191)->nullable()->unique();
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('left_at')->nullable();
            $table->timestamp('removed_at')->nullable();
            $table->timestamps();
            $table->index(['bunker_id', 'status', 'role']);
            $table->index(['user_id', 'status', 'joined_at']);
        });

        Schema::create('bunker_join_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bunker_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 32)->default('pending');
            $table->string('active_key', 191)->nullable()->unique();
            $table->string('message', 500)->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('decision_explanation', 500)->nullable();
            $table->timestamp('submitted_at');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['bunker_id', 'status', 'submitted_at']);
        });

        Schema::create('bunker_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bunker_id')->constrained()->restrictOnDelete();
            $table->foreignId('invited_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('inviter_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('proposed_role', 32)->default('member');
            $table->char('token_hash', 64)->unique();
            $table->string('status', 32)->default('pending');
            $table->string('active_key', 191)->nullable()->unique();
            $table->timestamp('sent_at');
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
            $table->index(['bunker_id', 'status', 'expires_at']);
        });

        Schema::create('bunker_bans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bunker_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('lifted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason_code', 64);
            $table->string('user_visible_explanation', 500);
            $table->text('private_note')->nullable();
            $table->string('status', 32)->default('active');
            $table->string('active_key', 191)->nullable()->unique();
            $table->timestamp('effective_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('lifted_at')->nullable();
            $table->timestamps();
            $table->index(['bunker_id', 'user_id', 'status', 'expires_at']);
        });

        Schema::create('bunker_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bunker_id')->constrained()->restrictOnDelete();
            $table->string('title', 120);
            $table->string('description', 1000);
            $table->string('category', 32);
            $table->unsignedSmallInteger('position');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();
            $table->unique(['bunker_id', 'position']);
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('bunker_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('universe_id')->constrained()->restrictOnDelete();
            $table->nullableMorphs('reference');
            $table->string('title', 200)->nullable();
            $table->text('body');
            $table->char('body_checksum', 64);
            $table->string('status', 32)->default('draft');
            $table->string('visibility', 32)->default('public');
            $table->boolean('comments_enabled')->default(true);
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamp('locked_at')->nullable();
            $table->timestamp('edited_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('removed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['visibility', 'status', 'published_at', 'id']);
            $table->index(['bunker_id', 'status', 'published_at', 'id']);
            $table->index(['universe_id', 'status', 'published_at', 'id']);
        });

        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->restrictOnDelete();
            $table->foreignId('author_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('comments')->restrictOnDelete();
            $table->foreignId('root_id')->nullable()->constrained('comments')->restrictOnDelete();
            $table->unsignedTinyInteger('depth')->default(0);
            $table->text('body');
            $table->char('body_checksum', 64);
            $table->string('status', 32)->default('published');
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamp('edited_at')->nullable();
            $table->timestamp('removed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['post_id', 'parent_id', 'created_at', 'id']);
            $table->index(['post_id', 'root_id', 'created_at', 'id']);
        });

        Schema::create('reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('reactable');
            $table->string('type', 32);
            $table->timestamps();
            $table->unique(['user_id', 'reactable_type', 'reactable_id', 'type'], 'reactions_actor_target_unique');
        });

        Schema::create('bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('bookmarkable');
            $table->timestamps();
            $table->unique(['user_id', 'bookmarkable_type', 'bookmarkable_id'], 'bookmarks_owner_target_unique');
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('universe_id')->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('normalized_name', 80);
            $table->string('display_name', 80);
            $table->string('slug', 90);
            $table->string('status', 32)->default('active');
            $table->timestamps();
            $table->unique(['universe_id', 'normalized_name']);
            $table->unique(['universe_id', 'slug']);
        });

        Schema::create('taggables', function (Blueprint $table) {
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->morphs('taggable');
            $table->timestamps();
            $table->unique(['tag_id', 'taggable_type', 'taggable_id'], 'taggables_target_unique');
        });

        Schema::create('mentions', function (Blueprint $table) {
            $table->id();
            $table->morphs('mentionable');
            $table->foreignId('mentioned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('mentioning_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 32);
            $table->string('notification_key', 191)->nullable()->unique();
            $table->timestamp('inactive_at')->nullable();
            $table->timestamps();
            $table->unique(['mentionable_type', 'mentionable_id', 'mentioned_user_id'], 'mentions_source_user_unique');
        });

        Schema::create('polls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->unique()->constrained()->restrictOnDelete();
            $table->string('question', 240);
            $table->string('type', 32)->default('single');
            $table->unsignedTinyInteger('maximum_choices')->default(1);
            $table->string('status', 32)->default('open');
            $table->string('results_visibility', 32)->default('after_vote');
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamp('opens_at')->nullable();
            $table->timestamp('closes_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'closes_at']);
        });

        Schema::create('poll_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poll_id')->constrained()->restrictOnDelete();
            $table->string('text', 160);
            $table->unsignedTinyInteger('position');
            $table->timestamps();
            $table->unique(['poll_id', 'position']);
        });

        Schema::create('poll_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poll_id')->constrained()->restrictOnDelete();
            $table->foreignId('poll_option_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->unique(['poll_id', 'poll_option_id', 'user_id']);
            $table->index(['poll_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poll_votes');
        Schema::dropIfExists('poll_options');
        Schema::dropIfExists('polls');
        Schema::dropIfExists('mentions');
        Schema::dropIfExists('taggables');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('bookmarks');
        Schema::dropIfExists('reactions');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('bunker_rules');
        Schema::dropIfExists('bunker_bans');
        Schema::dropIfExists('bunker_invitations');
        Schema::dropIfExists('bunker_join_requests');
        Schema::dropIfExists('bunker_memberships');
        Schema::dropIfExists('bunker_category');
        Schema::dropIfExists('bunkers');
        Schema::dropIfExists('bunker_categories');
    }
};
