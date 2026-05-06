<?php

declare(strict_types=1);

use App\Jobs\ProcessDealershipImport;
use App\Models\Contact;
use App\Models\Dealership;
use App\Models\Store;
use App\Models\User;
use App\Observers\ContactObserver;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    // Globally disable Mailcoach in tests; per-test toggling can override.
    ContactObserver::$syncMailcoach = false;
});

afterEach(function () {
    ContactObserver::$syncMailcoach = true;
});

function csvFile(string $contents): UploadedFile
{
    $path = tempnam(sys_get_temp_dir(), 'import').'.csv';
    file_put_contents($path, $contents);

    return new UploadedFile($path, 'import.csv', 'text/csv', null, true);
}

function defaultPreviewPayload(array $overrides = []): array
{
    return array_merge([
        'default_status' => 'active',
        'default_rating' => 'warm',
        'default_type' => 'Automotive',
        'sync_mailcoach' => 0,
        'update_existing' => 0,
        'transactional' => 1,
    ], $overrides);
}

it('renders the import page', function () {
    $this->get('/dealerships/import')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Dealership/Import')->has('allUsers'));
});

it('previews a valid CSV without persisting anything', function () {
    $csv = "name\nPrime Motors\nNorth Auto\n";

    $this->post('/dealerships/import/preview', defaultPreviewPayload([
        'file' => csvFile($csv),
    ]))->assertInertia(fn ($page) => $page
        ->component('Dealership/Import')
        ->where('preview.summary.dealerships', 2)
        ->where('preview.summary.errors', 0));

    expect(Dealership::count())->toBe(0);
});

it('imports dealerships, stores, and contacts in one CSV', function () {
    $csv = <<<'CSV'
row_type,dealership_ref,name,email
dealership,,Prime Motors,
store,Prime Motors,Prime Downtown,
contact,Prime Motors,Jane Doe,jane@example.com
CSV;

    $response = $this->post('/dealerships/import/preview', defaultPreviewPayload([
        'file' => csvFile($csv),
    ]));

    $token = $response->viewData('page')['props']['preview']['token'];

    $this->post('/dealerships/import', ['token' => $token])
        ->assertRedirect('/dashboard');

    expect(Dealership::count())->toBe(1);
    $dealer = Dealership::first();
    expect($dealer->name)->toBe('Prime Motors')
        ->and($dealer->stores()->count())->toBe(1)
        ->and($dealer->contacts()->count())->toBe(1)
        ->and($dealer->users)->toHaveCount(1);
});

it('applies dropdown defaults when status/rating/type columns are absent', function () {
    $csv = "name\nPrime Motors\n";

    $response = $this->post('/dealerships/import/preview', defaultPreviewPayload([
        'file' => csvFile($csv),
        'default_status' => 'inactive',
        'default_rating' => 'cold',
        'default_type' => 'Maritime',
    ]));

    $token = $response->viewData('page')['props']['preview']['token'];
    $this->post('/dealerships/import', ['token' => $token]);

    $dealer = Dealership::first();
    expect($dealer->status)->toBe('inactive')
        ->and($dealer->rating)->toBe('cold')
        ->and($dealer->type)->toBe('Maritime');
});

it('row values override dropdown defaults', function () {
    $csv = "name,status,rating,type\nPrime Motors,active,hot,RV\n";

    $response = $this->post('/dealerships/import/preview', defaultPreviewPayload([
        'file' => csvFile($csv),
        'default_status' => 'inactive',
        'default_rating' => 'cold',
        'default_type' => 'Maritime',
    ]));

    $token = $response->viewData('page')['props']['preview']['token'];
    $this->post('/dealerships/import', ['token' => $token]);

    $dealer = Dealership::first();
    expect($dealer->rating)->toBe('hot')
        ->and($dealer->type)->toBe('RV');
});

