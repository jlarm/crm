<?php

declare(strict_types=1);

use App\Ai\Agents\DealershipAssistant;
use App\Models\Dealership;
use App\Models\Progress;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('rejects an empty message', function () {
    $this->postJson('/ai/chat', ['message' => ''])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('message');
});

it('starts a new conversation when no id is provided', function () {
    DealershipAssistant::fake(['Hello from the assistant.']);

    $response = $this->postJson('/ai/chat', [
        'message' => 'Hi there',
    ])->assertOk()
        ->assertJsonStructure(['conversation_id', 'reply'])
        ->assertJson(['reply' => 'Hello from the assistant.']);

    expect($response->json('conversation_id'))->toBeString();

    DealershipAssistant::assertPrompted('Hi there');
});

it('continues an existing conversation when an id is provided', function () {
    DealershipAssistant::fake();

    $first = $this->postJson('/ai/chat', ['message' => 'Hi'])->assertOk();
    $conversationId = $first->json('conversation_id');

    $this->postJson('/ai/chat', [
        'message' => 'Tell me more',
        'conversation_id' => $conversationId,
    ])->assertOk()
        ->assertJson(['conversation_id' => $conversationId]);

    DealershipAssistant::assertPrompted('Tell me more');
});

it('builds dealership context into the agent instructions', function () {
    $dealership = Dealership::factory()->create([
        'name' => 'Prime Motors',
        'type' => 'Automotive',
        'city' => 'Detroit',
        'state' => 'MI',
        'rating' => 'Hot',
        'status' => 'Active',
    ]);

    Progress::factory()->create([
        'dealership_id' => $dealership->id,
        'user_id' => $this->user->id,
        'details' => 'Called the GM about renewal',
        'date' => now()->toDateString(),
    ]);

    $instructions = (new DealershipAssistant($dealership))->instructions();

    expect($instructions)
        ->toContain('Prime Motors')
        ->toContain('Detroit, MI')
        ->toContain('Hot')
        ->toContain('Called the GM about renewal');
});

it('falls back to a generic prompt when no dealership is set', function () {
    $instructions = (new DealershipAssistant)->instructions();

    expect($instructions)->toContain('has not selected a specific dealership');
});
