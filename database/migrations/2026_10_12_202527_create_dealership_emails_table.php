<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dealership_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dealership_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('dealership_emails')->cascadeOnDelete();
            $table->string('direction', 16);
            $table->string('from_email');
            $table->string('from_name')->nullable();
            $table->string('to_email');
            $table->string('subject');
            $table->longText('body_text');
            $table->longText('body_html')->nullable();
            $table->string('message_id')->unique();
            $table->string('in_reply_to')->nullable();
            $table->string('reply_token', 64)->nullable()->unique();
            $table->string('status', 16)->index();
            $table->string('status_reason', 32)->nullable();
            $table->text('status_detail')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            $table->index(['dealership_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dealership_emails');
    }
};
