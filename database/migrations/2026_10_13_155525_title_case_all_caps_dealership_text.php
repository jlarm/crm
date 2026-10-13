<?php

declare(strict_types=1);

use App\Support\TitleCase;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * One-time cleanup: rewrites existing ALL-CAPS names, addresses, cities and positions
 * in title case, as new saves now do. Updates the tables directly so the activity log
 * isn't filled with thousands of formatting edits. Mixed-case values are untouched.
 */
return new class extends Migration
{
    /**
     * @var array<string, list<string>>
     */
    private array $columns = [
        'dealerships' => ['name', 'address', 'city'],
        'stores' => ['name', 'address', 'city'],
        'contacts' => ['name', 'position'],
    ];

    public function up(): void
    {
        foreach ($this->columns as $table => $columns) {
            DB::table($table)
                ->select(['id', ...$columns])
                ->orderBy('id')
                ->chunkById(1000, function ($rows) use ($table, $columns): void {
                    foreach ($rows as $row) {
                        $changes = [];

                        foreach ($columns as $column) {
                            $value = $row->{$column};
                            $titled = is_string($value) ? TitleCase::fromAllCaps($value) : $value;

                            if ($titled !== $value) {
                                $changes[$column] = $titled;
                            }
                        }

                        if ($changes !== []) {
                            DB::table($table)->where('id', $row->id)->update($changes);
                        }
                    }
                });
        }
    }

    public function down(): void
    {
        // The original capitalisation isn't kept, so this can't be reversed.
    }
};
