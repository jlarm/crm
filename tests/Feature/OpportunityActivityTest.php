<?php

declare(strict_types=1);

use App\Enum\ActivityType;
use App\Models\Dealership;
use App\Models\Opportunity;
use App\Models\OpportunityActivity;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->dealership = Dealership::factory()->create();
    $this->opportunity = Opportunity::factory()->create(['dealership_id' => $this->dealership->id]);
});

it('stores an activity for an opportunity', function () {
    $this->actingAs($this->user)
        ->post("/dealerships/{$this->dealership->id}/opportunities/{$this->opportunity->id}/activities", [
            'type' => 'call',
            'details' => 'Spoke with the GM about pricing.',
            'occurred_at' => '2026-04-10',
        ])
        ->assertRedirect();

    $activity = OpportunityActivity::first();
    expect($activity->opportunity_id)->toBe($this->opportunity->id);
    expect($activity->user_id)->toBe($this->user->id);
    expect($activity->type->value)->toBe('call');
    expect($activity->details)->toBe('Spoke with the GM about pricing.');
    expect($activity->occurred_at->format('Y-m-d'))->toBe('2026-04-10');
});

it('assigns the authenticated user as the activity author', function () {
    $this->actingAs($this->user)
        ->post("/dealerships/{$this->dealership->id}/opportunities/{$this->opportunity->id}/activities", [
            'type' => 'note',
            'details' => 'Left a voicemail.',
        ]);

    expect(OpportunityActivity::first()->user_id)->toBe($this->user->id);
});

it('requires type and details', function () {
    $this->actingAs($this->user)
        ->post("/dealerships/{$this->dealership->id}/opportunities/{$this->opportunity->id}/activities", [])
        ->assertSessionHasErrors(['type', 'details']);
});

it('rejects an invalid activity type', function () {
    $this->actingAs($this->user)
        ->post("/dealerships/{$this->dealership->id}/opportunities/{$this->opportunity->id}/activities", [
            'type' => 'fax',
            'details' => 'Sent a fax.',
        ])
        ->assertSessionHasErrors(['type']);
});

it('accepts all valid activity types', function (string $type) {
    $this->actingAs($this->user)
        ->post("/dealerships/{$this->dealership->id}/opportunities/{$this->opportunity->id}/activities", [
            'type' => $type,
            'details' => 'Activity detail.',
        ])
        ->assertRedirect();

    expect(OpportunityActivity::where('type', $type)->exists())->toBeTrue();
})->with(array_column(ActivityType::cases(), 'value'));

it('allows occurred_at to be omitted', function () {
    $this->actingAs($this->user)
        ->post("/dealerships/{$this->dealership->id}/opportunities/{$this->opportunity->id}/activities", [
            'type' => 'email',
            'details' => 'Sent a proposal email.',
        ])
        ->assertRedirect();

    expect(OpportunityActivity::first()->occurred_at)->toBeNull();
});

it('deletes an activity', function () {
    $activity = OpportunityActivity::factory()->create([
        'opportunity_id' => $this->opportunity->id,
        'user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user)
        ->delete("/dealerships/{$this->dealership->id}/opportunities/{$this->opportunity->id}/activities/{$activity->id}")
        ->assertRedirect();

    $this->assertModelMissing($activity);
});

it('requires authentication', function () {
    $this->post("/dealerships/{$this->dealership->id}/opportunities/{$this->opportunity->id}/activities", [
        'type' => 'note',
        'details' => 'Test.',
    ])->assertRedirect('/login');
});

it('includes activities in the dealership show response', function () {
    OpportunityActivity::factory()->create([
        'opportunity_id' => $this->opportunity->id,
        'user_id' => $this->user->id,
        'type' => 'call',
        'details' => 'Demo call completed.',
    ]);

    $this->actingAs($this->user)
        ->get("/dealerships/{$this->dealership->id}")
        ->assertInertia(fn ($page) => $page
            ->component('Dealership/Show')
            ->where('dealership.opportunities.0.activities.0.type', 'call')
            ->where('dealership.opportunities.0.activities.0.details', 'Demo call completed.')
        );
});

it('has correct model relationships', function () {
    $activity = OpportunityActivity::factory()->create([
        'opportunity_id' => $this->opportunity->id,
        'user_id' => $this->user->id,
    ]);

    expect($activity->opportunity->id)->toBe($this->opportunity->id);
    expect($activity->user->id)->toBe($this->user->id);
    expect($this->opportunity->activities()->count())->toBe(1);
});

it('casts type to ActivityType enum', function () {
    $activity = OpportunityActivity::factory()->call()->create([
        'opportunity_id' => $this->opportunity->id,
        'user_id' => $this->user->id,
    ]);

    expect($activity->type)->toBeInstanceOf(ActivityType::class);
    expect($activity->type)->toBe(ActivityType::Call);
    expect($activity->type->label())->toBe('Call');
});
