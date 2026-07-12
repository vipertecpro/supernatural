<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_onboarding_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('current_step', 40)->default('introduction');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();

            $table->index(['current_step', 'completed_at'], 'user_onboarding_states_completion_index');
        });

        DB::table('users')
            ->select(['id', 'created_at', 'updated_at'])
            ->orderBy('id')
            ->chunkById(500, function ($users): void {
                $now = now();
                $rows = $users->map(fn (object $user): array => [
                    'user_id' => $user->id,
                    'current_step' => 'completed',
                    'started_at' => $user->created_at ?? $now,
                    'last_activity_at' => $user->updated_at ?? $now,
                    'completed_at' => $now,
                    'lock_version' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all();

                if ($rows !== []) {
                    DB::table('user_onboarding_states')->insert($rows);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_onboarding_states');
    }
};
