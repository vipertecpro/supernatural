<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_categories', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique();
            $table->string('name');
            $table->string('description', 1000);
            $table->json('applicable_target_types');
            $table->string('default_priority', 24);
            $table->boolean('evidence_required')->default(false);
            $table->boolean('explanation_required')->default(true);
            $table->boolean('rights_review_required')->default(false);
            $table->boolean('safety_review_required')->default(false);
            $table->boolean('appeals_supported')->default(true);
            $table->boolean('is_active')->default(true);
            $table->json('safe_metadata')->nullable();
            $table->timestamps();
            $table->index(['is_active', 'key']);
        });

        Schema::create('moderation_cases', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->nullableMorphs('target');
            $table->foreignId('subject_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 32);
            $table->string('priority', 24);
            $table->foreignId('opened_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('opened_at');
            $table->timestamp('triaged_at')->nullable();
            $table->timestamp('investigation_started_at')->nullable();
            $table->timestamp('decision_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->string('resolution_code', 64)->nullable();
            $table->string('user_visible_summary', 2000)->nullable();
            $table->text('private_internal_summary')->nullable();
            $table->json('safe_metadata')->nullable();
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();
            $table->index(['status', 'priority', 'opened_at', 'id'], 'moderation_cases_queue_index');
            $table->index(['subject_user_id', 'status', 'id']);
        });

        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('report_category_id')->constrained()->restrictOnDelete();
            $table->morphs('target');
            $table->foreignId('moderation_case_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('duplicate_of_report_id')->nullable()->constrained('reports')->nullOnDelete();
            $table->string('status', 24);
            $table->string('priority', 24);
            $table->string('reason_code', 64)->nullable();
            $table->text('explanation')->nullable();
            $table->string('request_id', 100)->nullable();
            $table->json('safe_metadata')->nullable();
            $table->timestamp('submitted_at');
            $table->timestamp('withdrawn_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->index(['reporter_user_id', 'submitted_at', 'id']);
            $table->index(['status', 'priority', 'submitted_at', 'id'], 'reports_queue_index');
            $table->index(['target_type', 'target_id', 'status'], 'reports_target_status_index');
        });

        Schema::create('report_evidence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->restrictOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 32);
            $table->string('visibility', 32);
            $table->text('description')->nullable();
            $table->foreignId('media_asset_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('source_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('citation_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('external_url', 2048)->nullable();
            $table->char('checksum', 64)->nullable();
            $table->json('safe_metadata')->nullable();
            $table->timestamps();
            $table->index(['report_id', 'visibility', 'id']);
        });

        Schema::create('moderation_case_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('moderation_case_id')->constrained()->restrictOnDelete();
            $table->foreignId('moderator_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('role', 32)->default('primary');
            $table->string('status', 24);
            $table->string('active_primary_key', 16)->nullable();
            $table->timestamp('assigned_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->string('private_note', 1000)->nullable();
            $table->timestamps();
            $table->unique(['moderation_case_id', 'active_primary_key'], 'moderation_one_active_primary');
            $table->index(['moderator_user_id', 'status', 'due_at', 'id'], 'moderation_assignee_queue_index');
        });

        Schema::create('moderation_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('moderation_case_id')->constrained()->restrictOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 48);
            $table->foreignId('target_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->nullableMorphs('target_content');
            $table->string('reason_code', 64);
            $table->string('user_visible_explanation', 2000);
            $table->text('private_moderator_note')->nullable();
            $table->timestamp('effective_at');
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('supersedes_action_id')->nullable()->constrained('moderation_actions')->nullOnDelete();
            $table->json('safe_metadata')->nullable();
            $table->timestamps();
            $table->index(['moderation_case_id', 'effective_at', 'id']);
            $table->index(['target_user_id', 'type', 'effective_at']);
        });

        Schema::create('user_restrictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('moderation_action_id')->unique()->constrained()->restrictOnDelete();
            $table->string('type', 32);
            $table->string('status', 24);
            $table->timestamp('effective_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('lifted_at')->nullable();
            $table->foreignId('lifted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_visible_reason', 2000);
            $table->timestamps();
            $table->index(['user_id', 'status', 'effective_at', 'expires_at'], 'user_restrictions_active_index');
        });

        Schema::create('user_restriction_scopes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_restriction_id')->constrained()->cascadeOnDelete();
            $table->string('scope', 48);
            $table->timestamps();
            $table->unique(['user_restriction_id', 'scope']);
            $table->index('scope');
        });

        Schema::create('content_restrictions', function (Blueprint $table) {
            $table->id();
            $table->morphs('target');
            $table->foreignId('moderation_action_id')->unique()->constrained()->restrictOnDelete();
            $table->string('type', 32);
            $table->string('status', 24);
            $table->timestamp('effective_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('lifted_at')->nullable();
            $table->foreignId('lifted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason_code', 64);
            $table->string('public_explanation', 2000);
            $table->timestamps();
            $table->index(['target_type', 'target_id', 'status', 'effective_at', 'expires_at'], 'content_restrictions_active_index');
        });

        Schema::create('appeals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appellant_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('moderation_case_id')->constrained()->restrictOnDelete();
            $table->foreignId('moderation_action_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_restriction_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('content_restriction_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('status', 24);
            $table->string('active_key', 16)->nullable();
            $table->text('explanation');
            $table->timestamp('submitted_at');
            $table->timestamp('review_started_at')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamp('withdrawn_at')->nullable();
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();
            $table->unique(['appellant_user_id', 'moderation_action_id', 'active_key'], 'appeals_one_active_per_action');
            $table->index(['status', 'submitted_at', 'id']);
        });

        Schema::create('appeal_decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appeal_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('reviewer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 24);
            $table->string('user_visible_explanation', 2000);
            $table->text('private_reviewer_note')->nullable();
            $table->foreignId('replacement_action_id')->nullable()->constrained('moderation_actions')->nullOnDelete();
            $table->timestamp('decided_at');
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 96);
            $table->unsignedSmallInteger('schema_version');
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->nullableMorphs('subject');
            $table->string('correlation_key', 160)->nullable();
            $table->string('idempotency_key', 160);
            $table->string('priority', 24);
            $table->string('status', 24);
            $table->json('payload');
            $table->timestamp('read_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'idempotency_key']);
            $table->index(['user_id', 'archived_at', 'read_at', 'created_at', 'id'], 'notifications_inbox_index');
            $table->index(['type', 'created_at', 'id']);
            $table->index('correlation_key');
        });

        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 96);
            $table->string('channel', 24);
            $table->string('state', 24);
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();
            $table->unique(['user_id', 'type', 'channel']);
        });

        Schema::create('notification_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_id')->constrained()->cascadeOnDelete();
            $table->string('channel', 24);
            $table->string('status', 24);
            $table->unsignedSmallInteger('attempt_number');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('attempted_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('suppressed_at')->nullable();
            $table->string('provider_response_code', 64)->nullable();
            $table->string('failure_code', 64)->nullable();
            $table->timestamp('retry_at')->nullable();
            $table->timestamps();
            $table->unique(['notification_id', 'channel', 'attempt_number'], 'notification_delivery_attempt_unique');
            $table->index(['status', 'retry_at', 'id'], 'notification_delivery_retry_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_deliveries');
        Schema::dropIfExists('notification_preferences');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('appeal_decisions');
        Schema::dropIfExists('appeals');
        Schema::dropIfExists('content_restrictions');
        Schema::dropIfExists('user_restriction_scopes');
        Schema::dropIfExists('user_restrictions');
        Schema::dropIfExists('moderation_actions');
        Schema::dropIfExists('moderation_case_assignments');
        Schema::dropIfExists('report_evidence');
        Schema::dropIfExists('reports');
        Schema::dropIfExists('moderation_cases');
        Schema::dropIfExists('report_categories');
    }
};
