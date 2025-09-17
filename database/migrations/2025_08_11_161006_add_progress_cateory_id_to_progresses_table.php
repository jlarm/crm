<?php

declare(strict_types=1);

use App\Models\ProgressCategory;
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
        Schema::table('progresses', function (Blueprint $table): void {
            $table->foreignIdFor(ProgressCategory::class)->nullable()->constrained();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('progresses', function (Blueprint $table): void {
            //
        });
    }
};
