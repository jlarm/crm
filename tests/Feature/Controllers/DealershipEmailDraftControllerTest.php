<?php

declare(strict_types=1);

use App\Ai\Agents\EmailDrafter;
use App\Models\Contact;
use App\Models\Dealership;
use App\Models\DealershipEmail;
use App\Models\User;
use Laravel\Ai\Prompts\AgentPrompt;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

beforeEach(function () {
    $this->rep = User::factory()->create(['name' => 'Rita Rep']);
    actingAs($this->rep);
    $this->dealership = Dealership::factory()->create(['user_id' => $this->rep->id, 'name' => 'Smith Ford']);
    $this->contact = Contact::factory()->create(['dealership_id' => $this->dealership->id, 'name' => 'Pat Smith', 'position' => 'GM']);
    $this->sent = DealershipEmail::factory()->create([
        'dealership_id' => $this->dealership->id,
        'contact_id' => $this->contact->id,
        'user_id' => $this->rep->id,
        'subject' => 'Renewal options',
        'body_text' => 'Hi Pat, here are the two renewal options we talked about.',
        'created_at' => now()->subDay(),
    ]);
    $this->reply = DealershipEmail::factory()->inbound()->create([
        'dealership_id' => $this->dealership->id,
        'contact_id' => $this->contact->id,
        'parent_id' => $this->sent->id,
        'from_name' => 'Pat Smith',
        'subject' => 'Re: Renewal options',
        'body_text' => 'Thanks. Can we talk Tuesday about option B?',
    ]);
});

it('drafts a reply from the thread history', function () {
    EmailDrafter::fake([['subject' => 'Ignored', 'body' => "Hi Pat,\n\nTuesday works. I'll call at 10 to go over option B.\n\nThanks,\nRita"]]);

    postJson(route('dealerships.emails.draft', $this->dealership), ['parent_id' => $this->reply->id])
        ->assertOk()
        ->assertJson([
            'subject' => 'Re: Renewal options',
            'bodyHtml' => '<p>Hi Pat,</p><p>Tuesday works. I&#039;ll call at 10 to go over option B.</p><p>Thanks,<br>Rita</p>',
        ]);

    EmailDrafter::assertPrompted(fn (AgentPrompt $prompt): bool => $prompt->contains('here are the two renewal options')
        && $prompt->contains('Can we talk Tuesday about option B?')
        && $prompt->contains('Pat Smith (contact)')
        && $prompt->contains('Writing to: Pat Smith, GM')
        && $prompt->contains("Write the rep's reply to the latest message."));
});

it('passes the rep notes along', function () {
    EmailDrafter::fake([['subject' => 'x', 'body' => "Hi Pat,\n\nThursday is better for me.\n\nThanks,\nRita"]]);

    postJson(route('dealerships.emails.draft', $this->dealership), ['parent_id' => $this->reply->id, 'notes' => 'Offer Thursday instead'])
        ->assertOk();

    EmailDrafter::assertPrompted(fn (AgentPrompt $prompt): bool => $prompt->contains('Offer Thursday instead'));
});

it('sends a sloppy draft back for one revision', function () {
    EmailDrafter::fake([
        ['subject' => 'x', 'body' => "Hi Pat,\n\nI hope this email finds you well — we can leverage option B.\n\nThanks,\nRita"],
        ['subject' => 'x', 'body' => "Hi Pat,\n\nOption B fits what you described. Does Tuesday at 10 work?\n\nThanks,\nRita"],
    ]);

    postJson(route('dealerships.emails.draft', $this->dealership), ['parent_id' => $this->reply->id])
        ->assertOk()
        ->assertJsonPath('body', "Hi Pat,\n\nOption B fits what you described. Does Tuesday at 10 work?\n\nThanks,\nRita");

    EmailDrafter::assertPrompted(fn (AgentPrompt $prompt): bool => $prompt->contains('Revise it')
        && $prompt->contains('i hope this email finds you')
        && $prompt->contains('"leverage"')
        && $prompt->contains('dashes'));
});

it('writes a new email from the rep notes, keeping the AI subject', function () {
    EmailDrafter::fake([['subject' => 'Cyber risk review for Smith Ford', 'body' => "Hi Pat,\n\nCould we set up 20 minutes next week?\n\nThanks,\nRita"]]);

    postJson(route('dealerships.emails.draft', $this->dealership), ['contact_id' => $this->contact->id, 'notes' => 'Ask for 20 minutes about a cyber review'])
        ->assertOk()
        ->assertJsonPath('subject', 'Cyber risk review for Smith Ford');

    EmailDrafter::assertPrompted(fn (AgentPrompt $prompt): bool => $prompt->contains('This is a new email, not a reply.'));
});

it('needs notes for a new email', function () {
    EmailDrafter::fake()->preventStrayPrompts();

    postJson(route('dealerships.emails.draft', $this->dealership), ['contact_id' => $this->contact->id])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['notes' => 'Tell the AI what the email should say.']);
});

it('rejects a thread from another dealership', function () {
    EmailDrafter::fake()->preventStrayPrompts();

    postJson(route('dealerships.emails.draft', $this->dealership), ['parent_id' => DealershipEmail::factory()->create()->id])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('parent_id');
});

it('returns a friendly 503 when drafting fails', function () {
    EmailDrafter::fake(fn () => throw new RuntimeException('Overloaded'));

    postJson(route('dealerships.emails.draft', $this->dealership), ['parent_id' => $this->reply->id])
        ->assertStatus(503)
        ->assertJsonPath('message', "Couldn't write a draft right now. Try again, or write the email yourself.");
});

it('splits a draft that uses single line breaks into paragraphs', function () {
    EmailDrafter::fake([['subject' => 'x', 'body' => "Hi Pat,\nTuesday works for me.\nWhat time is good?\nThanks,\nRita"]]);

    postJson(route('dealerships.emails.draft', $this->dealership), ['parent_id' => $this->reply->id])
        ->assertOk()
        ->assertJsonPath('bodyHtml', '<p>Hi Pat,</p><p>Tuesday works for me.</p><p>What time is good?</p><p>Thanks,<br>Rita</p>');
});
