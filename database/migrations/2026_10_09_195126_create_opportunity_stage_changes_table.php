<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opportunity_stage_changes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('opportunity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('from_stage')->nullable();
            $table->string('to_stage');
            $table->timestamp('changed_at');
            $table->timestamps();

            $table->index(['opportunity_id', 'changed_at']);
        });

        DB::table('opportunities')
            ->select(['id', 'stage', 'stage_entered_at', 'created_at'])
            ->orderBy('id')
            ->each(function (object $opportunity): void {
                $changedAt = $opportunity->stage_entered_at ?? $opportunity->created_at ?? now();

                DB::table('opportunity_stage_changes')->insert([
                    'opportunity_id' => $opportunity->id,
                    'user_id' => null,
                    'from_stage' => null,
                    'to_stage' => $opportunity->stage,
                    'changed_at' => $changedAt,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunity_stage_changes');
    }
};
