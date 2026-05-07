<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use App\Models\Dealership;
use App\Models\Opportunity;
use App\Models\Progress;
use App\Models\Task;
use App\Models\User;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Promptable;

#[Temperature(0.3)]
#[Timeout(90)]
class DealershipAssistant implements Agent, Conversational
{
    use Promptable, RemembersConversations;

    public function __construct(
        public ?Dealership $dealership = null,
        public ?User $user = null,
    ) {}

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

        $sections = [$base, $this->companyContext()];

        $sections[] = $this->dealership
            ? $this->dealershipContext()
            : $this->userDealershipsContext();

        return implode("\n\n", $sections);
    }

    protected function companyContext(): string
    {
        $company = config('company');

        if (! is_array($company) || empty($company['name'])) {
            return '';
        }

        $get = fn (string $key): string => is_string($company[$key] ?? null) ? $company[$key] : '';

        $valueProps = collect((array) ($company['value_props'] ?? []))
            ->map(fn ($line): string => '- '.(is_string($line) ? $line : ''))
            ->implode("\n");

        $regs = collect((array) ($company['regulatory_coverage'] ?? []))->implode(', ');

        $guidelines = collect((array) ($company['email_guidelines'] ?? []))
            ->map(fn ($line): string => '- '.(is_string($line) ? $line : ''))
            ->implode("\n");

        $name = $get('name');
        $shortName = $get('short_name');

        $sections = [
            'About the company you work for:',
            '- Name: '.$name.(
                $shortName !== '' && ! str_contains($name, $shortName)
                    ? " ({$shortName})"
                    : ''
            ),
            $get('website') !== '' ? '- Website: '.$get('website') : null,
            $get('phone') !== '' ? '- Phone: '.$get('phone') : null,
            $get('tagline') !== '' ? '- What we do: '.$get('tagline') : null,
            $get('positioning') !== '' ? '- Positioning: '.$get('positioning') : null,
            $get('offering') !== '' ? '- Offering: '.$get('offering') : null,
            $get('audience') !== '' ? '- Who we sell to: '.$get('audience') : null,
            $get('history') !== '' ? '- History: '.$get('history') : null,
            $get('mission') !== '' ? '- Mission: '.$get('mission') : null,
            $regs !== '' ? "- Regulatory areas covered: {$regs}" : null,
        ];

        $body = collect($sections)->filter()->implode("\n");

        if ($valueProps !== '') {
            $body .= "\n\nKey value propositions:\n{$valueProps}";
        }

        /** @var array<string, array{name?: string, description?: string, capabilities?: array<int, string>}> $productsRaw */
        $productsRaw = is_array($company['products'] ?? null) ? $company['products'] : [];
        $products = $this->formatProducts($productsRaw);
        if ($products !== '') {
            $body .= "\n\nProducts:\n{$products}";
        }

        /** @var array<int, array{name?: string, fit?: string, includes?: array<int, string>}> $packagesRaw */
        $packagesRaw = is_array($company['packages'] ?? null) ? $company['packages'] : [];
        $packages = $this->formatPackages($packagesRaw);
        if ($packages !== '') {
            $body .= "\n\nPackages we sell:\n{$packages}";
        }

        if ($guidelines !== '') {
            $body .= "\n\nWhen drafting emails or outreach on behalf of the rep, follow these rules:\n{$guidelines}";
        }

        return $body;
    }

    /**
     * @param  array<string, array{name?: string, description?: string, capabilities?: array<int, string>}>  $products
     */
    protected function formatProducts(array $products): string
    {
        return collect($products)
            ->filter(fn (array $p) => ! empty($p['name']))
            ->map(function (array $p): string {
                $block = "- {$p['name']}";
                if (! empty($p['description'])) {
                    $block .= ": {$p['description']}";
                }
                if (! empty($p['capabilities'])) {
                    $caps = collect($p['capabilities'])
                        ->map(fn (string $c): string => "    • {$c}")
                        ->implode("\n");
                    $block .= "\n{$caps}";
                }

                return $block;
            })
            ->implode("\n");
    }

    /**
     * @param  array<int, array{name?: string, fit?: string, includes?: array<int, string>}>  $packages
     */
    protected function formatPackages(array $packages): string
    {
        return collect($packages)
            ->filter(fn (array $p) => ! empty($p['name']))
            ->map(function (array $p): string {
                $block = "- {$p['name']}";
                if (! empty($p['fit'])) {
                    $block .= " — fit: {$p['fit']}";
                }
                if (! empty($p['includes'])) {
                    $items = collect($p['includes'])
                        ->map(fn (string $i): string => "    • {$i}")
                        ->implode("\n");
                    $block .= "\n{$items}";
                }

                return $block;
            })
            ->implode("\n");
    }

    protected function userDealershipsContext(): string
    {
        if (! $this->user) {
            return 'The user has not selected a specific dealership. Answer general CRM questions or ask which dealership they want to discuss.';
        }

        $dealerships = Dealership::query()
            ->forUser($this->user)
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'city', 'state', 'type', 'rating']);

        if ($dealerships->isEmpty()) {
            return 'The user has no active dealerships assigned to them. Ask which dealership they want to discuss, or suggest they check their assignments.';
        }

        $lines = $dealerships->map(fn (Dealership $d) => sprintf(
            '- #%d %s — %s, %s | %s | rating: %s',
            $d->id,
            $d->name,
            $d->city ?: '—',
            $d->state ?: '—',
            $d->type ?: '—',
            $d->rating ?: '—',
        ))->implode("\n");

        $count = $dealerships->count();

        return <<<TXT
The user has not selected a specific dealership. Only reference the active dealerships assigned to them, listed below. You do not have visibility into any other dealerships in the system.

Active dealerships assigned to this user ({$count}):
{$lines}

If the user asks about a dealership not in this list, say it isn't in their active assignments and ask them to open it to load full context.
TXT;
    }

    protected function dealershipContext(): string
    {
        if ($this->dealership === null) {
            return '';
        }

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

        $progressLines = $d->progresses->map(fn (Progress $p) => sprintf(
            '- %s | %s | %s: %s',
            $p->date?->format('Y-m-d') ?? '—',
            $p->user?->name ?? 'Unknown', // @phpstan-ignore nullsafe.neverNull
            $p->category?->name ?? 'Note', // @phpstan-ignore nullsafe.neverNull
            str((string) $p->details)->limit(280)->value(),
        ))->implode("\n") ?: '- (no recent activity)';

        $oppLines = $d->opportunities->map(fn (Opportunity $o) => sprintf(
            '- #%d %s — stage: %s%s',
            $o->id,
            $o->name,
            $o->stage->value,
            $o->expected_close_date ? ' (close '.$o->expected_close_date->format('Y-m-d').')' : '',
        ))->implode("\n") ?: '- (no opportunities)';

        $taskLines = $d->tasks->map(fn (Task $t) => sprintf(
            '- [%s] %s | %s priority | %s | assigned: %s%s',
            $t->completed_at ? 'x' : ' ',
            $t->title,
            $t->priority->value,
            $t->due_date ? 'due '.$t->due_date->format('Y-m-d') : 'no due date',
            $t->user->name,
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
