<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Dealerships\ImportDealershipRow;
use App\Actions\Dealerships\ParseDealershipImportCsv;
use App\Actions\Dealerships\ValidateDealershipImportRow;
use App\Http\Requests\DealershipImportConfirmRequest;
use App\Http\Requests\DealershipImportPreviewRequest;
use App\Jobs\ProcessDealershipImport;
use App\Models\Dealership;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

final class DealershipImportController extends Controller
{
    private const int SYNC_THRESHOLD = 100;

    private const int CACHE_TTL_MINUTES = 30;

    public function create(): Response
    {
        return Inertia::render('Dealership/Import', [
            'allUsers' => User::query()->select('id', 'name')->orderBy('name')->get(),
            'preview' => null,
        ]);
    }

    public function preview(
        DealershipImportPreviewRequest $request,
        ParseDealershipImportCsv $parse,
        ValidateDealershipImportRow $validate,
    ): Response {
        $defaults = [
            'status' => is_string($s = $request->validated('default_status')) ? $s : '',
            'rating' => is_string($r = $request->validated('default_rating')) ? $r : '',
            'type' => is_string($t = $request->validated('default_type')) ? $t : '',
        ];

        /** @var array<int, mixed> $rawIds */
        $rawIds = $request->validated('default_user_ids', []);
        $defaultUserIds = array_values(array_unique(array_map(fn (mixed $v): int => is_numeric($v) ? (int) $v : 0, $rawIds)));

        $options = [
            'sync_mailcoach' => (bool) $request->validated('sync_mailcoach', false),
            'update_existing' => (bool) $request->validated('update_existing', false),
            'transactional' => (bool) $request->validated('transactional', true),
        ];

        $parsed = $parse($request->file('file'));

        $validated = [];
        $errorCount = 0;
        $summary = ['dealerships' => 0, 'stores' => 0, 'contacts' => 0];

        foreach ($parsed['rows'] as $row) {
            $result = $validate($row, $defaults);
            $validated[] = $result;

            if ($result['errors'] !== []) {
                $errorCount++;

                continue;
            }

            if ($result['row_type'] === 'dealership') {
                $summary['dealerships']++;
            } elseif ($result['row_type'] === 'store') {
                $summary['stores']++;
            } elseif ($result['row_type'] === 'contact') {
                $summary['contacts']++;
            }
        }

        $errorCount += count($parsed['parse_errors']);

        $autoCreateCount = $this->countAutoCreatedDealerships($validated);

        $token = Str::uuid()->toString();
        Cache::put('dealership-import:'.$token, [
            'validated' => $validated,
            'options' => [
                ...$options,
                'importer_id' => (int) auth()->id(),
                'default_user_ids' => $defaultUserIds,
                'defaults' => $defaults,
            ],
        ], now()->addMinutes(self::CACHE_TTL_MINUTES));

        return Inertia::render('Dealership/Import', [
            'allUsers' => User::query()->select('id', 'name')->orderBy('name')->get(),
            'preview' => [
                'token' => $token,
                'defaults' => $defaults,
                'defaultUserIds' => $defaultUserIds,
                'options' => $options,
                'summary' => [
                    ...$summary,
                    'errors' => $errorCount,
                    'autoCreatedDealerships' => $autoCreateCount,
                ],
                'parseErrors' => $parsed['parse_errors'],
                'rows' => array_map(fn (array $r): array => [
                    'line' => $r['line'],
                    'rowType' => $r['row_type'],
                    'resolved' => $r['resolved'],
                    'errors' => $r['errors'],
                    'parentRef' => $r['parent_ref'] ?? null,
                ], $validated),
            ],
        ]);
    }

    public function store(
        DealershipImportConfirmRequest $request,
        ImportDealershipRow $import,
    ): RedirectResponse {
        $token = is_string($t = $request->validated('token')) ? $t : '';
        $cacheKey = 'dealership-import:'.$token;
        /** @var array{validated: array<int, array{line: int, row_type: string, resolved: array<string, mixed>, errors: array<string, array<int, string>>, parent_ref: string|null, extra_user_emails: array<int, string>}>, options: array{importer_id: int, default_user_ids: array<int, int>, defaults: array{status: string, rating: string, type: string}, sync_mailcoach: bool, update_existing: bool, transactional: bool}}|null $payload */
        $payload = Cache::get($cacheKey);

        if (! $payload) {
            return redirect()
                ->route('dealerships.import.create')
                ->with('error', 'Import preview expired. Please re-upload the CSV.');
        }

        $validRows = array_values(array_filter(
            $payload['validated'],
            fn (array $r): bool => empty($r['errors'])
        ));

        if (count($validRows) > self::SYNC_THRESHOLD) {
            ProcessDealershipImport::dispatch($validRows, $payload['options']);
            Cache::forget($cacheKey);

            return redirect()
                ->route('dashboard')
                ->with('success', sprintf(
                    'Import queued: %d rows. You will be notified when complete.',
                    count($validRows)
                ));
        }

        $stats = $import($validRows, $payload['options']);
        Cache::forget($cacheKey);

        $message = sprintf(
            'Import complete: %d dealerships, %d stores, %d contacts created. %d updated. %d skipped.',
            $stats['created']['dealerships'],
            $stats['created']['stores'],
            $stats['created']['contacts'],
            $stats['updated']['dealerships'] + $stats['updated']['stores'] + $stats['updated']['contacts'],
            $stats['skipped'],
        );

        return redirect()->route('dashboard')->with('success', $message);
    }

    /**
     * Count unique dealership names that will be auto-created from contact/store rows
     * referencing a dealership not present in the file or in the database.
     *
     * @param  array<int, array<string, mixed>>  $validated
     */
    private function countAutoCreatedDealerships(array $validated): int
    {
        $dealershipNames = [];
        $orphanRefs = [];

        foreach ($validated as $row) {
            if (! empty($row['errors'])) {
                continue;
            }

            if ($row['row_type'] === 'dealership') {
                $resolved = is_array($row['resolved']) ? $row['resolved'] : [];
                $name = is_string($resolved['name'] ?? null) ? $resolved['name'] : '';
                $dealershipNames[mb_strtolower($name)] = true;

                continue;
            }

            $parentRef = $row['parent_ref'] ?? null;
            if (is_string($parentRef) && $parentRef !== '') {
                $orphanRefs[mb_strtolower($parentRef)] = $parentRef;
            }
        }

        $orphans = array_diff_key($orphanRefs, $dealershipNames);

        if ($orphans === []) {
            return 0;
        }

        $existingNames = Dealership::query()
            ->whereIn(DB::raw('LOWER(name)'), array_keys($orphans))
            ->pluck('name')
            ->map(fn (mixed $n): string => mb_strtolower(is_string($n) ? $n : ''))
            ->all();

        return count(array_diff_key($orphans, array_flip($existingNames)));
    }
}