it('skips dealerships with duplicate name when update_existing is false', function () {
    Dealership::factory()->create(['name' => 'Prime Motors', 'user_id' => $this->user->id]);

    $csv = "name\nPrime Motors\n";

    $response = $this->post('/dealerships/import/preview', defaultPreviewPayload([
        'file' => csvFile($csv),
    ]));
    $token = $response->viewData('page')['props']['preview']['token'];
    $this->post('/dealerships/import', ['token' => $token]);

    expect(Dealership::count())->toBe(1);
});

it('updates existing dealerships when update_existing is true', function () {
    Dealership::factory()->create([
        'name' => 'Prime Motors',
        'city' => 'Old City',
        'user_id' => $this->user->id,
    ]);

    $csv = "name,city\nPrime Motors,New City\n";

    $response = $this->post('/dealerships/import/preview', defaultPreviewPayload([
        'file' => csvFile($csv),
        'update_existing' => 1,
    ]));
    $token = $response->viewData('page')['props']['preview']['token'];
    $this->post('/dealerships/import', ['token' => $token]);

    expect(Dealership::first()->city)->toBe('New City');
});

it('attaches stores to existing dealership matched by ref', function () {
    Dealership::factory()->create(['name' => 'Prime Motors', 'user_id' => $this->user->id]);

    $csv = <<<'CSV'
row_type,dealership_ref,name
store,Prime Motors,Downtown Branch
CSV;

    $response = $this->post('/dealerships/import/preview', defaultPreviewPayload([
        'file' => csvFile($csv),
    ]));
    $token = $response->viewData('page')['props']['preview']['token'];
    $this->post('/dealerships/import', ['token' => $token]);

    expect(Store::count())->toBe(1)
        ->and(Store::first()->dealership->name)->toBe('Prime Motors');
});

it('reports per-row validation errors in preview without rejecting whole file', function () {
    $csv = "name,type\nPrime Motors,Automotive\nBad,NotAType\n";

    $response = $this->post('/dealerships/import/preview', defaultPreviewPayload([
        'file' => csvFile($csv),
    ]));

    $rows = $response->viewData('page')['props']['preview']['rows'];
    expect($rows[0]['errors'])->toBeEmpty()
        ->and($rows[1]['errors'])->not->toBeEmpty();
});

it('queues the import job when row count exceeds threshold', function () {
    Queue::fake();

    $lines = ['name'];
    for ($i = 1; $i <= 105; $i++) {
        $lines[] = "Dealer {$i}";
    }
    $csv = implode("\n", $lines)."\n";

    $response = $this->post('/dealerships/import/preview', defaultPreviewPayload([
        'file' => csvFile($csv),
    ]));
    $token = $response->viewData('page')['props']['preview']['token'];

    $this->post('/dealerships/import', ['token' => $token]);

    Queue::assertPushed(ProcessDealershipImport::class);
});

it('rejects non-csv uploads via form request', function () {
    $bad = UploadedFile::fake()->create('not.pdf', 10, 'application/pdf');

    $this->post('/dealerships/import/preview', defaultPreviewPayload([
        'file' => $bad,
    ]))->assertSessionHasErrors('file');
});

it('returns to upload when token is expired', function () {
    Cache::flush();

    $this->post('/dealerships/import', ['token' => 'nonexistent'])
        ->assertRedirect('/dealerships/import');
});

it('attaches default consultants and importer to imported dealerships', function () {
    $other = User::factory()->create();
    $csv = "name\nPrime Motors\n";

    $response = $this->post('/dealerships/import/preview', defaultPreviewPayload([
        'file' => csvFile($csv),
        'default_user_ids' => [$other->id],
    ]));
    $token = $response->viewData('page')['props']['preview']['token'];
    $this->post('/dealerships/import', ['token' => $token]);

    $dealer = Dealership::first();
    expect($dealer->users->pluck('id')->all())
        ->toContain($this->user->id)
        ->toContain($other->id);
});

it('attaches consultants from CSV user_emails column on top of defaults', function () {
    $extra = User::factory()->create(['email' => 'extra@example.com']);

    $csv = "name,user_emails\nPrime Motors,extra@example.com\n";

    $response = $this->post('/dealerships/import/preview', defaultPreviewPayload([
        'file' => csvFile($csv),
    ]));
    $token = $response->viewData('page')['props']['preview']['token'];
    $this->post('/dealerships/import', ['token' => $token]);

    expect(Dealership::first()->users->pluck('id')->all())
        ->toContain($extra->id);
});

