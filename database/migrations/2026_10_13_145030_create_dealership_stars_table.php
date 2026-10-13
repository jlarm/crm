<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Dealerships a user starred to keep on their dashboard. Personal, unlike dealership_user assignments.
        Schema::create('dealership_stars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dealership_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'dealership_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dealership_stars');
    }
};
