<?php

declare(strict_types=1);

use App\Ai\Agents\CallNoteAnalyst;
use App\Enum\OpportunityStage;
use App\Enum\TaskType;
use App\Models\Contact;
use App\Models\Dealership;
use App\Models\Opportunity;
use App\Models\OpportunityActivity;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Laravel\Ai\Prompts\AgentPrompt;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->dealership = Dealership::factory()->create(['user_id' => $this->user->id, 'rating' => 'warm']);
    $this->contact = Contact::factory()->create(['dealership_id' => $this->dealership->id, 'name' => 'Dana Price', 'email' => 'dana@example.com']);
    $this->opportunity = Opportunity::factory()->create([
        'dealership_id' => $this->dealership->id,
        'stage' => OpportunityStage::Demo->value,
    ]);
    $this->actingAs($this->user);
});

function analyzeUrl(Dealership $dealership, Opportunity $opportunity): string
{
    return "/dealerships/{$dealership->id}/opportunities/{$opportunity->id}/activities/analyze";
}

function activityUrl(Dealership $dealership, Opportunity $opportunity): string
{
    return "/dealerships/{$dealership->id}/opportunities/{$opportunity->id}/activities";
}

describe('analyzing call notes', function () {
    it('returns cleaned-up suggestions without saving anything', function () {
        $otherContact = Contact::factory()->create();

        CallNoteAnalyst::fake([[
            'summary' => '- GM wants pricing for 3 rooftops',
            'tasks' => [
                ['title' => 'Send proposal', 'type' => 'proposal', 'priority' => 'high', 'due_date' => '2026-10-14', 'contact_id' => $this->contact->id],
                ['title' => 'Call back', 'type' => 'call', 'priority' => 'urgent', 'due_date' => 'next week', 'contact_id' => $otherContact->id],
                ['title' => 'Bogus', 'type' => 'carrier_pigeon', 'priority' => 'low', 'due_date' => '', 'contact_id' => 0],
            ],
            'stage' => 'proposal',
            'stage_reason' => 'They asked for a proposal.',
            'rating' => 'hot',
            'rating_reason' => 'Budget approved.',
            'new_contacts' => [
                ['name' => 'Sam Lee', 'position' => 'CFO', 'email' => 'not-an-email', 'phone' => ''],
                ['name' => 'dana price', 'position' => '', 'email' => '', 'phone' => ''],
            ],
        ]]);

        $this->postJson(analyzeUrl($this->dealership, $this->opportunity), ['notes' => 'Met with the GM, they want pricing for all three rooftops.'])
            ->assertOk()
            ->assertExactJson([
                'summary' => '- GM wants pricing for 3 rooftops',
                'tasks' => [
                    ['title' => 'Send proposal', 'type' => 'proposal', 'priority' => 'high', 'dueDate' => '2026-10-14', 'contactId' => $this->contact->id],
                    ['title' => 'Call back', 'type' => 'call', 'priority' => 'medium', 'dueDate' => null, 'contactId' => null],
                ],
                'stage' => ['value' => 'proposal', 'label' => 'Proposal', 'reason' => 'They asked for a proposal.'],
                'rating' => ['value' => 'hot', 'label' => 'Hot', 'reason' => 'Budget approved.'],
                'newContacts' => [
                    ['name' => 'Sam Lee', 'position' => 'CFO', 'email' => '', 'phone' => ''],
                ],
            ]);

        CallNoteAnalyst::assertPrompted(fn (AgentPrompt $prompt) => $prompt->contains('three rooftops'));
        expect(Task::count())->toBe(0)
            ->and(OpportunityActivity::count())->toBe(0);
    });

    it('omits stage and rating suggestions that match the current values', function () {
        CallNoteAnalyst::fake([[
            'summary' => '- Check-in call',
            'tasks' => [],
            'stage' => 'demo',
            'stage_reason' => '',
            'rating' => 'warm',
            'rating_reason' => '',
            'new_contacts' => [],
        ]]);

        $this->postJson(analyzeUrl($this->dealership, $this->opportunity), ['notes' => 'Quick check-in call, nothing new to report.'])
            ->assertOk()
            ->assertJsonPath('stage', null)
            ->assertJsonPath('rating', null);
    });

    it('rejects notes that are too short to analyze', function () {
        CallNoteAnalyst::fake();

        $this->postJson(analyzeUrl($this->dealership, $this->opportunity), ['notes' => 'hi'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('notes');

        CallNoteAnalyst::assertNeverPrompted();
    });

    it('returns a friendly error when the AI call fails', function () {
        CallNoteAnalyst::fake(fn () => throw new RuntimeException('Provider down'));

        $this->postJson(analyzeUrl($this->dealership, $this->opportunity), ['notes' => 'Met with the GM about pricing for next quarter.'])
            ->assertStatus(503)
            ->assertJsonPath('message', 'The AI could not analyze these notes right now. You can still log the activity.');
    });

    it('404s when the deal belongs to a different dealership', function () {
        CallNoteAnalyst::fake();
        $otherDealership = Dealership::factory()->create(['user_id' => $this->user->id]);

        $this->postJson(analyzeUrl($otherDealership, $this->opportunity), ['notes' => 'Met with the GM about pricing for next quarter.'])
            ->assertNotFound();
    });
});

describe('logging an activity with approved suggestions', function () {
    it('saves the activity and every approved suggestion together', function () {
        $this->post(activityUrl($this->dealership, $this->opportunity), [
            'type' => 'call',
            'details' => 'Raw notes from the call.',
            'apply' => [
                'summary' => '- Wants a proposal',
                'stage' => 'proposal',
                'rating' => 'hot',
                'tasks' => [
                    ['title' => 'Send proposal', 'type' => 'proposal', 'priority' => 'high', 'due_date' => '2026-10-14', 'contact_id' => $this->contact->id],
                ],
                'contacts' => [
                    ['name' => 'Sam Lee', 'position' => 'CFO', 'email' => 'sam@example.com', 'phone' => ''],
                ],
            ],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $activity = OpportunityActivity::sole();
        $task = Task::sole();

        expect($activity->details)->toBe("Summary:\n- Wants a proposal\n\nNotes:\nRaw notes from the call.")
            ->and($task->title)->toBe('Send proposal')
            ->and($task->type)->toBe(TaskType::Proposal)
            ->and($task->user_id)->toBe($this->user->id)
            ->and($task->opportunity_id)->toBe($this->opportunity->id)
            ->and($task->contact_id)->toBe($this->contact->id)
            ->and($this->opportunity->fresh()->stage)->toBe(OpportunityStage::Proposal)
            ->and($this->dealership->fresh()->rating)->toBe('hot')
            ->and(Contact::where('name', 'Sam Lee')->where('dealership_id', $this->dealership->id)->exists())->toBeTrue();
    });

    it('logs a plain activity when no suggestions are approved', function () {
        $this->post(activityUrl($this->dealership, $this->opportunity), [
            'type' => 'note',
            'details' => 'Left a voicemail.',
        ])->assertRedirect();

        expect(OpportunityActivity::sole()->details)->toBe('Left a voicemail.')
            ->and(Task::count())->toBe(0)
            ->and($this->opportunity->fresh()->stage)->toBe(OpportunityStage::Demo);
    });

    it('rejects a task tied to another dealership\'s contact and saves nothing', function () {
        $otherContact = Contact::factory()->create();

        $this->post(activityUrl($this->dealership, $this->opportunity), [
            'type' => 'call',
            'details' => 'Raw notes.',
            'apply' => [
                'tasks' => [
                    ['title' => 'Call', 'type' => 'call', 'priority' => 'low', 'contact_id' => $otherContact->id],
                ],
            ],
        ])->assertSessionHasErrors('apply.tasks.0.contact_id');

        expect(OpportunityActivity::count())->toBe(0)
            ->and(Task::count())->toBe(0);
    });
});

describe('Anthropic request', function () {
    it('asks Claude for structured output without temperature or a forced tool call', function () {
        config(['ai.providers.anthropic.key' => 'test-key']);

        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'id' => 'msg_test',
                'type' => 'message',
                'role' => 'assistant',
                'model' => 'claude-opus-5-5',
                'content' => [[
                    'type' => 'text',
                    'text' => json_encode([
                        'summary' => '- Wants pricing',
                        'tasks' => [],
                        'stage' => 'demo',
                        'stage_reason' => '',
                        'rating' => 'warm',
                        'rating_reason' => '',
                        'new_contacts' => [],
                    ]),
                ]],
                'stop_reason' => 'end_turn',
                'usage' => ['input_tokens' => 10, 'output_tokens' => 10],
            ]),
        ]);

        $this->postJson(analyzeUrl($this->dealership, $this->opportunity), ['notes' => 'Met with the GM, they want pricing for all three rooftops.'])
            ->assertOk()
            ->assertJsonPath('summary', '- Wants pricing');

        Http::assertSent(function (Request $request): bool {
            $body = $request->data();

            return $request->url() === 'https://api.anthropic.com/v1/messages'
                && $body['model'] === 'claude-opus-5-5'
                && ($body['output_config']['format']['type'] ?? null) === 'json_schema'
                && ! array_key_exists('temperature', $body)
                && ! array_key_exists('tool_choice', $body);
        });
    });
});
