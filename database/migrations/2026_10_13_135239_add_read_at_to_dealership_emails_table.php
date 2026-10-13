<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dealership_emails', function (Blueprint $table) {
            // When someone opened a contact's reply in the CRM; null means it's still new.
            $table->timestamp('read_at')->nullable()->after('failed_at');
        });
    }

    public function down(): void
    {
        Schema::table('dealership_emails', function (Blueprint $table) {
            $table->dropColumn('read_at');
        });
    }
};
