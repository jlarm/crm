<?php

declare(strict_types=1);

use App\Actions\Dealerships\ImportDealershipRow;
use App\Jobs\ProcessDealershipImport;
use App\Models\Dealership;
use App\Models\User;
use App\Observers\ContactObserver;
use Illuminate\Support\Facades\Log;

beforeEach(function (): void {
    ContactObserver::$syncMailcoach = false;
});

afterEach(function (): void {
    ContactObserver::$syncMailcoach = true;
});

describe('ProcessDealershipImport job', function (): void {
    it('runs the import action with the validated rows and options', function (): void {
        $user = User::factory()->create();

        $rows = [[
            'line' => 1,
            'row_type' => 'dealership',
            'resolved' => [
                'name' => 'Imported Motors',
                'status' => 'active',
                'rating' => 'warm',
                'type' => 'Automotive',
            ],
            'errors' => [],
            'parent_ref' => null,
            'extra_user_emails' => [],
        ]];

        $options = [
            'importer_id' => $user->id,
            'default_user_ids' => [],
            'defaults' => ['status' => 'active', 'rating' => 'warm', 'type' => 'Automotive'],
            'sync_mailcoach' => false,
            'update_existing' => false,
            'transactional' => true,
        ];

        $job = new ProcessDealershipImport($rows, $options);
        $job->handle(app(ImportDealershipRow::class));

        expect(Dealership::where('name', 'Imported Motors')->exists())->toBeTrue();
    });

    it('logs context when failed() is called with a throwable', function (): void {
        $user = User::factory()->create();

        $rows = [[
            'line' => 1,
            'row_type' => 'dealership',
            'resolved' => ['name' => 'X'],
            'errors' => [],
            'parent_ref' => null,
            'extra_user_emails' => [],
        ]];

        $options = [
            'importer_id' => $user->id,
            'default_user_ids' => [],
            'defaults' => ['status' => 'active', 'rating' => 'warm', 'type' => 'Automotive'],
            'sync_mailcoach' => false,
            'update_existing' => false,
            'transactional' => true,
        ];

        Log::shouldReceive('error')
            ->once()
            ->withArgs(fn ($message, $context) => $message === '[ProcessDealershipImport] Import job failed.'
                && ($context['importer_id'] ?? null) === $user->id
                && ($context['row_count'] ?? null) === 1);

        (new ProcessDealershipImport($rows, $options))->failed(new RuntimeException('boom'));
    });

    it('logs even when failed() is invoked with null', function (): void {
        $user = User::factory()->create();

        $options = [
            'importer_id' => $user->id,
            'default_user_ids' => [],
            'defaults' => ['status' => 'active', 'rating' => 'warm', 'type' => 'Automotive'],
            'sync_mailcoach' => false,
            'update_existing' => false,
            'transactional' => true,
        ];

        Log::shouldReceive('error')
            ->once()
            ->withArgs(fn ($message, $context) => $message === '[ProcessDealershipImport] Import job failed.'
                && ($context['row_count'] ?? null) === 0);

        (new ProcessDealershipImport([], $options))->failed(null);
    });
});
