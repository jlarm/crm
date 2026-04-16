<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('opportunities')) {
            return;
        }

        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dealership_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('stage');
            $table->timestamp('stage_entered_at')->nullable();
            $table->unsignedTinyInteger('probability')->nullable();
            $table->decimal('estimated_value', 12, 2)->nullable();
            $table->decimal('actual_value', 12, 2)->nullable();
            $table->date('expected_close_date')->nullable();
            $table->string('next_action')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->text('lost_reason')->nullable();
            $table->string('lost_reason_code')->nullable();
            $table->date('contract_sent_date')->nullable();
            $table->date('contract_signed_date')->nullable();
            $table->date('contract_renewal_date')->nullable();
            $table->date('closed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};
