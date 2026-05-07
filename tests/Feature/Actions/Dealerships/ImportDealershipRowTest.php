<?php

declare(strict_types=1);

use App\Actions\Dealerships\ImportDealershipRow;
use App\Models\Contact;
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

function importerOptions(int $userId, array $overrides = []): array
{
    return array_merge([
        'importer_id' => $userId,
        'default_user_ids' => [],
        'defaults' => ['status' => 'active', 'rating' => 'warm', 'type' => 'Automotive'],
        'sync_mailcoach' => false,
        'update_existing' => false,
        'transactional' => true,
    ], $overrides);
}

function dealershipRow(string $name, array $extras = []): array
{
    return [
        'line' => 1,
        'row_type' => 'dealership',
        'resolved' => array_merge([
            'name' => $name,
            'status' => 'active',
            'rating' => 'warm',
            'type' => 'Automotive',
        ], $extras),
        'errors' => [],
        'parent_ref' => null,
        'extra_user_emails' => [],
    ];
}

describe('ImportDealershipRow action', function (): void {
    it('runs in non-transactional mode and rolls back individual failed groups', function (): void {
        $user = User::factory()->create();

        // The second group will fail because primary_contact is non-bool, etc.
        $goodRow = dealershipRow('Good Motors');
        $badRow = [
            'line' => 2,
            'row_type' => 'dealership',
            'resolved' => [
                // Force a DB failure by using an invalid status that violates a non-existent
                // column; instead, use a value that triggers the rescue path. Easiest: skip
                // the resolved name to cause SQLite NOT NULL.
                'name' => null,
                'status' => 'active',
                'rating' => 'warm',
                'type' => 'Automotive',
            ],
            'errors' => [],
            'parent_ref' => null,
            'extra_user_emails' => [],
        ];

        Log::shouldReceive('error')->atLeast()->once();

        $action = new ImportDealershipRow;
        $stats = $action(
            [$goodRow, $badRow],
            importerOptions($user->id, ['transactional' => false]),
        );

        expect($stats['created']['dealerships'])->toBe(1)
            ->and($stats['errors'])->not->toBeEmpty()
            ->and(Dealership::where('name', 'Good Motors')->exists())->toBeTrue();
    });

    it('toggles ContactObserver mailcoach flag and restores it after the import', function (): void {
        ContactObserver::$syncMailcoach = false;

        $user = User::factory()->create();

        $action = new ImportDealershipRow;
        $action(
            [dealershipRow('Toggle Motors')],
            importerOptions($user->id, ['sync_mailcoach' => true]),
        );

        expect(ContactObserver::$syncMailcoach)->toBeFalse();
    });

    it('handles orphan contact rows whose parent_ref does not match an existing dealership', function (): void {
        $user = User::factory()->create();

        // Orphan contact pointing to non-existent dealership ref (no dealership row provided).
        $contactRow = [
            'line' => 1,
            'row_type' => 'contact',
            'resolved' => [
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
            ],
            'errors' => [],
            'parent_ref' => 'Phantom Motors',
            'extra_user_emails' => [],
        ];

        $action = new ImportDealershipRow;
        $stats = $action([$contactRow], importerOptions($user->id));

        // Auto-creates the dealership and the contact under it.
        expect($stats['created']['dealerships'])->toBe(1)
            ->and($stats['created']['contacts'])->toBe(1)
            ->and(Contact::where('email', 'jane@example.com')->exists())->toBeTrue();
    });

    it('skips contact and store rows whose parent_ref is empty or non-string', function (): void {
        $user = User::factory()->create();

        $rows = [
            dealershipRow('Anchor Motors'),
            // Contact with empty parent_ref - skipped at grouping time.
            [
                'line' => 2,
                'row_type' => 'contact',
                'resolved' => ['name' => 'Skip Me', 'email' => 'skip@example.com'],
                'errors' => [],
                'parent_ref' => '',
                'extra_user_emails' => [],
            ],
            // Store with non-string parent_ref - skipped at grouping time.
            [
                'line' => 3,
                'row_type' => 'store',
                'resolved' => ['name' => 'Ghost Store'],
                'errors' => [],
                'parent_ref' => null,
                'extra_user_emails' => [],
            ],
        ];

        $action = new ImportDealershipRow;
        $stats = $action($rows, importerOptions($user->id));

        expect($stats['created']['dealerships'])->toBe(1)
            ->and($stats['created']['contacts'])->toBe(0)
            ->and($stats['created']['stores'])->toBe(0);
    });

    it('skips rows with pre-existing validation errors entirely', function (): void {
        $user = User::factory()->create();

        $rows = [
            [
                'line' => 1,
                'row_type' => 'dealership',
                'resolved' => ['name' => 'Skipped'],
                'errors' => ['name' => ['boom']],
                'parent_ref' => null,
                'extra_user_emails' => [],
            ],
            dealershipRow('Real Motors'),
        ];

        $action = new ImportDealershipRow;
        $stats = $action($rows, importerOptions($user->id));

        expect(Dealership::where('name', 'Skipped')->exists())->toBeFalse()
            ->and($stats['created']['dealerships'])->toBe(1);
    });

    it('attaches consultants from extra_user_emails resolving via the prefetch cache', function (): void {
        $importer = User::factory()->create();
        $extra = User::factory()->create(['email' => 'extra@example.com']);

        $rows = [[
            'line' => 1,
            'row_type' => 'dealership',
            'resolved' => [
                'name' => 'Cache Motors',
                'status' => 'active',
                'rating' => 'warm',
                'type' => 'Automotive',
            ],
            'errors' => [],
            'parent_ref' => null,
            'extra_user_emails' => ['extra@example.com', '', 'unknown@example.com'],
        ]];

        $action = new ImportDealershipRow;
        $action($rows, importerOptions($importer->id));

        $dealer = Dealership::where('name', 'Cache Motors')->first();
        expect($dealer->users->pluck('id')->all())
            ->toContain($importer->id)
            ->toContain($extra->id);
    });

    it('updates existing stores and contacts when update_existing is true', function (): void {
        $importer = User::factory()->create();
        $dealer = Dealership::factory()->create([
            'name' => 'Existing Motors',
            'user_id' => $importer->id,
        ]);
        $dealer->stores()->create([
            'name' => 'Main Branch',
            'user_id' => $importer->id,
        ]);
        $dealer->contacts()->create([
            'name' => 'Old Name',
            'email' => 'rep@example.com',
        ]);

        $rows = [
            dealershipRow('Existing Motors'),
            [
                'line' => 2,
                'row_type' => 'store',
                'resolved' => ['name' => 'Main Branch', 'city' => 'Newville'],
                'errors' => [],
                'parent_ref' => 'Existing Motors',
                'extra_user_emails' => [],
            ],
            [
                'line' => 3,
                'row_type' => 'contact',
                'resolved' => ['name' => 'Updated Name', 'email' => 'rep@example.com'],
                'errors' => [],
                'parent_ref' => 'Existing Motors',
                'extra_user_emails' => [],
            ],
        ];

        $action = new ImportDealershipRow;
        $stats = $action($rows, importerOptions($importer->id, ['update_existing' => true]));

        expect($stats['updated']['stores'])->toBe(1)
            ->and($stats['updated']['contacts'])->toBe(1)
            ->and($dealer->fresh()->contacts->first()->name)->toBe('Updated Name');
    });

    it('returns no-ops when given an empty validatedRows array', function (): void {
        // Empty input -> $groups === [] -> prefetchDealerships and prefetchUsers
        // hit their early returns (lines 166 and 204..205).
        $action = new ImportDealershipRow;
        $stats = $action([], importerOptions(0));

        expect($stats['created']['dealerships'])->toBe(0)
            ->and($stats['errors'])->toBe([]);
    });

    it('skips when extra_user_emails on a row is not an array', function (): void {
        $importer = User::factory()->create();

        $rows = [[
            'line' => 1,
            'row_type' => 'dealership',
            'resolved' => [
                'name' => 'NonArrayExtras Motors',
                'status' => 'active',
                'rating' => 'warm',
                'type' => 'Automotive',
            ],
            'errors' => [],
            'parent_ref' => null,
            // Non-array extra_user_emails -> line 188 'continue' arm.
            'extra_user_emails' => 'not-an-array',
        ]];

        $action = new ImportDealershipRow;
        // @phpstan-ignore argument.type
        $action($rows, importerOptions($importer->id));

        expect(Dealership::where('name', 'NonArrayExtras Motors')->exists())->toBeTrue();
    });

    it('skips non-string and empty extra_user_emails entries', function (): void {
        $importer = User::factory()->create();
        User::factory()->create(['email' => 'real@example.com']);

        $rows = [[
            'line' => 1,
            'row_type' => 'dealership',
            'resolved' => [
                'name' => 'Mixed Inputs Motors',
                'status' => 'active',
                'rating' => 'warm',
                'type' => 'Automotive',
            ],
            'errors' => [],
            'parent_ref' => null,
            // Mix in non-string entries (covers line 188/193 'continue' arms in
            // prefetchUsers).
            'extra_user_emails' => [123, '', 'real@example.com', null],
        ]];

        $action = new ImportDealershipRow;
        $action($rows, importerOptions($importer->id));

        expect(Dealership::where('name', 'Mixed Inputs Motors')->exists())->toBeTrue();
    });

    it('creates a contact with no email under an existing dealership', function (): void {
        $importer = User::factory()->create();
        $dealer = Dealership::factory()->create([
            'name' => 'NoEmail Motors',
            'user_id' => $importer->id,
        ]);

        // Contact row with no email -> $existing remains null (line 367 branch).
        $rows = [
            dealershipRow('NoEmail Motors'),
            [
                'line' => 2,
                'row_type' => 'contact',
                'resolved' => ['name' => 'Anonymous Contact'],
                'errors' => [],
                'parent_ref' => 'NoEmail Motors',
                'extra_user_emails' => [],
            ],
        ];

        $action = new ImportDealershipRow;
        $action($rows, importerOptions($importer->id));

        $dealer->refresh();
        expect($dealer->contacts()->where('name', 'Anonymous Contact')->exists())->toBeTrue();
    });

    it('skips existing stores and contacts when update_existing is false', function (): void {
        $importer = User::factory()->create();
        $dealer = Dealership::factory()->create([
            'name' => 'Existing Motors',
            'user_id' => $importer->id,
        ]);
        $dealer->stores()->create([
            'name' => 'Main Branch',
            'user_id' => $importer->id,
        ]);
        $dealer->contacts()->create([
            'name' => 'Old',
            'email' => 'rep@example.com',
        ]);

        $rows = [
            dealershipRow('Existing Motors'),
            [
                'line' => 2,
                'row_type' => 'store',
                'resolved' => ['name' => 'Main Branch'],
                'errors' => [],
                'parent_ref' => 'Existing Motors',
                'extra_user_emails' => [],
            ],
            [
                'line' => 3,
                'row_type' => 'contact',
                'resolved' => ['name' => 'New', 'email' => 'rep@example.com'],
                'errors' => [],
                'parent_ref' => 'Existing Motors',
                'extra_user_emails' => [],
            ],
        ];

        $action = new ImportDealershipRow;
        $stats = $action($rows, importerOptions($importer->id));

        // 1 dealership skipped + 1 store skipped + 1 contact skipped.
        expect($stats['skipped'])->toBeGreaterThanOrEqual(2)
            ->and($stats['updated']['contacts'])->toBe(0);
    });
});
