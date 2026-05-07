<?php

declare(strict_types=1);

namespace App\Actions\Dealerships;

use App\Models\Contact;
use App\Models\Dealership;
use App\Models\Store;
use App\Models\User;
use App\Observers\ContactObserver;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

final class ImportDealershipRow
{
    /**
     * @var array<string, Dealership>
     */
    private array $existingByName = [];

    /**
     * @var array<string, int>
     */
    private array $userIdByEmail = [];

    /**
     * @param  array<int, array{line: int, row_type: string, resolved: array<string, mixed>, errors: array<string, array<int, string>>, parent_ref: string|null, extra_user_emails: array<int, string>}>  $validatedRows
     * @param  array{importer_id: int, default_user_ids: array<int, int>, defaults: array{status: string, rating: string, type: string}, sync_mailcoach: bool, update_existing: bool, transactional: bool}  $options
     * @return array{created: array{dealerships: int, stores: int, contacts: int}, updated: array{dealerships: int, stores: int, contacts: int}, skipped: int, errors: array<int, array{line: int, message: string}>}
     */
    public function __invoke(array $validatedRows, array $options): array
    {
        /** @var array{created: array{dealerships: int, stores: int, contacts: int}, updated: array{dealerships: int, stores: int, contacts: int}, skipped: int, errors: array<int, array{line: int, message: string}>} $stats */
        $stats = [
            'created' => ['dealerships' => 0, 'stores' => 0, 'contacts' => 0],
            'updated' => ['dealerships' => 0, 'stores' => 0, 'contacts' => 0],
            'skipped' => 0,
            'errors' => [],
        ];

        $previousFlag = ContactObserver::$syncMailcoach;
        ContactObserver::$syncMailcoach = $options['sync_mailcoach'];

        try {
            $groups = $this->groupRows($validatedRows);

            $this->prefetchDealerships($groups);
            $this->prefetchUsers($validatedRows);

            if ($options['transactional']) {
                DB::transaction(function () use ($groups, $options, &$stats): void {
                    foreach ($groups as $group) {
                        $this->processGroup($group, $options, $stats);
                    }
                });
            } else {
                foreach ($groups as $group) {
                    try {
                        DB::transaction(function () use ($group, $options, &$stats): void {
                            $this->processGroup($group, $options, $stats);
                        });
                    } catch (Throwable $e) {
                        Log::error('[ImportDealershipRow] Group failed.', [
                            'parent_ref' => $group['parent_ref'] ?? null,
                            'exception' => $e,
                        ]);
                        $stats['errors'][] = [
                            'line' => $group['parent']['line'] ?? 0,
                            'message' => $e->getMessage(),
                        ];
                    }
                }
            }
        } finally {
            ContactObserver::$syncMailcoach = $previousFlag;
        }

        return $stats;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array{parent: ?array<string, mixed>, parent_ref: ?string, stores: array<int, array<string, mixed>>, contacts: array<int, array<string, mixed>>}>
     */
    private function groupRows(array $rows): array
    {
        $groups = [];
        $orphans = [];

        foreach ($rows as $row) {
            if (! empty($row['errors'])) {
                continue;
            }

            if ($row['row_type'] === 'dealership') {
                $key = mb_strtolower((string) $row['resolved']['name']);
                $groups[$key] = [
                    'parent' => $row,
                    'parent_ref' => $row['resolved']['name'],
                    'stores' => [],
                    'contacts' => [],
                ];
            }
        }

        foreach ($rows as $row) {
            if (! empty($row['errors']) || $row['row_type'] === 'dealership') {
                continue;
            }

            $ref = $row['parent_ref'];
            if ($ref === null) {
                continue;
            }

            $key = mb_strtolower($ref);

            if (isset($groups[$key])) {
                $groups[$key][$row['row_type'] === 'store' ? 'stores' : 'contacts'][] = $row;

                continue;
            }

            $orphans[$key] ??= [
                'parent' => null,
                'parent_ref' => $ref,
                'stores' => [],
                'contacts' => [],
            ];
            $orphans[$key][$row['row_type'] === 'store' ? 'stores' : 'contacts'][] = $row;
        }

        return array_values(array_merge($groups, $orphans));
    }

    /**
     * Single query to fetch every existing dealership referenced by the import,
     * eager-loading stores and contacts so per-row dedupe checks hit memory.
     *
     * @param  array<int, array<string, mixed>>  $groups
     */
    private function prefetchDealerships(array $groups): void
    {
        $names = [];
        foreach ($groups as $group) {
            if ($group['parent_ref']) {
                $names[mb_strtolower($group['parent_ref'])] = true;
            }
        }

        if ($names === []) {
            return;
        }

        $this->existingByName = Dealership::query()
            ->with(['stores:id,dealership_id,name', 'contacts:id,dealership_id,email'])
            ->whereIn(DB::raw('LOWER(name)'), array_keys($names))
            ->get()
            ->keyBy(fn (Dealership $d): string => mb_strtolower($d->name))
            ->all();
    }

    /**
     * Single query to resolve every consultant email referenced across the import.
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function prefetchUsers(array $rows): void
    {
        $emails = [];
        foreach ($rows as $row) {
            foreach ($row['extra_user_emails'] ?? [] as $email) {
                $emails[mb_strtolower($email)] = true;
            }
        }

        if ($emails === []) {
            return;
        }

        $this->userIdByEmail = User::query()
            ->whereIn(DB::raw('LOWER(email)'), array_keys($emails))
            ->pluck('id', 'email')
            ->mapWithKeys(fn (int $id, string $email): array => [mb_strtolower($email) => $id])
            ->all();
    }

    /**
     * @param  array{parent: ?array<string, mixed>, parent_ref: ?string, stores: array<int, array<string, mixed>>, contacts: array<int, array<string, mixed>>}  $group
     * @param  array<string, mixed>  $options
     *
     * @param-out array{created: array{dealerships: int, stores: int, contacts: int}, updated: array{dealerships: int, stores: int, contacts: int}, skipped: int, errors: array<int, array{line: int, message: string}>} $stats
     *
     * @param  array{created: array{dealerships: int, stores: int, contacts: int}, updated: array{dealerships: int, stores: int, contacts: int}, skipped: int, errors: array<int, array{line: int, message: string}>}  $stats
     */
    private function processGroup(array $group, array $options, array &$stats): void
    {
        $dealership = $this->upsertDealership($group, $options, $stats);

        foreach ($group['stores'] as $row) {
            $this->upsertStore($dealership, $row, $options, $stats);
        }

        foreach ($group['contacts'] as $row) {
            $this->upsertContact($dealership, $row, $options, $stats);
        }
    }

    /**
     * @param  array{parent: ?array<string, mixed>, parent_ref: ?string, stores: array<int, array<string, mixed>>, contacts: array<int, array<string, mixed>>}  $group
     * @param  array<string, mixed>  $options
     *
     * @param-out array{created: array{dealerships: int, stores: int, contacts: int}, updated: array{dealerships: int, stores: int, contacts: int}, skipped: int, errors: array<int, array{line: int, message: string}>} $stats
     *
     * @param  array{created: array{dealerships: int, stores: int, contacts: int}, updated: array{dealerships: int, stores: int, contacts: int}, skipped: int, errors: array<int, array{line: int, message: string}>}  $stats
     */
    private function upsertDealership(array $group, array $options, array &$stats): Dealership
    {
        $parent = $group['parent'];
        $name = $parent['resolved']['name'] ?? $group['parent_ref'];
        $key = mb_strtolower((string) $name);
        $existing = $this->existingByName[$key] ?? null;

        if ($existing) {
            if ($parent && $options['update_existing']) {
                $existing->update($parent['resolved']);
                $this->syncUsers($existing, $parent['extra_user_emails'] ?? [], $options);
                $stats['updated']['dealerships']++;
            } elseif ($parent) {
                $stats['skipped']++;
            }

            return $existing;
        }

        $attributes = $parent
            ? [...$parent['resolved'], 'user_id' => $options['importer_id']]
            : [
                'name' => $name,
                'status' => $options['defaults']['status'],
                'rating' => $options['defaults']['rating'],
                'type' => $options['defaults']['type'],
                'user_id' => $options['importer_id'],
            ];

        $dealership = Dealership::create($attributes);
        $dealership->setRelation('stores', new Collection);
        $dealership->setRelation('contacts', new Collection);
        $this->existingByName[$key] = $dealership;

        $this->syncUsers($dealership, $parent['extra_user_emails'] ?? [], $options);
        $stats['created']['dealerships']++;

        return $dealership;
    }

    /**
     * @param  array<int, string>  $extraEmails
     * @param  array<string, mixed>  $options
     */
    private function syncUsers(Dealership $dealership, array $extraEmails, array $options): void
    {
        $ids = array_unique(array_merge([$options['importer_id']], $options['default_user_ids']));

        foreach ($extraEmails as $email) {
            if (isset($this->userIdByEmail[mb_strtolower($email)])) {
                $ids[] = $this->userIdByEmail[mb_strtolower($email)];
            }
        }

        $dealership->users()->syncWithoutDetaching(array_unique($ids));
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, mixed>  $options
     *
     * @param-out array{created: array{dealerships: int, stores: int, contacts: int}, updated: array{dealerships: int, stores: int, contacts: int}, skipped: int, errors: array<int, array{line: int, message: string}>} $stats
     *
     * @param  array{created: array{dealerships: int, stores: int, contacts: int}, updated: array{dealerships: int, stores: int, contacts: int}, skipped: int, errors: array<int, array{line: int, message: string}>}  $stats
     */
    private function upsertStore(Dealership $dealership, array $row, array $options, array &$stats): void
    {
        $name = (string) $row['resolved']['name'];
        $existing = $dealership->stores->first(
            fn (Store $s): bool => mb_strtolower($s->name) === mb_strtolower($name)
        );

        if ($existing) {
            if ($options['update_existing']) {
                $existing->update($row['resolved']);
                $stats['updated']['stores']++;
            } else {
                $stats['skipped']++;
            }

            return;
        }

        $store = Store::create([
            ...$row['resolved'],
            'dealership_id' => $dealership->id,
            'user_id' => $options['importer_id'],
        ]);
        $dealership->stores->push($store);
        $stats['created']['stores']++;
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, mixed>  $options
     *
     * @param-out array{created: array{dealerships: int, stores: int, contacts: int}, updated: array{dealerships: int, stores: int, contacts: int}, skipped: int, errors: array<int, array{line: int, message: string}>} $stats
     *
     * @param  array{created: array{dealerships: int, stores: int, contacts: int}, updated: array{dealerships: int, stores: int, contacts: int}, skipped: int, errors: array<int, array{line: int, message: string}>}  $stats
     */
    private function upsertContact(Dealership $dealership, array $row, array $options, array &$stats): void
    {
        $email = $row['resolved']['email'] ?? null;

        $existing = $email
            ? $dealership->contacts->firstWhere('email', $email)
            : null;

        if ($existing) {
            if ($options['update_existing']) {
                $existing->update($row['resolved']);
                $stats['updated']['contacts']++;
            } else {
                $stats['skipped']++;
            }

            return;
        }

        $contact = Contact::create([
            ...$row['resolved'],
            'dealership_id' => $dealership->id,
        ]);
        $dealership->contacts->push($contact);
        $stats['created']['contacts']++;
    }
}
