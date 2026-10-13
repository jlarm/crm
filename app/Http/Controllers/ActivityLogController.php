<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\DealerEmail;
use App\Models\DealerEmailTemplate;
use App\Models\Dealership;
use App\Models\Opportunity;
use App\Models\PdfAttachment;
use App\Models\Progress;
use App\Models\Reminder;
use App\Models\SentEmail;
use App\Models\Store;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

/**
 * Everything the CRM's activity log recorded, across all users and records. Super admins only.
 */
final class ActivityLogController extends Controller
{
    /**
     * Record types that can be filtered on, by the key used in the query string.
     *
     * @var array<string, array{class: class-string, label: string}>
     */
    private const array TYPES = [
        'dealership' => ['class' => Dealership::class, 'label' => 'Dealership'],
        'contact' => ['class' => Contact::class, 'label' => 'Contact'],
        'store' => ['class' => Store::class, 'label' => 'Store'],
        'opportunity' => ['class' => Opportunity::class, 'label' => 'Opportunity'],
        'progress' => ['class' => Progress::class, 'label' => 'Logged activity'],
        'user' => ['class' => User::class, 'label' => 'User'],
        'tag' => ['class' => Tag::class, 'label' => 'Tag'],
        'reminder' => ['class' => Reminder::class, 'label' => 'Reminder'],
        'dealer_email' => ['class' => DealerEmail::class, 'label' => 'Dealer email'],
        'dealer_email_template' => ['class' => DealerEmailTemplate::class, 'label' => 'Email template'],
        'sent_email' => ['class' => SentEmail::class, 'label' => 'Sent email'],
        'attachment' => ['class' => PdfAttachment::class, 'label' => 'Attachment'],
    ];

    /**
     * Never shown, even if an older entry recorded them.
     *
     * @var list<string>
     */
    private const array HIDDEN_FIELDS = ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes', 'created_at', 'updated_at'];

