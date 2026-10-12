<?php

declare(strict_types=1);

use App\Ai\Agents\DealershipFinder;
use App\Models\Dealership;
use App\Models\User;
use Laravel\Ai\Prompts\AgentPrompt;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

beforeEach(function () {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

function smithFordResult(array $overrides = []): array
{
    return array_merge([
        'name' => 'Smith Ford',
        'address' => '1200 W Ogden Ave',
        'city' => 'Naperville',
        'state' => 'IL',
        'zip_code' => '60540',
        'phone' => '630-555-0100',
        'website' => 'https://smithford.com',
        'source_url' => 'https://smithford.com/contact',
    ], $overrides);
}

describe('looking up a dealership online', function () {
    it('returns the businesses found, searching with the name and location', function () {
        DealershipFinder::fake([['results' => [smithFordResult()]]]);

        postJson(route('dealerships.lookup'), ['name' => 'Smith Ford', 'city' => 'Naperville', 'state' => 'IL'])
            ->assertOk()
            ->assertExactJson([[
                'name' => 'Smith Ford',
                'address' => '1200 W Ogden Ave',
                'city' => 'Naperville',
                'state' => 'IL',
                'zip_code' => '60540',
                'phone' => '(630) 555-0100',
                'website' => 'https://smithford.com',
                'sourceUrl' => 'https://smithford.com/contact',
                'duplicate' => null,
            ]]);

        DealershipFinder::assertPrompted(fn (AgentPrompt $prompt) => $prompt->contains('Smith Ford') && $prompt->contains('Naperville, IL'));
    });

    it('drops values that do not look like a state, zip, phone or url', function () {
        DealershipFinder::fake([['results' => [smithFordResult([
            'state' => 'Illinois',
            'zip_code' => 'unknown',
            'phone' => '555-0100',
            'website' => 'smithford dot com',
            'source_url' => 'javascript:alert(1)',
        ])]]]);

        postJson(route('dealerships.lookup'), ['name' => 'Smith Ford'])
            ->assertOk()
            ->assertJsonPath('0.state', null)
            ->assertJsonPath('0.zip_code', null)
            ->assertJsonPath('0.phone', null)
            ->assertJsonPath('0.website', null)
            ->assertJsonPath('0.sourceUrl', null);
    });

    it('skips nameless and repeated results', function () {
        DealershipFinder::fake([['results' => [
            smithFordResult(),
            smithFordResult(['phone' => '630-555-0199']),
            smithFordResult(['name' => '']),
            smithFordResult(['name' => 'Smith Ford', 'address' => '400 E Ogden Ave']),
        ]]]);

        postJson(route('dealerships.lookup'), ['name' => 'Smith Ford'])
            ->assertOk()
            ->assertJsonCount(2)
            ->assertJsonPath('1.address', '400 E Ogden Ave');
    });

    it('flags a result that is already in the CRM', function () {
        $existing = Dealership::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Smith Ford, Inc.',
            'city' => 'Naperville',
            'state' => 'IL',
        ]);
        DealershipFinder::fake([['results' => [smithFordResult()]]]);

        postJson(route('dealerships.lookup'), ['name' => 'Smith Ford'])
            ->assertOk()
            ->assertJsonPath('0.duplicate', [
                'reason' => 'Similar name',
                'match' => ['id' => $existing->id, 'name' => 'Smith Ford, Inc.', 'city' => 'Naperville', 'state' => 'IL'],
            ]);
    });

    it('requires a name of at least three characters', function () {
        DealershipFinder::fake();

        postJson(route('dealerships.lookup'), ['name' => 'Sm'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('name');

        DealershipFinder::assertNeverPrompted();
    });

    it('returns a friendly error when the AI call fails', function () {
        DealershipFinder::fake(fn () => throw new RuntimeException('Provider down'));

        postJson(route('dealerships.lookup'), ['name' => 'Smith Ford'])
            ->assertStatus(503)
            ->assertJsonPath('message', "Couldn't search for this dealership right now. You can still fill in the details by hand.");
    });

    it('requires authentication', function () {
        auth()->logout();

        postJson(route('dealerships.lookup'), ['name' => 'Smith Ford'])->assertUnauthorized();
    });
});
