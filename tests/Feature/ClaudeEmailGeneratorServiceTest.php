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

it('uses every supported tone description', function () {
    config(['services.claude.api_key' => 'test-key']);

    Http::fake([
        'api.anthropic.com/*' => Http::response($this->mockSuccessfulResponse, 200),
    ]);

    $tones = [
        'professional' => 'Professional and business-focused',
        'formal' => 'Formal and traditional business',
        'casual' => 'Conversational and relaxed',
        'consultative' => 'Advisory and solution-focused',
    ];

    foreach ($tones as $key => $expected) {
        $service = new ClaudeEmailGeneratorService;
        $service->generateEmailContentWithContext('Topic', $key);

        Http::assertSent(fn ($request) => str_contains($request->body(), $expected));
    }
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

it('generates email content with template fallback when api fails', function () {
    config(['services.claude.api_key' => 'test-key']);

    Http::fake([
        'api.anthropic.com/*' => Http::response(['error' => 'fail'], 500),
    ]);

    $dealership = Dealership::factory()->create();
    $template = DealerEmailTemplate::factory()->create([
        'body' => 'Fallback template body',
    ]);

    $service = new ClaudeEmailGeneratorService;
    $content = $service->generateEmailContent($dealership, $template);

    expect($content)->toBe('Fallback template body');
});

it('returns hardcoded default content when api fails and no template provided', function () {
    config(['services.claude.api_key' => 'test-key']);

    Http::fake([
        'api.anthropic.com/*' => Http::response(['error' => 'fail'], 500),
    ]);

    $dealership = Dealership::factory()->create();

    $service = new ClaudeEmailGeneratorService;
    $content = $service->generateEmailContent($dealership);

    expect($content)->toBe('Thank you for your time. Looking forward to connecting soon.');
});

it('generates a personalized message with the api response', function () {
    config(['services.claude.api_key' => 'test-key']);

    Http::fake([
        'api.anthropic.com/*' => Http::response($this->mockSuccessfulResponse, 200),
    ]);

    $dealership = Dealership::factory()->create([
        'name' => 'Acme Auto',
        'type' => 'Automotive',
    ]);

    $service = new ClaudeEmailGeneratorService;
    $message = $service->generatePersonalizedMessage($dealership, 'opening greeting');

    expect($message)->toBe('Generated content from Claude');

    Http::assertSent(fn ($request) => str_contains($request->body(), 'opening greeting')
        && str_contains($request->body(), 'Acme Auto'));
});

it('falls back to a default personalized message when the api fails', function () {
    config(['services.claude.api_key' => 'test-key']);

    Http::fake([
        'api.anthropic.com/*' => Http::response(['error' => 'fail'], 500),
    ]);

    $dealership = Dealership::factory()->create([
        'name' => 'Acme Auto',
        'type' => 'Automotive',
    ]);

    $service = new ClaudeEmailGeneratorService;
    $fallback = $service->generatePersonalizedMessage($dealership);

    expect($fallback)->toBe('Hi there! I wanted to reach out regarding your Automotive dealership.');
});

it('generates follow up suggestions parsing a numbered list', function () {
    config(['services.claude.api_key' => 'test-key']);

    Http::fake([
        'api.anthropic.com/*' => Http::response([
            'content' => [
                ['text' => "1. Schedule a follow-up call\n2. Send pricing\n3. Invite to demo\n4. Share case study\n5. Connect on LinkedIn\n6. Beyond the cap"],
            ],
        ], 200),
    ]);

    $dealership = Dealership::factory()->create();

    $service = new ClaudeEmailGeneratorService;
    $suggestions = $service->generateFollowUpSuggestions($dealership);

    expect($suggestions)->toHaveCount(5)
        ->and($suggestions[0])->toContain('Schedule a follow-up call');
});

it('returns default follow up suggestions when api fails', function () {
    config(['services.claude.api_key' => 'test-key']);

    Http::fake([
        'api.anthropic.com/*' => Http::response(['error' => 'fail'], 500),
    ]);

    $dealership = Dealership::factory()->create();

    $service = new ClaudeEmailGeneratorService;
    $suggestions = $service->generateFollowUpSuggestions($dealership);

    expect($suggestions)->toBe([
        'Schedule a follow-up call',
        'Send product information',
        'Invite to demo session',
    ]);
});

it('falls back to a context subject when the api fails', function () {
    config(['services.claude.api_key' => 'test-key']);

    Http::fake([
        'api.anthropic.com/*' => Http::response(['error' => 'fail'], 500),
    ]);

    $service = new ClaudeEmailGeneratorService;
    $subject = $service->generateEmailSubjectWithContext('product demo invitation discussion');

    expect($subject)->toStartWith('Partnership Opportunity - ');
});

it('falls back to default content templates when context-only api fails', function () {
    config(['services.claude.api_key' => 'test-key']);

    Http::fake([
        'api.anthropic.com/*' => Http::response(['error' => 'fail'], 500),
    ]);

    $service = new ClaudeEmailGeneratorService;
    $content = $service->generateEmailContentWithContext('pricing discussion', 'casual');

    expect($content)->toContain('pricing discussion')
        ->and($content)->toContain('{{contact_name}}');
});

it('falls back to a dealership context subject when api fails', function () {
    config(['services.claude.api_key' => 'test-key']);

    Http::fake([
        'api.anthropic.com/*' => Http::response(['error' => 'fail'], 500),
    ]);

    $dealership = Dealership::factory()->create([
        'name' => 'Lakeside Marine',
        'type' => 'Maritime',
    ]);

    $service = new ClaudeEmailGeneratorService;
    $subject = $service->generateEmailSubjectWithDealershipContext(
        $dealership,
        'inventory question regarding spring stock',
    );

    expect($subject)->toStartWith('Re: Lakeside Marine - ');
});

it('falls back to default personalized content when dealership context api fails', function () {
    config(['services.claude.api_key' => 'test-key']);

    Http::fake([
        'api.anthropic.com/*' => Http::response(['error' => 'fail'], 500),
    ]);

    $dealership = Dealership::factory()->create([
        'name' => 'Lakeside Marine',
        'type' => 'Maritime',
        'city' => 'Tampa',
        'state' => 'FL',
    ]);

    $service = new ClaudeEmailGeneratorService;
    $content = $service->generateEmailContentWithDealershipContext(
        $dealership,
        'inventory question',
        'formal',
        false,
    );

    expect($content)->toContain('{{contact_name}}');
});

it('handles claude api network exceptions gracefully', function () {
    config(['services.claude.api_key' => 'test-key']);

    Http::fake(function () {
        throw new RuntimeException('connection error');
    });

    $dealership = Dealership::factory()->create();

    $service = new ClaudeEmailGeneratorService;
    $subject = $service->generateEmailSubject($dealership);

    expect($subject)->toBe('Follow-up: '.$dealership->name);
});

it('returns null fallback when claude response has unexpected structure', function () {
    config(['services.claude.api_key' => 'test-key']);

    Http::fake([
        'api.anthropic.com/*' => Http::response(['unexpected' => 'shape'], 200),
    ]);

    $dealership = Dealership::factory()->create();

    $service = new ClaudeEmailGeneratorService;
    $subject = $service->generateEmailSubject($dealership);

    expect($subject)->toBe('Follow-up: '.$dealership->name);
});

it('exposes prompts that include dealership notes, current solution, and dev status', function () {
    config(['services.claude.api_key' => 'test-key']);

    Http::fake([
        'api.anthropic.com/*' => Http::response($this->mockSuccessfulResponse, 200),
    ]);

    $dealership = Dealership::factory()->create([
        'name' => 'Loaded Dealer',
        'notes' => str_repeat('a', 250),
        'current_solution_name' => 'Old CRM',
        'current_solution_use' => 'tracking deals',
        'in_development' => false,
    ]);

    $service = new ClaudeEmailGeneratorService;
    $service->generateEmailSubject($dealership);

    Http::assertSent(function ($request) {
        return str_contains($request->body(), 'Old CRM')
            && str_contains($request->body(), 'tracking deals')
            && str_contains($request->body(), 'Notes:')
            && str_contains($request->body(), 'Not in development');
    });
});
