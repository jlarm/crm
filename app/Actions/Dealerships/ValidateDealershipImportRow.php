<?php

declare(strict_types=1);

namespace App\Actions\Dealerships;

use Illuminate\Support\Facades\Validator;

final class ValidateDealershipImportRow
{
    private const DEALERSHIP_FIELDS = [
        'name', 'address', 'city', 'state', 'zip_code', 'phone',
        'type', 'status', 'rating', 'current_solution_name',
        'current_solution_use', 'notes',
    ];

    private const STORE_FIELDS = [
        'name', 'address', 'city', 'state', 'zip_code', 'phone',
        'current_solution_name', 'current_solution_use',
    ];

    private const CONTACT_FIELDS = [
        'name', 'email', 'phone', 'position', 'linkedin_link', 'primary_contact',
    ];

    /**
     * Apply defaults and validate a row. Returns the resolved values and any errors.
     *
     * @param  array{line: int, row_type: string, raw: array<string, string|null>}  $row
     * @param  array{status: string, rating: string, type: string}  $defaults
     * @return array{line: int, row_type: string, resolved: array<string, mixed>, errors: array<string, array<int, string>>, parent_ref: ?string, extra_user_emails: array<int, string>}
     */
    public function __invoke(array $row, array $defaults): array
    {
        $type = $row['row_type'];
        $raw = $row['raw'];

        return match ($type) {
            'dealership' => $this->validateDealership($row['line'], $raw, $defaults),
            'store' => $this->validateStore($row['line'], $raw),
            'contact' => $this->validateContact($row['line'], $raw),
            default => [
                'line' => $row['line'],
                'row_type' => $type,
                'resolved' => [],
                'errors' => ['row_type' => ["Unknown row type: {$type}"]],
                'parent_ref' => null,
                'extra_user_emails' => [],
            ],
        };
    }

    /**
     * @param  array<string, string|null>  $raw
     * @param  array{status: string, rating: string, type: string}  $defaults
     */
    private function validateDealership(int $line, array $raw, array $defaults): array
    {
        $resolved = $this->pick($raw, self::DEALERSHIP_FIELDS);
        $resolved['status'] ??= $defaults['status'];
        $resolved['rating'] ??= $defaults['rating'];
        $resolved['type'] ??= $defaults['type'];

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:2'],
            'zip_code' => ['nullable', 'string', 'max:10'],
            'phone' => ['nullable', 'string', 'max:20'],
            'type' => ['required', 'string', 'in:Automotive,RV,Motorsports,Maritime,Association'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'rating' => ['required', 'string', 'in:hot,warm,cold'],
            'current_solution_name' => ['nullable', 'string', 'max:255'],
            'current_solution_use' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];

        $errors = Validator::make($resolved, $rules)->errors()->toArray();

        return [
            'line' => $line,
            'row_type' => 'dealership',
            'resolved' => $resolved,
            'errors' => $errors,
            'parent_ref' => null,
            'extra_user_emails' => $this->parseExtraEmails($raw['user_emails'] ?? null),
        ];
    }

    /**
     * @param  array<string, string|null>  $raw
     */
    private function validateStore(int $line, array $raw): array
    {
        $resolved = $this->pick($raw, self::STORE_FIELDS);
        $parentRef = $raw['dealership_ref'] ?? null;

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:2'],
            'zip_code' => ['nullable', 'string', 'max:10'],
            'phone' => ['nullable', 'string', 'max:20'],
            'current_solution_name' => ['nullable', 'string', 'max:255'],
            'current_solution_use' => ['nullable', 'string', 'max:255'],
        ];

        $errors = Validator::make($resolved, $rules)->errors()->toArray();

        if ($parentRef === null || $parentRef === '') {
            $errors['dealership_ref'] = ['A dealership_ref is required for store rows.'];
        }

        return [
            'line' => $line,
            'row_type' => 'store',
            'resolved' => $resolved,
            'errors' => $errors,
            'parent_ref' => $parentRef,
            'extra_user_emails' => [],
        ];
    }

    /**
     * @param  array<string, string|null>  $raw
     */
    private function validateContact(int $line, array $raw): array
    {
        $resolved = $this->pick($raw, self::CONTACT_FIELDS);
        $parentRef = $raw['dealership_ref'] ?? null;

        if (is_string($resolved['primary_contact'] ?? null)) {
            $resolved['primary_contact'] = in_array(
                mb_strtolower($resolved['primary_contact']),
                ['1', 'true', 'yes', 'y'],
                true
            );
        } else {
            $resolved['primary_contact'] = (bool) ($resolved['primary_contact'] ?? false);
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'position' => ['nullable', 'string', 'max:255'],
            'linkedin_link' => ['nullable', 'string', 'max:255'],
            'primary_contact' => ['nullable', 'boolean'],
        ];

        $errors = Validator::make($resolved, $rules)->errors()->toArray();

        if ($parentRef === null || $parentRef === '') {
            $errors['dealership_ref'] = ['A dealership_ref is required for contact rows.'];
        }

        return [
            'line' => $line,
            'row_type' => 'contact',
            'resolved' => $resolved,
            'errors' => $errors,
            'parent_ref' => $parentRef,
            'extra_user_emails' => [],
        ];
    }

    /**
     * @param  array<string, string|null>  $raw
     * @param  array<int, string>  $fields
     * @return array<string, mixed>
     */
    private function pick(array $raw, array $fields): array
    {
        $out = [];
        foreach ($fields as $field) {
            $out[$field] = $raw[$field] ?? null;
        }

        return $out;
    }

    /**
     * @return array<int, string>
     */
    private function parseExtraEmails(?string $value): array
    {
        if ($value === null || mb_trim($value) === '') {
            return [];
        }

        return array_values(array_filter(array_map(
            fn (string $e): string => mb_strtolower(mb_trim($e)),
            preg_split('/[,;|]/', $value) ?: []
        )));
    }
}
