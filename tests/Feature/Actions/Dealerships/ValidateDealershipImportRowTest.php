<?php

declare(strict_types=1);

use App\Actions\Dealerships\ValidateDealershipImportRow;

$defaults = ['status' => 'active', 'rating' => 'warm', 'type' => 'Automotive'];

describe('ValidateDealershipImportRow action', function () use ($defaults): void {
    it('coerces string primary_contact values to true booleans', function () use ($defaults): void {
        $action = new ValidateDealershipImportRow;

        $row = [
            'line' => 1,
            'row_type' => 'contact',
            'raw' => [
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
                'primary_contact' => 'YES',
                'dealership_ref' => 'Parent Co',
            ],
        ];

        $result = $action($row, $defaults);

        expect($result['resolved']['primary_contact'])->toBeTrue()
            ->and($result['errors'])->toBeEmpty();
    });

    it('coerces string primary_contact value "0" to false', function () use ($defaults): void {
        $action = new ValidateDealershipImportRow;

        $row = [
            'line' => 1,
            'row_type' => 'contact',
            'raw' => [
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
                'primary_contact' => 'no',
                'dealership_ref' => 'Parent Co',
            ],
        ];

        $result = $action($row, $defaults);

        expect($result['resolved']['primary_contact'])->toBeFalse();
    });

    it('coerces a non-string primary_contact value to a boolean', function () use ($defaults): void {
        $action = new ValidateDealershipImportRow;

        $row = [
            'line' => 1,
            'row_type' => 'contact',
            'raw' => [
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
                // non-string flows through (bool) cast -> null becomes false.
                'primary_contact' => null,
                'dealership_ref' => 'Parent Co',
            ],
        ];

        $result = $action($row, $defaults);

        expect($result['resolved']['primary_contact'])->toBeFalse();
    });

    it('reports an error when contact rows lack a dealership_ref', function () use ($defaults): void {
        $action = new ValidateDealershipImportRow;

        $row = [
            'line' => 5,
            'row_type' => 'contact',
            'raw' => [
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
                'dealership_ref' => null,
            ],
        ];

        $result = $action($row, $defaults);

        expect($result['errors'])->toHaveKey('dealership_ref');
    });

    it('reports an error when store rows lack a dealership_ref', function () use ($defaults): void {
        $action = new ValidateDealershipImportRow;

        $row = [
            'line' => 5,
            'row_type' => 'store',
            'raw' => [
                'name' => 'Branch',
                'dealership_ref' => '',
            ],
        ];

        $result = $action($row, $defaults);

        expect($result['errors'])->toHaveKey('dealership_ref');
    });

    it('returns an unknown row type error for unsupported row types', function () use ($defaults): void {
        $action = new ValidateDealershipImportRow;

        $row = [
            'line' => 7,
            'row_type' => 'mystery',
            'raw' => ['name' => 'X'],
        ];

        $result = $action($row, $defaults);

        expect($result['errors'])->toHaveKey('row_type');
    });

    it('parses the user_emails column into a lowercased deduplicated list', function () use ($defaults): void {
        $action = new ValidateDealershipImportRow;

        $row = [
            'line' => 1,
            'row_type' => 'dealership',
            'raw' => [
                'name' => 'Acme',
                'user_emails' => 'A@example.com, b@example.com; B@EXAMPLE.com|',
            ],
        ];

        $result = $action($row, $defaults);

        expect($result['extra_user_emails'])->toContain('a@example.com')
            ->and($result['extra_user_emails'])->toContain('b@example.com');
    });

    it('returns an empty extra_user_emails array when none are provided', function () use ($defaults): void {
        $action = new ValidateDealershipImportRow;

        $row = [
            'line' => 1,
            'row_type' => 'dealership',
            'raw' => ['name' => 'Acme', 'user_emails' => null],
        ];

        $result = $action($row, $defaults);

        expect($result['extra_user_emails'])->toBe([]);
    });

    it('applies dropdown defaults when status, rating or type are missing', function () use ($defaults): void {
        $action = new ValidateDealershipImportRow;

        $row = [
            'line' => 1,
            'row_type' => 'dealership',
            'raw' => ['name' => 'Acme'],
        ];

        $result = $action($row, $defaults);

        expect($result['resolved']['status'])->toBe('active')
            ->and($result['resolved']['rating'])->toBe('warm')
            ->and($result['resolved']['type'])->toBe('Automotive');
    });
});
