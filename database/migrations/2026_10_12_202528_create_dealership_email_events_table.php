<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dealership_email_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dealership_email_id')->constrained()->cascadeOnDelete();
            $table->string('mailgun_event_id')->nullable()->unique();
            $table->string('event', 32);
            $table->string('severity', 16)->nullable();
            $table->string('reason', 32)->nullable();
            $table->unsignedSmallInteger('code')->nullable();
            $table->text('description')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dealership_email_events');
    }
};
