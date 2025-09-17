<?php

declare(strict_types=1);

use App\Models\DealerEmailTemplate;
use App\Models\Dealership;
use App\Models\User;
use App\Services\ClaudeEmailGeneratorService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    // Mock successful Claude API response
    $this->mockSuccessfulResponse = [
        'content' => [
            ['text' => 'Generated content from Claude'],
        ],
    ];
});

it('can check if claude is configured', function () {
    config(['services.claude.api_key' => null]);
    $service = new ClaudeEmailGeneratorService;
    expect($service->isConfigured())->toBeFalse();

    config(['services.claude.api_key' => 'test-key']);
    $service = new ClaudeEmailGeneratorService;
    expect($service->isConfigured())->toBeTrue();
});

it('generates email subject for dealership', function () {
    config(['services.claude.api_key' => 'test-key']);

    Http::fake([
        'api.anthropic.com/*' => Http::response($this->mockSuccessfulResponse, 200),
    ]);

    $dealership = Dealership::factory()->create([
        'name' => 'Test Motors',
        'type' => 'Automotive',
        'city' => 'Detroit',
        'state' => 'MI',
        'rating' => 'Hot',
    ]);

    $service = new ClaudeEmailGeneratorService;
    $subject = $service->generateEmailSubject($dealership);

    expect($subject)->toBe('Generated content from Claude');

    Http::assertSent(function ($request) {
        return str_contains($request->body(), 'Test Motors') &&
               str_contains($request->body(), 'Automotive') &&
               str_contains($request->body(), 'subject line');
    });
});

it('generates email content for dealership with template', function () {
    config(['services.claude.api_key' => 'test-key']);

    Http::fake([
        'api.anthropic.com/*' => Http::response($this->mockSuccessfulResponse, 200),
    ]);

    $dealership = Dealership::factory()->create([
        'name' => 'RV World',
        'type' => 'RV',
        'current_solution_name' => 'Legacy CRM',
        'notes' => 'Interested in upgrading their system',
    ]);

    $template = DealerEmailTemplate::factory()->create([
        'name' => 'Introduction Email',
        'subject' => 'Partnership Opportunity',
        'body' => 'Hello {{contact_name}}, we have solutions for you.',
    ]);

    $service = new ClaudeEmailGeneratorService;
    $content = $service->generateEmailContent($dealership, $template);

    expect($content)->toBe('Generated content from Claude');

    Http::assertSent(function ($request) {
        return str_contains($request->body(), 'RV World') &&
               str_contains($request->body(), 'Legacy CRM') &&
               str_contains($request->body(), 'Base template:');
    });
});

it('handles claude api errors gracefully', function () {
    config(['services.claude.api_key' => 'test-key']);

    Http::fake([
        'api.anthropic.com/*' => Http::response(['error' => 'API Error'], 500),
    ]);

    $dealership = Dealership::factory()->create();

    $service = new ClaudeEmailGeneratorService;
    $subject = $service->generateEmailSubject($dealership);

    expect($subject)->toBe('Follow-up: '.$dealership->name);
});

it('generates email subject with context', function () {
    config(['services.claude.api_key' => 'test-key']);

    Http::fake([
        'api.anthropic.com/*' => Http::response($this->mockSuccessfulResponse, 200),
    ]);

    $service = new ClaudeEmailGeneratorService;
    $subject = $service->generateEmailSubjectWithContext('Product demo invitation');

    expect($subject)->toBe('Generated content from Claude');

    Http::assertSent(function ($request) {
        return str_contains($request->body(), 'Product demo invitation') &&
               str_contains($request->body(), 'subject line');
    });
});

it('generates email content with context and tone', function () {
    config(['services.claude.api_key' => 'test-key']);

    Http::fake([
        'api.anthropic.com/*' => Http::response($this->mockSuccessfulResponse, 200),
    ]);

    $service = new ClaudeEmailGeneratorService;
    $content = $service->generateEmailContentWithContext('Pricing discussion', 'friendly');

    expect($content)->toBe('Generated content from Claude');

    Http::assertSent(function ($request) {
        return str_contains($request->body(), 'Pricing discussion') &&
               str_contains($request->body(), 'Warm and approachable yet professional');
    });
});

it('generates dealership-specific content with context', function () {
    config(['services.claude.api_key' => 'test-key']);

    Http::fake([
        'api.anthropic.com/*' => Http::response($this->mockSuccessfulResponse, 200),
    ]);

    $dealership = Dealership::factory()->create([
        'name' => 'Prime Motors',
        'type' => 'Automotive',
    ]);

    $service = new ClaudeEmailGeneratorService;
    $content = $service->generateEmailContentWithDealershipContext(
        $dealership,
        'Follow-up from trade show conversation',
        'consultative',
        true
    );

    expect($content)->toBe('Generated content from Claude');

    Http::assertSent(function ($request) {
        return str_contains($request->body(), 'Prime Motors') &&
               str_contains($request->body(), 'Follow-up from trade show conversation') &&
               str_contains($request->body(), 'Advisory and solution-focused') &&
               str_contains($request->body(), 'Include a specific, relevant call to action');
    });
});
