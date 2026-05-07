<?php

declare(strict_types=1);

use App\Actions\Dealerships\ParseDealershipImportCsv;
use Illuminate\Http\UploadedFile;

function tmpCsv(string $content): UploadedFile
{
    $path = tempnam(sys_get_temp_dir(), 'parse').'.csv';
    file_put_contents($path, $content);

    return new UploadedFile($path, 'parse.csv', 'text/csv', null, true);
}

describe('ParseDealershipImportCsv action', function (): void {
    it('records a parse error for an unknown row_type value', function (): void {
        $csv = "row_type,name\nbogus,Phantom\n";

        $result = (new ParseDealershipImportCsv)(tmpCsv($csv));

        expect($result['rows'])->toBeEmpty()
            ->and($result['parse_errors'])->toHaveCount(1)
            ->and($result['parse_errors'][0]['message'])->toContain('Unknown row_type');
    });

    it('combines first_name and last_name when no name column is present', function (): void {
        $csv = "Email,First Name,Last Name,companyName\njoe@x.com,Joe,Smith,Acme\n";

        $result = (new ParseDealershipImportCsv)(tmpCsv($csv));

        expect($result['rows'])->toHaveCount(1)
            ->and($result['rows'][0]['raw']['name'])->toBe('Joe Smith')
            ->and($result['rows'][0]['row_type'])->toBe('contact');
    });

    it('treats empty header columns as droppable and ignores empty values', function (): void {
        // Header with junk-only column "!!!" canonicalises to '' and is skipped (line 134).
        $csv = "name,!!!,city\nFoo,,Detroit\n";

        $result = (new ParseDealershipImportCsv)(tmpCsv($csv));

        expect($result['rows'])->toHaveCount(1)
            ->and($result['rows'][0]['raw']['name'])->toBe('Foo')
            ->and($result['rows'][0]['raw']['city'])->toBe('Detroit');
    });

    it('passes through unknown but non-empty canonical headers', function (): void {
        $csv = "name,custom_field\nFoo,Bar\n";

        $result = (new ParseDealershipImportCsv)(tmpCsv($csv));

        expect($result['rows'][0]['raw']['custom_field'] ?? null)->toBe('Bar');
    });

    it('falls back to dealership row_type when row_type column is missing', function (): void {
        $csv = "name\nPrime Motors\n";

        $result = (new ParseDealershipImportCsv)(tmpCsv($csv));

        expect($result['rows'][0]['row_type'])->toBe('dealership');
    });

    it('honours an explicit row_type column even when contact aliases are present', function (): void {
        $csv = "row_type,name,email,companyName\nstore,Branch,b@x.com,Parent\n";

        $result = (new ParseDealershipImportCsv)(tmpCsv($csv));

        expect($result['rows'][0]['row_type'])->toBe('store');
    });
});
