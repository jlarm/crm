<?php

declare(strict_types=1);

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
        Schema::create('email_tracking_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sent_email_id')->constrained()->onDelete('cascade');
            $table->string('event_type'); // 'delivered', 'opened', 'clicked', 'bounced', 'complained', 'unsubscribed'
            $table->string('message_id'); // Mailgun message ID
            $table->string('recipient_email');
            $table->string('url')->nullable(); // For click events
            $table->string('user_agent')->nullable();
            $table->string('ip_address')->nullable();
            $table->json('mailgun_data')->nullable(); // Store full Mailgun webhook data
            $table->timestamp('event_timestamp');
            $table->timestamps();

            $table->index(['sent_email_id', 'event_type']);
            $table->index(['message_id']);
            $table->index(['recipient_email']);
            $table->index(['event_timestamp']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_tracking_events');
    }
};
