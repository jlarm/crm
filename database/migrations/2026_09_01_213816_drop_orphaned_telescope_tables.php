<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Laravel Telescope was removed from the application some time ago — it is
     * absent from composer.lock and vendor, and nothing in the codebase
     * references it — but its tables were left behind. telescope_entries alone
     * held 1.39 million rows and 1.4 GB, which was the overwhelming majority of
     * the database and of every backup taken from it.
     *
     * Dropped in dependency order: the tags table has a foreign key onto
     * entries.
     */
    public function up(): void
    {
        Schema::dropIfExists('telescope_entries_tags');
        Schema::dropIfExists('telescope_entries');
        Schema::dropIfExists('telescope_monitoring');
    }

    public function down(): void
    {
        // Telescope is not installed, so there is no schema to restore these to
        // and no code that would read them. Recreating empty tables would only
        // reintroduce the clutter this migration removes.
    }
};
