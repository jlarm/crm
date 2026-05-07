<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\Dealerships\ImportDealershipRow;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessDealershipImport implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    public int $timeout = 600;

    /**
     * @param  array<int, array{line: int, row_type: string, resolved: array<string, mixed>, errors: array<string, array<int, string>>, parent_ref: string|null, extra_user_emails: array<int, string>}>  $validatedRows
     * @param  array{importer_id: int, default_user_ids: array<int, int>, defaults: array{status: string, rating: string, type: string}, sync_mailcoach: bool, update_existing: bool, transactional: bool}  $options
     */
    public function __construct(
        protected array $validatedRows,
        protected array $options,
    ) {}

    public function handle(ImportDealershipRow $import): void
    {
        $import($this->validatedRows, $this->options);
    }

    public function failed(?Throwable $e): void
    {
        Log::error('[ProcessDealershipImport] Import job failed.', [
            'importer_id' => $this->options['importer_id'],
            'row_count' => count($this->validatedRows),
            'exception' => $e,
        ]);
    }
}
