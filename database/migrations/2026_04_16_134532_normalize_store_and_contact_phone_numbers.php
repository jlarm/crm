<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['stores', 'contacts'] as $table) {
            DB::table($table)
                ->whereNotNull('phone')
                ->where('phone', '!=', '')
                ->chunkById(200, function ($rows) use ($table): void {
                    foreach ($rows as $row) {
                        $digits = preg_replace('/\D/', '', $row->phone);

                        if ($digits !== $row->phone) {
                            DB::table($table)
                                ->where('id', $row->id)
                                ->update(['phone' => $digits]);
                        }
                    }
                });
        }
    }

    public function down(): void
    {
        // Raw digits cannot be reliably re-formatted to their original form
    }
};
