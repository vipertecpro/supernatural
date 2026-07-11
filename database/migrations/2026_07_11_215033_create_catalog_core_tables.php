<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('franchises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('universe_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('status', 32)->default('draft');
            $table->boolean('is_public')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['universe_id', 'slug']);
            $table->index(['universe_id', 'status', 'published_at']);
            $table->index(['universe_id', 'position']);
            $table->index(['status', 'is_public', 'archived_at']);
        });

        Schema::create('works', function (Blueprint $table) {
            $table->id();
            $table->foreignId('universe_id')->constrained()->restrictOnDelete();
            $table->foreignId('franchise_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('type', 32);
            $table->string('slug');
            $table->string('original_title');
            $table->string('original_language', 35);
            $table->text('summary')->nullable();
            $table->unsignedSmallInteger('runtime_minutes')->nullable();
            $table->string('release_status', 32)->default('unknown');
            $table->string('canon_status', 32)->default('unknown');
            $table->date('original_release_date')->nullable();
            $table->string('release_date_precision', 16)->nullable();
            $table->string('status', 32)->default('draft');
            $table->boolean('is_public')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->unsignedInteger('lock_version')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['universe_id', 'slug']);
            $table->index(['universe_id', 'type', 'status', 'published_at']);
            $table->index(['franchise_id', 'status', 'published_at']);
            $table->index(['status', 'is_public', 'archived_at']);
        });

        Schema::create('work_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_id')->constrained()->restrictOnDelete();
            $table->string('locale', 35);
            $table->string('title');
            $table->string('short_title')->nullable();
            $table->text('summary')->nullable();
            $table->longText('synopsis')->nullable();
            $table->string('translated_from_locale', 35)->nullable();
            $table->string('status', 32)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['work_id', 'locale']);
            $table->index(['locale', 'status', 'published_at']);
        });

        Schema::create('series_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_id')->unique()->constrained()->restrictOnDelete();
            $table->string('format', 32);
            $table->string('series_status', 32)->default('unknown');
            $table->date('premiere_date')->nullable();
            $table->date('end_date')->nullable();
            $table->unsignedSmallInteger('default_episode_duration')->nullable();
            $table->string('default_episode_order', 32)->default('aired');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['series_status', 'premiere_date']);
        });

        Schema::create('seasons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_id')->constrained()->restrictOnDelete();
            $table->string('type', 32)->default('season');
            $table->unsignedInteger('number')->nullable();
            $table->string('display_number')->nullable();
            $table->string('title');
            $table->string('slug');
            $table->text('summary')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->date('original_release_date')->nullable();
            $table->string('release_date_precision', 16)->nullable();
            $table->string('status', 32)->default('draft');
            $table->boolean('is_public')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['work_id', 'slug']);
            $table->unique(['work_id', 'number']);
            $table->index(['work_id', 'position', 'id']);
            $table->index(['work_id', 'status', 'published_at']);
            $table->index(['status', 'is_public', 'archived_at']);
        });

        Schema::create('episodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_id')->constrained()->restrictOnDelete();
            $table->foreignId('season_id')->nullable()->constrained()->restrictOnDelete();
            $table->unsignedInteger('episode_number')->nullable();
            $table->string('display_number')->nullable();
            $table->unsignedInteger('absolute_number')->nullable();
            $table->string('production_code')->nullable();
            $table->string('type', 32)->default('standard');
            $table->string('title');
            $table->string('slug');
            $table->text('summary')->nullable();
            $table->longText('synopsis')->nullable();
            $table->unsignedSmallInteger('runtime_minutes')->nullable();
            $table->date('original_release_date')->nullable();
            $table->string('release_date_precision', 16)->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->string('status', 32)->default('draft');
            $table->boolean('is_public')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['work_id', 'slug']);
            $table->unique(['season_id', 'episode_number']);
            $table->unique(['work_id', 'absolute_number']);
            $table->unique(['work_id', 'production_code']);
            $table->index(['season_id', 'position', 'id']);
            $table->index(['work_id', 'position', 'id']);
            $table->index(['work_id', 'status', 'published_at']);
            $table->index(['status', 'is_public', 'archived_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('episodes');
        Schema::dropIfExists('seasons');
        Schema::dropIfExists('series_details');
        Schema::dropIfExists('work_translations');
        Schema::dropIfExists('works');
        Schema::dropIfExists('franchises');
    }
};
