<?php

use App\Enums\PublicationStatus;
use App\Enums\SourceType;
use App\Enums\SpoilerSeverity;
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
        Schema::create('content_licenses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('reference_url', 2048)->nullable();
            $table->boolean('attribution_required')->nullable();
            $table->boolean('commercial_use_allowed')->nullable();
            $table->boolean('derivative_use_allowed')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('universes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('status', array_map(fn (PublicationStatus $status) => $status->value, PublicationStatus::cases()))->index();
            $table->boolean('is_public')->default(false)->index();
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('universe_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('content_license_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('canonical_url', 2048);
            $table->enum('source_type', array_map(fn (SourceType $type) => $type->value, SourceType::cases()))->index();
            $table->string('publisher')->nullable();
            $table->string('author')->nullable();
            $table->date('published_at')->nullable();
            $table->date('accessed_at')->nullable();
            $table->text('attribution_text')->nullable();
            $table->text('usage_notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['universe_id', 'source_type']);
        });

        Schema::create('spoiler_constraints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('universe_id')->constrained()->cascadeOnDelete();
            $table->morphs('spoilerable');
            $table->enum('severity', array_map(fn (SpoilerSeverity $severity) => $severity->value, SpoilerSeverity::cases()))->index();
            $table->json('earliest_progress')->nullable();
            $table->string('warning', 500)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['universe_id', 'severity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spoiler_constraints');
        Schema::dropIfExists('sources');
        Schema::dropIfExists('universes');
        Schema::dropIfExists('content_licenses');
    }
};
