<?php

declare(strict_types=1);

namespace App\Actions\Dealerships;

use App\Models\Dealership;
use Illuminate\Support\Facades\DB;

final class FindLikelyDuplicateDealerships
{
    /**
     * Words dropped before comparing names, so "Smith Ford, Inc." matches "Smith Ford".
     */
    private const array IGNORED_NAME_WORDS = ['the', 'inc', 'incorporated', 'llc', 'ltd', 'co', 'corp', 'corporation', 'company'];

    /**
     * Import rows that would create a new dealership which probably already exists.
     *
     * Exact (case-insensitive) name matches are not reported: the importer already
     * treats those as the existing dealership. This catches the near misses it would
     * otherwise create as duplicates: the same name written differently, or the same
     * phone number under a different name. Rows referencing a dealership that is not
     * in the file are checked too, since the importer auto-creates those.
     *
     * @param  array<int, array{line: int, row_type: string, resolved: array<string, mixed>, errors: array<string, array<int, string>>, parent_ref: string|null, extra_user_emails: array<int, string>}>  $validatedRows
     * @return list<array{line: int, name: string, reason: string, match: array{id: int, name: string, city: string|null, state: string|null}}>
     */
    public function __invoke(array $validatedRows): array
    {
        $candidates = $this->newDealershipCandidates($validatedRows);

        if ($candidates === []) {
            return [];
        }

        $existingNames = Dealership::query()
            ->whereIn(DB::raw('LOWER(name)'), array_keys($candidates))
            ->pluck('name')
            ->map(fn (mixed $name): string => mb_strtolower(is_string($name) ? $name : ''))
            ->flip()
            ->all();

        $candidates = array_diff_key($candidates, $existingNames);

        if ($candidates === []) {
            return [];
        }

        $byName = [];
        $byPhone = [];

        Dealership::query()
            ->select(['id', 'name', 'city', 'state', 'phone'])
            ->orderBy('id')
            ->chunk(1000, function ($dealerships) use (&$byName, &$byPhone): void {
                foreach ($dealerships as $dealership) {
                    $byName[$this->normaliseName($dealership->name)] ??= $dealership;

                    $phone = $this->normalisePhone($dealership->phone);
                    if ($phone !== null) {
                        $byPhone[$phone] ??= $dealership;
                    }
                }
            });

        $duplicates = [];

        foreach ($candidates as $candidate) {
            $reason = 'Similar name';
            $match = $byName[$this->normaliseName($candidate['name'])] ?? null;

            if ($match === null && $candidate['phone'] !== null) {
                $reason = 'Same phone number';
                $match = $byPhone[$candidate['phone']] ?? null;
            }

            if (! $match instanceof Dealership) {
                continue;
            }

            $duplicates[] = [
                'line' => $candidate['line'],
                'name' => $candidate['name'],
                'reason' => $reason,
                'match' => [
                    'id' => $match->id,
                    'name' => $match->name,
                    'city' => $match->city,
                    'state' => $match->state,
                ],
            ];
        }

        return $duplicates;
    }

    /**
     * The existing dealership a single new one most likely duplicates, if any.
     *
     * Unlike the import check, an exact name match counts here: nothing else will catch it.
     *
     * @return array{reason: string, match: array{id: int, name: string, city: string|null, state: string|null}}|null
     */
    public function forCandidate(string $name, ?string $phone): ?array
    {
        $columns = ['id', 'name', 'city', 'state', 'phone'];

        $normalisedName = $this->normaliseName($name);
        $normalisedPhone = $this->normalisePhone($phone);
        $phoneMatch = null;

        foreach (Dealership::query()->select($columns)->orderBy('id')->lazy(1000) as $dealership) {
            if ($normalisedName !== '' && $this->normaliseName($dealership->name) === $normalisedName) {
                return $this->candidateMatch('Similar name', $dealership);
            }

            if ($normalisedPhone !== null && $phoneMatch === null && $this->normalisePhone($dealership->phone) === $normalisedPhone) {
                $phoneMatch = $dealership;
            }
        }

        return $phoneMatch === null ? null : $this->candidateMatch('Same phone number', $phoneMatch);
    }

