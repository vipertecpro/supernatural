<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('universe_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('source_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('content_license_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('kind', 24);
            $table->string('origin', 24);
            $table->string('disk', 64);
            $table->string('storage_key', 1024)->unique();
            $table->string('original_filename');
            $table->string('display_filename');
            $table->string('mime_type', 128);
            $table->string('extension', 16);
            $table->unsignedBigInteger('size_bytes');
            $table->char('checksum', 64);
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->string('alt_text', 1000)->nullable();
            $table->text('caption')->nullable();
            $table->text('attribution_text')->nullable();
            $table->string('copyright_owner')->nullable();
            $table->string('status', 24)->default('pending');
            $table->string('moderation_status', 24)->default('pending');
            $table->string('processing_status', 24)->default('pending');
            $table->string('visibility', 24)->default('private');
            $table->json('metadata')->nullable();
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamp('takedown_at')->nullable();
            $table->unsignedInteger('lock_version')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['checksum', 'size_bytes']);
            $table->index(['owner_user_id', 'status', 'id']);
            $table->index(['universe_id', 'status', 'visibility', 'published_at', 'id'], 'media_assets_public_index');
            $table->index(['moderation_status', 'status', 'id']);
        });

        Schema::create('media_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_asset_id')->constrained()->cascadeOnDelete();
            $table->string('purpose', 24);
            $table->string('format', 16);
            $table->string('disk', 64);
            $table->string('storage_key', 1024)->unique();
            $table->string('mime_type', 128);
            $table->unsignedBigInteger('size_bytes');
            $table->char('checksum', 64);
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('processing_status', 24)->default('pending');
            $table->timestamp('ready_at')->nullable();
            $table->timestamps();

            $table->unique(['media_asset_id', 'purpose', 'format']);
            $table->index(['media_asset_id', 'processing_status']);
        });

        Schema::create('external_embeds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('universe_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('source_id')->constrained()->restrictOnDelete();
            $table->foreignId('content_license_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('provider', 24);
            $table->string('provider_content_id', 255);
            $table->string('canonical_url', 2048);
            $table->string('embed_url', 2048);
            $table->string('kind', 24);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('thumbnail_url', 2048)->nullable();
            $table->string('creator')->nullable();
            $table->string('publisher')->nullable();
            $table->text('attribution_text')->nullable();
            $table->string('status', 24)->default('pending');
            $table->string('moderation_status', 24)->default('pending');
            $table->string('visibility', 24)->default('private');
            $table->json('provider_metadata')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamp('takedown_at')->nullable();
            $table->unsignedInteger('lock_version')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['provider', 'provider_content_id']);
            $table->index(['universe_id', 'status', 'visibility', 'published_at', 'id'], 'external_embeds_public_index');
            $table->index(['moderation_status', 'status', 'id']);
        });

        Schema::create('media_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_asset_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('external_embed_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('attachable_type', 64);
            $table->unsignedBigInteger('attachable_id');
            $table->string('role', 24);
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->string('primary_key', 16)->nullable();
            $table->string('locale', 35)->nullable();
            $table->text('caption_override')->nullable();
            $table->string('alt_text_override', 1000)->nullable();
            $table->string('status', 24)->default('draft');
            $table->foreignId('spoiler_constraint_id')->nullable()->constrained()->restrictOnDelete();
            $table->unsignedInteger('lock_version')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['attachable_type', 'attachable_id', 'role', 'primary_key'], 'media_attachments_one_primary');
            $table->index(['attachable_type', 'attachable_id', 'status', 'role', 'position', 'id'], 'media_attachments_target_index');
            $table->index(['media_asset_id', 'status']);
            $table->index(['external_embed_id', 'status']);
        });

        Schema::create('media_processing_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_asset_id')->constrained()->cascadeOnDelete();
            $table->string('operation', 64);
            $table->string('status', 24)->default('pending');
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->string('error_code', 64)->nullable();
            $table->json('safe_metadata')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at', 'id']);
            $table->index(['media_asset_id', 'operation', 'status']);
        });

        Schema::create('search_documents', function (Blueprint $table) {
            $table->id();
            $table->string('source_type', 64);
            $table->unsignedBigInteger('source_id');
            $table->foreignId('universe_id')->constrained()->restrictOnDelete();
            $table->string('locale', 35);
            $table->string('document_type', 32);
            $table->string('canonical_title');
            $table->string('localized_title')->nullable();
            $table->text('searchable_summary')->nullable();
            $table->text('normalized_text');
            $table->string('slug');
            $table->string('route_key', 128);
            $table->string('status', 24)->default('active');
            $table->string('visibility', 24)->default('public');
            $table->string('canon_classification', 32)->nullable();
            $table->string('spoiler_severity', 24)->nullable();
            $table->json('spoiler_boundary')->nullable();
            $table->integer('ranking_weight')->default(0);
            $table->unsignedBigInteger('popularity_score')->default(0);
            $table->unsignedInteger('projection_version')->default(1);
            $table->unsignedInteger('source_lock_version')->default(0);
            $table->json('facets')->nullable();
            $table->json('safe_metadata')->nullable();
            $table->timestamp('freshness_at')->nullable();
            $table->timestamp('indexed_at');
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->unique(['source_type', 'source_id', 'locale']);
            $table->index(['universe_id', 'document_type', 'locale', 'status', 'visibility', 'id'], 'search_documents_filter_index');
            $table->index(['locale', 'canonical_title', 'id']);
            $table->index(['freshness_at', 'id']);
        });

        Schema::create('search_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('search_document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('universe_id')->constrained()->restrictOnDelete();
            $table->string('locale', 35);
            $table->string('suggestion_type', 24);
            $table->string('value');
            $table->string('normalized_value');
            $table->unsignedSmallInteger('weight')->default(0);
            $table->boolean('spoiler_sensitive')->default(false);
            $table->timestamps();

            $table->unique(['search_document_id', 'suggestion_type', 'normalized_value'], 'search_suggestions_unique');
            $table->index(['universe_id', 'locale', 'normalized_value', 'weight', 'id'], 'search_suggestions_lookup');
        });

        Schema::create('trending_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('universe_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('subject_type', 64);
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->char('query_hash', 64)->nullable();
            $table->unsignedBigInteger('score');
            $table->unsignedInteger('sample_size');
            $table->timestamp('window_started_at');
            $table->timestamp('window_ended_at');
            $table->timestamps();

            $table->index(['window_ended_at', 'score', 'id']);
            $table->index(['universe_id', 'subject_type', 'window_ended_at'], 'trending_universe_type_window_index');
        });

        Schema::create('search_queries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('universe_id')->nullable()->constrained()->restrictOnDelete();
            $table->char('query_hash', 64);
            $table->unsignedSmallInteger('query_length');
            $table->string('locale', 35);
            $table->string('document_type', 32)->nullable();
            $table->unsignedSmallInteger('result_count_bucket')->default(0);
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['occurred_at', 'query_hash']);
            $table->index(['universe_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_queries');
        Schema::dropIfExists('trending_snapshots');
        Schema::dropIfExists('search_suggestions');
        Schema::dropIfExists('search_documents');
        Schema::dropIfExists('media_processing_jobs');
        Schema::dropIfExists('media_attachments');
        Schema::dropIfExists('external_embeds');
        Schema::dropIfExists('media_variants');
        Schema::dropIfExists('media_assets');
    }
};
