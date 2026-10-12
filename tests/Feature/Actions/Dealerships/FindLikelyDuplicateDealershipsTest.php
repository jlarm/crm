<?php

declare(strict_types=1);

use App\Actions\Dealerships\FindLikelyDuplicateDealerships;
use App\Models\Dealership;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

describe('forCandidate', function () {
    it('matches a name written differently', function () {
        $existing = Dealership::factory()->create(['user_id' => $this->user->id, 'name' => 'Smith Ford, Inc.']);

        $duplicate = app(FindLikelyDuplicateDealerships::class)->forCandidate('smith ford', null);

        expect($duplicate['reason'])->toBe('Similar name')
            ->and($duplicate['match']['id'])->toBe($existing->id);
    });

    it('matches the same phone number under another name', function () {
        $existing = Dealership::factory()->create(['user_id' => $this->user->id, 'name' => 'Smith Auto Group', 'phone' => '+1 (630) 555-0100']);

        $duplicate = app(FindLikelyDuplicateDealerships::class)->forCandidate('Smith Ford', '630.555.0100');

        expect($duplicate['reason'])->toBe('Same phone number')
            ->and($duplicate['match']['id'])->toBe($existing->id);
    });

    it('returns null when nothing matches', function () {
        Dealership::factory()->create(['user_id' => $this->user->id, 'name' => 'Jones Chevrolet', 'phone' => '312-555-0199']);

        expect(app(FindLikelyDuplicateDealerships::class)->forCandidate('Smith Ford', '630-555-0100'))->toBeNull();
    });
});