    /**
     * Point flagged rows, and the store/contact rows under them, at the existing dealership.
     *
     * @param  array<int, array{line: int, row_type: string, resolved: array<string, mixed>, errors: array<string, array<int, string>>, parent_ref: string|null, extra_user_emails: array<int, string>}>  $validatedRows
     * @param  list<array{line: int, name: string, reason: string, match: array{id: int, name: string, city: string|null, state: string|null}}>  $duplicates
     * @return array<int, array{line: int, row_type: string, resolved: array<string, mixed>, errors: array<string, array<int, string>>, parent_ref: string|null, extra_user_emails: array<int, string>}>
     */
    public function mergeIntoExisting(array $validatedRows, array $duplicates): array
    {
        $existingNameFor = [];
        foreach ($duplicates as $duplicate) {
            $existingNameFor[mb_strtolower($duplicate['name'])] = $duplicate['match']['name'];
        }

        foreach ($validatedRows as $index => $row) {
            if ($row['row_type'] === 'dealership') {
                $name = $row['resolved']['name'] ?? null;
                $key = is_string($name) ? mb_strtolower($name) : null;

                if ($key !== null && isset($existingNameFor[$key])) {
                    $row['resolved']['name'] = $existingNameFor[$key];
                }
            } elseif (is_string($row['parent_ref']) && isset($existingNameFor[mb_strtolower($row['parent_ref'])])) {
                $row['parent_ref'] = $existingNameFor[mb_strtolower($row['parent_ref'])];
            }

            $validatedRows[$index] = $row;
        }

        return $validatedRows;
    }

    /**
     * Valid rows that would create a dealership, keyed by lowercased name, first line wins.
     *
     * @param  array<int, array{line: int, row_type: string, resolved: array<string, mixed>, errors: array<string, array<int, string>>, parent_ref: string|null, extra_user_emails: array<int, string>}>  $validatedRows
     * @return array<string, array{line: int, name: string, phone: string|null}>
     */
    private function newDealershipCandidates(array $validatedRows): array
    {
        $candidates = [];
        $orphanRefs = [];

        foreach ($validatedRows as $row) {
            if ($row['errors'] !== []) {
                continue;
            }

            if ($row['row_type'] === 'dealership') {
                $name = $row['resolved']['name'] ?? null;
                if (! is_string($name) || $name === '') {
                    continue;
                }

                $phone = $row['resolved']['phone'] ?? null;
                $candidates[mb_strtolower($name)] ??= [
                    'line' => $row['line'],
                    'name' => $name,
                    'phone' => $this->normalisePhone(is_string($phone) ? $phone : null),
                ];

                continue;
            }

            if (is_string($row['parent_ref']) && $row['parent_ref'] !== '') {
                $orphanRefs[mb_strtolower($row['parent_ref'])] ??= [
                    'line' => $row['line'],
                    'name' => $row['parent_ref'],
                    'phone' => null,
                ];
            }
        }

        return $candidates + $orphanRefs;
    }

    /**
     * @return array{reason: string, match: array{id: int, name: string, city: string|null, state: string|null}}
     */
    private function candidateMatch(string $reason, Dealership $dealership): array
    {
        return [
            'reason' => $reason,
            'match' => [
                'id' => $dealership->id,
                'name' => $dealership->name,
                'city' => $dealership->city,
                'state' => $dealership->state,
            ],
        ];
    }

    private function normaliseName(string $name): string
    {
        $name = mb_strtolower(str_replace('&', ' and ', $name));
        $words = preg_split('/[^\p{L}\p{N}]+/u', $name, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return implode(' ', array_diff($words, self::IGNORED_NAME_WORDS));
    }

    private function normalisePhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?? '';

        return mb_strlen($digits) >= 10 ? mb_substr($digits, -10) : null;
    }
}
