<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Dealership;
use App\Models\Opportunity;
use App\Models\OpportunityActivity;
use App\Models\Progress;
use App\Models\Store;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Throwable;

final class DealershipActivityController extends Controller
{
    public function index(Request $request, Dealership $dealership): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 25);
        $perPage = min(max($perPage, 5), 100);

        $contactIds = Contact::where('dealership_id', $dealership->id)->pluck('id');
        $storeIds = Store::where('dealership_id', $dealership->id)->pluck('id');
        $opportunityIds = Opportunity::where('dealership_id', $dealership->id)->pluck('id');

        $logged = Activity::query()
            ->with('causer:id,name')
            ->where(function (Builder $query) use ($dealership, $contactIds, $storeIds, $opportunityIds): void {
                $query->where(function (Builder $q) use ($dealership): void {
                    $q->where('subject_type', Dealership::class)
                        ->where('subject_id', $dealership->id);
                })
                    ->orWhere(function (Builder $q) use ($contactIds): void {
                        $q->where('subject_type', Contact::class)
                            ->whereIn('subject_id', $contactIds);
                    })
                    ->orWhere(function (Builder $q) use ($storeIds): void {
                        $q->where('subject_type', Store::class)
                            ->whereIn('subject_id', $storeIds);
                    })
                    ->orWhere(function (Builder $q) use ($opportunityIds): void {
                        $q->where('subject_type', Opportunity::class)
                            ->whereIn('subject_id', $opportunityIds);
                    })
                    ->orWhere(function (Builder $q) use ($dealership): void {
                        $q->where('subject_type', Progress::class)
                            ->whereIn('subject_id', Progress::where('dealership_id', $dealership->id)->pluck('id'));
                    });
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(200)
            ->get()
            ->map(fn (Activity $a): ?array => $this->mapLoggedActivity($a));

        $opportunityActivities = OpportunityActivity::query()
            ->with(['user:id,name', 'opportunity:id,name'])
            ->whereIn('opportunity_id', $opportunityIds)
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->limit(200)
            ->get()
            ->map(fn (OpportunityActivity $a): array => [
                'id' => 'opp_act_'.$a->id,
                'category' => 'opportunity_activity',
                'icon' => $a->type->value,
                'title' => sprintf('%s logged on %s', $a->type->label(), $a->opportunity->name ?? 'opportunity'),
                'description' => $a->details,
                'actor' => ['id' => $a->user->id, 'name' => $a->user->name],
                'occurredAt' => ($a->occurred_at ?? $a->created_at)->toIso8601String(),
            ]);

        $items = $logged
            ->merge($opportunityActivities)
            ->filter()
            ->sortByDesc('occurredAt')
            ->values();

        $page = (int) $request->integer('page', 1);
        $page = max($page, 1);

        $total = $items->count();
        $paged = $items->slice(($page - 1) * $perPage, $perPage)->values();

        return response()->json([
            'data' => $paged,
            'meta' => [
                'currentPage' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'hasMore' => ($page * $perPage) < $total,
            ],
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function mapLoggedActivity(Activity $activity): ?array
    {
        $event = $activity->event;
        $subjectClass = $activity->subject_type;

        if (! $subjectClass || ! $event) {
            return null;
        }

        $causer = $activity->causer;
        $properties = $activity->properties ?? collect();
        /** @var array<string, mixed> $attributes */
        $attributes = is_array($properties['attributes'] ?? null) ? $properties['attributes'] : [];
        /** @var array<string, mixed> $old */
        $old = is_array($properties['old'] ?? null) ? $properties['old'] : [];

        $subjectLabel = match ($subjectClass) {
            Dealership::class => 'Dealership',
            Contact::class => 'Contact',
            Store::class => 'Store',
            Opportunity::class => 'Opportunity',
            Progress::class => 'Progress note',
            default => class_basename($subjectClass),
        };

        $nameRaw = $attributes['name'] ?? $old['name'] ?? null;
        $name = is_string($nameRaw) ? $nameRaw : null;

        $title = match ($event) {
            'created' => $name !== null
                ? sprintf('%s %s created', $subjectLabel, $name)
                : $subjectLabel.' created',
            'updated' => $name !== null
                ? sprintf('%s %s updated', $subjectLabel, $name)
                : $subjectLabel.' updated',
            'deleted' => $name !== null
                ? sprintf('%s %s deleted', $subjectLabel, $name)
                : $subjectLabel.' deleted',
            default => $activity->description ?? sprintf('%s %s', $subjectLabel, $event),
        };

        $description = $this->describeChanges($attributes, $old);

        return [
            'id' => 'log_'.$activity->id,
            'category' => mb_strtolower($subjectLabel),
            'icon' => $this->iconFor($subjectClass, $event),
            'title' => $title,
            'description' => $description,
            'actor' => $causer ? ['id' => $causer->getKey(), 'name' => $causer->getAttribute('name')] : null,
            'occurredAt' => $activity->created_at?->toIso8601String(),
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>  $old
     */
    private function describeChanges(array $attributes, array $old): ?string
    {
        if ($old === [] || $attributes === []) {
            return null;
        }

        $ignored = ['updated_at', 'created_at', 'id'];
        $changes = [];

        foreach ($attributes as $key => $value) {
            if (in_array($key, $ignored, true)) {
                continue;
            }

            $previous = $old[$key] ?? null;
            if ($previous === $value) {
                continue;
            }

            $label = str_replace('_', ' ', $key);
            $changes[] = sprintf('%s: %s → %s', $label, $this->formatValue($previous), $this->formatValue($value));
        }

        if ($changes === []) {
            return null;
        }

        return implode(' · ', array_slice($changes, 0, 3));
    }

    private function formatValue(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        if (is_bool($value)) {
            return $value ? 'yes' : 'no';
        }

        if (is_array($value)) {
            return json_encode($value) ?: '—';
        }

        if (! is_scalar($value)) {
            return '—';
        }

        $string = (string) $value;

        if ($this->looksLikeDate($string)) {
            try {
                $date = \Illuminate\Support\Carbon::parse($string);

                return $date->format('H:i:s') === '00:00:00'
                    ? $date->format('M j, Y')
                    : $date->format('M j, Y g:i A');
            } catch (Throwable) {
                // fall through to truncation
            }
        }

        return mb_strlen($string) > 40 ? mb_substr($string, 0, 40).'…' : $string;
    }

    private function looksLikeDate(string $value): bool
    {
        return (bool) preg_match('/^\d{4}-\d{2}-\d{2}([ T]\d{2}:\d{2}(:\d{2})?)?/', $value);
    }

    private function iconFor(string $subjectClass, string $event): string
    {
        return match (true) {
            $subjectClass === Contact::class => 'contact',
            $subjectClass === Store::class => 'store',
            $subjectClass === Opportunity::class => 'opportunity',
            $subjectClass === Progress::class => 'note',
            $subjectClass === Dealership::class && $event === 'created' => 'sparkle',
            $subjectClass === Dealership::class => 'building',
            default => 'activity',
        };
    }
}
