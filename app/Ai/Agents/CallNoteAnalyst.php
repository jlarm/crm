<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use App\Enum\OpportunityStage;
use App\Enum\TaskPriority;
use App\Enum\TaskType;
use App\Models\Contact;
use App\Models\Opportunity;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;

/**
 * Turns a rep's raw call notes into suggested CRM updates for them to approve.
 *
 * Every field is required and uses an empty value ("" or 0) for "nothing", rather than
 * null, so the schema works with strict structured output on every provider.
 */
#[Timeout(120)]
class CallNoteAnalyst implements Agent, HasStructuredOutput
{
    use Promptable;

    public function __construct(public Opportunity $opportunity) {}

    public function instructions(): string
    {
        $opportunity = $this->opportunity->loadMissing('dealership.contacts');
        $dealership = $opportunity->dealership;

        $contacts = $dealership->contacts
            ->map(fn (Contact $contact): string => sprintf(
                '- id %d: %s%s',
                $contact->id,
                $contact->name,
                $contact->position ? ' ('.$contact->position.')' : '',
            ))
            ->implode("\n") ?: '- (no contacts on file)';

        $stages = implode(', ', array_map(fn (OpportunityStage $stage): string => $stage->value, OpportunityStage::cases()));

        return <<<TXT
You turn a sales rep's notes from a call or meeting with a dealership into CRM updates. The rep reviews every suggestion before anything is saved, so only suggest what the notes actually support. Never invent people, dates, or commitments.

Today is {$this->today()}.

Dealership: {$dealership->name} (current rating: {$this->rating()})
Deal: {$opportunity->name}
Current stage: {$opportunity->stage->value}
Expected close date: {$this->closeDate()}

Existing contacts at this dealership:
{$contacts}

Fill in each field:
- summary: 2 to 4 short bullet lines ("- ...") covering what was discussed, decisions, and objections.
- tasks: concrete follow-ups the rep committed to or clearly needs to do. Use a short imperative title. due_date is YYYY-MM-DD, resolved from phrases like "next Tuesday" against today's date, or "" if no timing was mentioned. contact_id is the id of an existing contact the task is about, or 0.
- stage: the stage the deal should be in after this conversation. One of: {$stages}. Repeat the current stage if nothing changed. stage_reason explains why in one sentence, or "" if unchanged.
- rating: how hot the dealership is now: hot, warm, or cold. Repeat the current rating if nothing in the notes changes it. rating_reason explains why in one sentence, or "" if unchanged.
- new_contacts: people mentioned by name who are not in the existing contacts list. Use "" for any detail not in the notes.
TXT;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'summary' => $schema->string()->required(),
            'tasks' => $schema->array()->items($schema->object(fn (JsonSchema $schema): array => [
                'title' => $schema->string()->required(),
                'type' => $schema->string()->enum(array_column(TaskType::cases(), 'value'))->required(),
                'priority' => $schema->string()->enum(array_column(TaskPriority::cases(), 'value'))->required(),
                'due_date' => $schema->string()->required(),
                'contact_id' => $schema->integer()->required(),
            ]))->required(),
            'stage' => $schema->string()->enum(array_column(OpportunityStage::cases(), 'value'))->required(),
            'stage_reason' => $schema->string()->required(),
            'rating' => $schema->string()->enum(['hot', 'warm', 'cold'])->required(),
            'rating_reason' => $schema->string()->required(),
            'new_contacts' => $schema->array()->items($schema->object(fn (JsonSchema $schema): array => [
                'name' => $schema->string()->required(),
                'position' => $schema->string()->required(),
                'email' => $schema->string()->required(),
                'phone' => $schema->string()->required(),
            ]))->required(),
        ];
    }

    private function today(): string
    {
        return now()->format('l, Y-m-d');
    }

    private function rating(): string
    {
        $rating = $this->opportunity->dealership->rating;

        return $rating ? mb_strtolower($rating) : 'not rated';
    }

    private function closeDate(): string
    {
        return $this->opportunity->expected_close_date?->toDateString() ?? 'not set';
    }
}
