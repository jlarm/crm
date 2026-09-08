<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Laravel Pulse was removed from the application alongside Telescope — it is
     * absent from composer.lock and vendor, and nothing in the codebase
     * references it — but its tables were left behind. There are no foreign keys
     * between them, so the drop order does not matter.
     *
     * @see 2026_09_01_213816_drop_orphaned_telescope_tables
     */
    public function up(): void
    {
        Schema::dropIfExists('pulse_aggregates');
        Schema::dropIfExists('pulse_entries');
        Schema::dropIfExists('pulse_values');
    }

    public function down(): void
    {
        // Pulse is not installed, so there is no schema to restore these to and
        // no code that would read them.
    }
};
