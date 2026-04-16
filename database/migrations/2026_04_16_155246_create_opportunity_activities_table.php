<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opportunity_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opportunity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('type');
            $table->text('details');
            $table->date('occurred_at')->nullable();
            $table->timestamps();

            $table->index(['opportunity_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunity_activities');
    }
};
