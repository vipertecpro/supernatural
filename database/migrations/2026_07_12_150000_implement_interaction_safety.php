<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_blocks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('blocker_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('blocked_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('reason_code', 64)->nullable();
            $table->timestamps();
            $table->unique(['blocker_user_id', 'blocked_user_id'], 'user_blocks_pair_unique');
            $table->index(['blocked_user_id', 'blocker_user_id'], 'user_blocks_reverse_lookup');
        });

        Schema::create('user_mutes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('muting_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('muted_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('scope', 32);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->unique(['muting_user_id', 'muted_user_id', 'scope'], 'user_mutes_scope_unique');
            $table->index(['muted_user_id', 'muting_user_id', 'scope'], 'user_mutes_reverse_lookup');
            $table->index(['muting_user_id', 'expires_at'], 'user_mutes_expiration_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_mutes');
        Schema::dropIfExists('user_blocks');
    }
};
