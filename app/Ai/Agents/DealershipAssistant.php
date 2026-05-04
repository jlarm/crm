<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use App\Models\Dealership;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;

#[Provider(Lab::Anthropic)]
#[Model('claude-sonnet-4-6')]
#[Temperature(0.3)]
#[Timeout(90)]
class DealershipAssistant implements Agent, Conversational
{
    use Promptable, RemembersConversations;

    public function __construct(public ?Dealership $dealership = null) {}

    public function instructions(): string
    {
        $base = <<<'TXT'
You are a CRM assistant for sales reps managing dealerships across automotive, RV, motorsports, maritime, and association markets.

Style:
- Be concise and action-oriented. No filler.
- Prefer bullet points and short paragraphs over long prose.
- When summarising activity, surface what changed, who acted, and what's next.
- When asked what to do next, suggest a specific action grounded in the data.
- If you don't have enough information, say so plainly and ask one focused question.
TXT;

        if (! $this->dealership) {
            return $base."\n\nThe user has not selected a specific dealership. Answer general CRM questions or ask which dealership they want to discuss.";
        }

        return $base."\n\n".$this->dealershipContext();
    }

    protected function dealershipContext(): string
    {
        $d = $this->dealership->loadMissing([
            'progresses' => fn ($q) => $q->with(['user:id,name', 'category:id,name'])->latest('date')->limit(20),
            'opportunities' => fn ($q) => $q->latest()->limit(10),
            'tasks' => fn ($q) => $q->with('user:id,name')
                ->orderByRaw('completed_at IS NULL DESC')
                ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")
                ->orderBy('due_date')
                ->limit(20),
            'contacts:id,dealership_id,name,email,phone,position',
            'users:id,name',
        ]);

        $progressLines = $d->progresses->map(fn ($p) => sprintf(
            '- %s | %s | %s: %s',
            optional($p->date)->format('Y-m-d') ?? '—',
            $p->user?->name ?? 'Unknown',
            $p->category?->name ?? 'Note',
            str($p->details)->limit(280)->value(),
        ))->implode("\n") ?: '- (no recent activity)';

        $oppLines = $d->opportunities->map(fn ($o) => sprintf(
            '- #%d %s — stage: %s%s',
            $o->id,
            $o->name ?? 'Opportunity',
            $o->stage?->value ?? '—',
            $o->expected_close_date ? ' (close '.$o->expected_close_date->format('Y-m-d').')' : '',
        ))->implode("\n") ?: '- (no opportunities)';

        $taskLines = $d->tasks->map(fn ($t) => sprintf(
            '- [%s] %s | %s priority | %s | assigned: %s%s',
            $t->completed_at ? 'x' : ' ',
            $t->title,
            $t->priority?->value ?? '—',
            $t->due_date ? 'due '.$t->due_date->format('Y-m-d') : 'no due date',
            $t->user?->name ?? 'unassigned',
            $t->description ? ' — '.str($t->description)->limit(160)->value() : '',
        ))->implode("\n") ?: '- (no tasks)';

        $assigned = $d->users->pluck('name')->implode(', ') ?: '(unassigned)';

        return <<<TXT
Current dealership context:
- Name: {$d->name}
- Type: {$d->type}
- Location: {$d->city}, {$d->state}
- Status: {$d->status} | Rating: {$d->rating}
- Assigned reps: {$assigned}
- Contacts on file: {$d->contacts->count()}

Recent activity (most recent first, up to 20):
{$progressLines}

Open opportunities (up to 10):
{$oppLines}

Tasks (open first, up to 20; "[x]" = completed):
{$taskLines}

Use this context when answering. If the user asks something the context can't answer, say so.
TXT;
    }
}