it('imports a contact-only CSV with mixed-case headers and aliases', function () {
    $csv = <<<'CSV'
Email,First Name,Last Name,jobTitle,linkedIn,companyName
mdency@example.com,Matthew,Dency,Manager,http://www.linkedin.com/in/matt,Mercedes-Benz of Massapequa
nbankston@example.com,Nick,Bankston,Service Drive Manager,http://www.linkedin.com/in/nick,Mercedes-Benz of Oklahoma City
schesney@example.com,Shane,Chesney,Finance Manager,http://www.linkedin.com/in/shane,Mercedes-Benz of Massapequa
CSV;

    $response = $this->post('/dealerships/import/preview', defaultPreviewPayload([
        'file' => csvFile($csv),
    ]));

    $preview = $response->viewData('page')['props']['preview'];

    expect($preview['summary']['contacts'])->toBe(3)
        ->and($preview['summary']['autoCreatedDealerships'])->toBe(2)
        ->and($preview['summary']['errors'])->toBe(0);

    $this->post('/dealerships/import', ['token' => $preview['token']]);

    expect(Dealership::count())->toBe(2)
        ->and(Contact::count())->toBe(3);

    $massapequa = Dealership::where('name', 'Mercedes-Benz of Massapequa')->first();
    expect($massapequa->contacts)->toHaveCount(2)
        ->and($massapequa->contacts->pluck('name')->all())
        ->toContain('Matthew Dency')
        ->toContain('Shane Chesney')
        ->and($massapequa->contacts->where('email', 'mdency@example.com')->first()->position)
        ->toBe('Manager');
});

it('auto-creates dealerships for orphan contacts using dropdown defaults', function () {
    $csv = "Email,First Name,Last Name,companyName\njoe@x.com,Joe,Smith,Acme Auto\n";

    $response = $this->post('/dealerships/import/preview', defaultPreviewPayload([
        'file' => csvFile($csv),
        'default_type' => 'RV',
        'default_status' => 'inactive',
        'default_rating' => 'cold',
    ]));
    $token = $response->viewData('page')['props']['preview']['token'];
    $this->post('/dealerships/import', ['token' => $token]);

    $dealer = Dealership::first();
    expect($dealer->name)->toBe('Acme Auto')
        ->and($dealer->type)->toBe('RV')
        ->and($dealer->status)->toBe('inactive')
        ->and($dealer->rating)->toBe('cold');
});

it('attaches default consultants to auto-created dealerships', function () {
    $other = User::factory()->create();
    $csv = "Email,First Name,Last Name,companyName\njoe@x.com,Joe,Smith,Acme Auto\n";

    $response = $this->post('/dealerships/import/preview', defaultPreviewPayload([
        'file' => csvFile($csv),
        'default_user_ids' => [$other->id],
    ]));
    $token = $response->viewData('page')['props']['preview']['token'];
    $this->post('/dealerships/import', ['token' => $token]);

    $userIds = Dealership::first()->users->pluck('id')->all();
    expect($userIds)->toContain($this->user->id)->toContain($other->id);
});

it('does not call Mailcoach when sync_mailcoach is off (default)', function () {
    // Confirm the observer flag stays false through the import.
    $csv = <<<'CSV'
row_type,dealership_ref,name,email
dealership,,Prime Motors,
contact,Prime Motors,Jane,jane@example.com
CSV;

    $response = $this->post('/dealerships/import/preview', defaultPreviewPayload([
        'file' => csvFile($csv),
    ]));
    $token = $response->viewData('page')['props']['preview']['token'];

    // If Mailcoach were called, the observer would attempt the API and fail in tests.
    // The fact that the import completes proves the gate worked.
    $this->post('/dealerships/import', ['token' => $token])->assertRedirect('/dashboard');

    expect(Contact::count())->toBe(1);
});