    public function __invoke(Request $request): Response
    {
        $filters = $request->validate([
            'user' => ['nullable', 'integer'],
            'type' => ['nullable', Rule::in([...array_keys(self::TYPES), 'login'])],
            'event' => ['nullable', Rule::in(['created', 'updated', 'deleted'])],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $type = $filters['type'] ?? null;
        $search = mb_trim((string) ($filters['search'] ?? ''));

        $entries = Activity::query()
            ->when($filters['user'] ?? null, fn (Builder $query, int|string $userId): Builder => $query
                ->where('causer_type', User::class)
                ->where('causer_id', $userId))
            ->when($type === 'login', fn (Builder $query): Builder => $query->whereNull('subject_type')->where('description', 'like', 'Logged%'))
            ->when($type !== null && $type !== 'login', fn (Builder $query): Builder => $query->where('subject_type', self::TYPES[$type]['class']))
            ->when($filters['event'] ?? null, fn (Builder $query, string $event): Builder => $query->where('event', $event))
            ->when($filters['from'] ?? null, fn (Builder $query, string $from): Builder => $query->where('created_at', '>=', Carbon::parse($from)->startOfDay()))
            ->when($filters['to'] ?? null, fn (Builder $query, string $to): Builder => $query->where('created_at', '<=', Carbon::parse($to)->endOfDay()))
            ->when($search !== '', fn (Builder $query): Builder => $query->where(fn (Builder $query) => $query
                ->where('description', 'like', '%'.$search.'%')
                ->orWhere('properties', 'like', '%'.$search.'%')))
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        $this->loadSubjects($entries->getCollection());

        // Looked up directly so people whose accounts were deleted still show by name.
        $causers = User::withTrashed()
            ->whereIn('id', $entries->getCollection()->where('causer_type', User::class)->pluck('causer_id')->unique())
            ->get(['id', 'name'])
            ->keyBy('id');

        $entries->through(fn (Activity $activity): array => $this->present($activity, $activity->causer_type === User::class ? $causers->get($activity->causer_id) : null));

        return Inertia::render('ActivityLog/Index', [
            'entries' => $entries,
            'filters' => [
                'user' => isset($filters['user']) ? (string) $filters['user'] : '',
                'type' => $type ?? '',
                'event' => $filters['event'] ?? '',
                'from' => $filters['from'] ?? null,
                'to' => $filters['to'] ?? null,
                'search' => $search,
            ],
            'users' => User::withTrashed()->orderBy('name')->get(['id', 'name']),
            'types' => [
                ...collect(self::TYPES)->map(fn (array $type, string $key): array => ['value' => $key, 'label' => $type['label']])->values()->all(),
                ['value' => 'login', 'label' => 'Logins'],
            ],
            'retentionDays' => (int) config('activitylog.delete_records_older_than_days'),
        ]);
    }

    /**
     * Loads each entry's record, but only for the types listed above. The log also
     * holds entries for models that have since been removed from the app, which
     * a plain eager load would try, and fail, to build.
     *
     * @param  Collection<int, Activity>  $activities
     */
    private function loadSubjects(Collection $activities): void
    {
        $known = array_column(self::TYPES, 'class');

        foreach ($activities->groupBy('subject_type') as $class => $group) {
            $subjects = in_array($class, $known, true)
                ? $class::query()
                    ->when(in_array(SoftDeletes::class, class_uses_recursive($class), true), fn (Builder $query): Builder => $query->withTrashed())
                    ->whereKey($group->pluck('subject_id')->filter()->unique())
                    ->get()
                    ->keyBy(fn (Model $model): int|string => $model->getKey())
                : collect();

            foreach ($group as $activity) {
                $activity->setRelation('subject', $subjects->get($activity->subject_id));
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function present(Activity $activity, ?User $causer): array
    {
        $properties = $activity->properties?->toArray() ?? [];
        $attributes = is_array($properties['attributes'] ?? null) ? $properties['attributes'] : [];
        $old = is_array($properties['old'] ?? null) ? $properties['old'] : [];

        return [
            'id' => $activity->id,
            'description' => $activity->description,
            'event' => $activity->event,
            'type' => $this->typeLabel($activity->subject_type),
            'subject' => $this->subjectName($activity, $attributes, $old),
            'url' => $this->subjectUrl($activity, $attributes, $old),
            'actor' => $causer === null ? null : ['id' => $causer->id, 'name' => $causer->name],
            'changes' => $this->changes($activity->event, $attributes, $old),
            'occurredAt' => $activity->created_at?->toIso8601String(),
        ];
    }

    private function typeLabel(?string $subjectType): ?string
    {
        if ($subjectType === null) {
            return null;
        }

        foreach (self::TYPES as $type) {
            if ($type['class'] === $subjectType) {
                return $type['label'];
            }
        }

        return class_basename($subjectType);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>  $old
     */
    private function subjectName(Activity $activity, array $attributes, array $old): ?string
    {
        foreach (['name', 'title', 'subject', 'file_name'] as $key) {
            $value = $attributes[$key] ?? $old[$key] ?? $activity->subject?->getAttribute($key);

            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return $activity->subject_id === null ? null : '#'.$activity->subject_id;
    }

    /**
     * Where to look at the record now: its dealership, on the matching tab.
     *
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>  $old
     */
    private function subjectUrl(Activity $activity, array $attributes, array $old): ?string
    {
        if ($activity->subject_type === Dealership::class && $activity->subject !== null) {
            return route('dealerships.show', $activity->subject_id);
        }

        $tab = match ($activity->subject_type) {
            Contact::class => 'contacts',
            Store::class => 'stores',
            Opportunity::class => 'opportunities',
            Progress::class => 'activity',
            default => null,
        };

        $dealershipId = $activity->subject?->getAttribute('dealership_id') ?? $attributes['dealership_id'] ?? $old['dealership_id'] ?? null;

        if ($tab === null || ! is_numeric($dealershipId) || ! Dealership::whereKey($dealershipId)->exists()) {
            return null;
        }

        return route('dealerships.show', ['dealership' => (int) $dealershipId, 'tab' => $tab]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>  $old
     * @return list<array{field: string, old: string|null, new: string|null}>
     */
    private function changes(?string $event, array $attributes, array $old): array
    {
        $keys = $event === 'deleted' ? array_keys($old ?: $attributes) : array_keys($attributes);
        $changes = [];

        foreach ($keys as $key) {
            if (in_array($key, self::HIDDEN_FIELDS, true) || $key === 'id') {
                continue;
            }

            $before = $event === 'deleted' ? ($old[$key] ?? $attributes[$key] ?? null) : ($old[$key] ?? null);
            $after = $event === 'deleted' ? null : ($attributes[$key] ?? null);

            if ($event === 'updated' && $before === $after) {
                continue;
            }

            if ($event !== 'updated' && ($before ?? $after) === null) {
                continue;
            }

            $changes[] = [
                'field' => str_replace('_', ' ', $key),
                'old' => $this->formatValue($before),
                'new' => $this->formatValue($after),
            ];
        }

        return $changes;
    }

    private function formatValue(mixed $value): ?string
    {
        if (is_string($value) && preg_match('/^\s*[\[{]/', $value) === 1) {
            $decoded = json_decode($value, true);
            $value = is_array($decoded) ? $decoded : $value;
        }

        if (is_array($value)) {
            $value = $this->describeArray($value);
        }

        if ($value === null || $value === '') {
            return null;
        }

        if (is_bool($value)) {
            return $value ? 'yes' : 'no';
        }

        if (! is_scalar($value)) {
            return null;
        }

        // Tags become spaces so "<p>Hi</p><p>See</p>" reads "Hi See", not "HiSee".
        $string = preg_replace('/<[^>]+>/', ' ', (string) $value) ?? (string) $value;
        $string = mb_trim(preg_replace('/\s+/u', ' ', html_entity_decode($string)) ?? $string);

        return mb_strlen($string) > 160 ? mb_substr($string, 0, 160).'…' : $string;
    }

    /**
     * Lists read as "WY, IL"; settings read as "states: WY · direction: asc", skipping empty ones.
     *
     * @param  array<mixed>  $value
     */
    private function describeArray(array $value): ?string
    {
        if (array_is_list($value)) {
            $items = array_filter(array_map(fn (mixed $item): ?string => is_scalar($item) ? (string) $item : null, $value), fn (?string $item): bool => $item !== null && $item !== '');

            return $items === [] ? null : implode(', ', $items);
        }

        $parts = [];

        foreach ($value as $key => $item) {
            $formatted = is_array($item) ? $this->describeArray($item) : (is_bool($item) ? ($item ? 'yes' : null) : (is_scalar($item) ? (string) $item : null));

            if ($formatted !== null && $formatted !== '') {
                $parts[] = str_replace('_', ' ', (string) $key).': '.$formatted;
            }
        }

        return $parts === [] ? null : implode(' · ', $parts);
    }
}
