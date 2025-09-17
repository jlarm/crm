<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dealer_emails', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('dealership_id');
            $table->json('recipients');
            $table->string('attachment')->nullable();
            $table->string('subject');
            $table->text('message');
            $table->date('start_date');
            $table->date('last_sent')->nullable();
            $table->integer('frequency')->nullable();
            $table->boolean('paused')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dealer_emails');
    }
};
