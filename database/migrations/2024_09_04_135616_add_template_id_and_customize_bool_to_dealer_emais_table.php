<?php

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
        Schema::table('dealer_emails', function (Blueprint $table) {
            $table->foreignId('dealer_email_template_id')->nullable()->after('dealership_id');
            $table->boolean('customize_email')->default(false)->after('dealer_email_template_id');
            $table->boolean('customize_attachment')->default(false)->after('customize_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dealer_emails', function (Blueprint $table) {
            $table->dropColumn('dealer_email_template_id');
            $table->dropColumn('customize_email');
            $table->dropColumn('customize_attachment');
        });
    }
};
