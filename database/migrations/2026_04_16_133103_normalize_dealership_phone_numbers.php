<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('dealerships')
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->chunkById(200, function ($dealerships): void {
                foreach ($dealerships as $dealership) {
                    $digits = preg_replace('/\D/', '', $dealership->phone);

                    if ($digits !== $dealership->phone) {
                        DB::table('dealerships')
                            ->where('id', $dealership->id)
                            ->update(['phone' => $digits]);
                    }
                }
            });
    }

    public function down(): void
    {
        // Raw digits cannot be reliably re-formatted to their original form
    }
};
