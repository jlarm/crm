<?php

declare(strict_types=1);

namespace App\Actions\Dealerships;

use Illuminate\Http\UploadedFile;
use League\Csv\Reader;

final class ParseDealershipImportCsv
{
    /**
     * @var array<string, array<int, string>>
     */
    private const array ALIASES = [
        'row_type' => ['row_type', 'rowtype', 'record_type', 'recordtype'],
        'name' => ['name', 'full_name', 'fullname', 'contact_name', 'contactname'],
        'first_name' => ['first_name', 'firstname', 'first', 'given_name', 'givenname'],
        'last_name' => ['last_name', 'lastname', 'last', 'surname', 'family_name', 'familyname'],
        'email' => ['email', 'e_mail', 'email_address', 'emailaddress'],
        'phone' => ['phone', 'phone_number', 'phonenumber', 'telephone', 'tel', 'mobile', 'cell'],
        'position' => ['position', 'title', 'job_title', 'jobtitle', 'role', 'job'],
        'linkedin_link' => ['linkedin_link', 'linkedinlink', 'linkedin', 'linkedin_url', 'linkedinurl'],
        'dealership_ref' => ['dealership_ref', 'dealershipref', 'company', 'company_name', 'companyname', 'dealership', 'dealership_name', 'dealershipname', 'account', 'account_name', 'accountname'],
        'address' => ['address', 'street', 'street_address', 'streetaddress', 'address_line_1', 'addressline1'],
        'city' => ['city', 'town'],
        'state' => ['state', 'st', 'province'],
        'zip_code' => ['zip_code', 'zipcode', 'zip', 'postal_code', 'postalcode', 'postal', 'postcode'],
        'type' => ['type', 'business_type', 'businesstype', 'dealership_type', 'dealershiptype', 'account_type', 'accounttype'],
        'status' => ['status', 'account_status', 'accountstatus'],
        'rating' => ['rating', 'score', 'priority'],
        'notes' => ['notes', 'note', 'comments', 'comment', 'description'],
        'primary_contact' => ['primary_contact', 'primarycontact', 'primary', 'is_primary', 'isprimary'],
        'user_emails' => ['user_emails', 'useremails', 'consultants', 'consultant_emails', 'consultantemails', 'assigned_users', 'assignedusers', 'assigned_to', 'assignedto', 'owner', 'owner_email', 'owneremail', 'owners'],
        'current_solution_name' => ['current_solution_name', 'currentsolutionname', 'solution', 'current_solution', 'currentsolution', 'crm', 'current_crm'],
        'current_solution_use' => ['current_solution_use', 'currentsolutionuse', 'solution_use', 'solutionuse'],
    ];

    /**
     * @return array{rows: array<int, array{line: int, row_type: string, raw: array<string, string|null>}>, parse_errors: array<int, array{line: int, message: string}>}
     */
    public function __invoke(UploadedFile $file): array
    {
        $reader = Reader::createFromPath($file->getRealPath());
        $reader->setHeaderOffset(0);

        $rawHeaders = $reader->getHeader();
        $canonicalHeaders = array_map(
            $this->canonicalize(...),
            $rawHeaders
        );

        $hasRowType = in_array('row_type', $canonicalHeaders, true);
        $hasEmail = in_array('email', $canonicalHeaders, true);
        $hasParentRef = in_array('dealership_ref', $canonicalHeaders, true);

        $defaultRowType = ($hasParentRef && $hasEmail) ? 'contact' : 'dealership';

        $rows = [];
        $parseErrors = [];

        foreach ($reader->getRecords() as $offset => $record) {
            $line = (int) $offset + 1;
            /** @var array<string, mixed> $record */
            $normalized = $this->normalizeRecord($record);

            if (($normalized['name'] ?? null) === null) {
                $combined = mb_trim(
                    ($normalized['first_name'] ?? '').' '.($normalized['last_name'] ?? '')
                );
                if ($combined !== '') {
                    $normalized['name'] = $combined;
                }
            }

            $rowType = $hasRowType
                ? mb_strtolower((string) ($normalized['row_type'] ?? $defaultRowType))
                : $defaultRowType;

            if (! in_array($rowType, ['dealership', 'store', 'contact'], true)) {
                $parseErrors[] = [
                    'line' => $line,
                    'message' => sprintf('Unknown row_type "%s". Expected dealership, store, or contact.', $rowType),
                ];

                continue;
            }

            $rows[] = [
                'line' => $line,
                'row_type' => $rowType,
                'raw' => $normalized,
            ];
        }

        return [
            'rows' => $rows,
            'parse_errors' => $parseErrors,
        ];
    }

    /**
     * @param  array<string, mixed>  $record
     * @return array<string, string|null>
     */
    private function normalizeRecord(array $record): array
    {
        $normalized = [];
        foreach ($record as $rawKey => $value) {
            $canonical = $this->canonicalize((string) $rawKey);
            if ($canonical === null) {
                continue;
            }

            $value = is_string($value) ? mb_trim($value) : $value;
            if ($value === '' || $value === null) {
                $normalized[$canonical] = null;
            } elseif (is_scalar($value)) {
                $normalized[$canonical] = (string) $value;
            } else {
                $normalized[$canonical] = null;
            }
        }

        return $normalized;
    }

    private function canonicalize(string $header): ?string
    {
        $clean = mb_strtolower(mb_trim($header));
        $clean = preg_replace('/[\s\-]+/', '_', $clean) ?? $clean;
        $clean = preg_replace('/[^a-z0-9_]/', '', $clean) ?? $clean;

        if ($clean === '') {
            return null;
        }

        foreach (self::ALIASES as $canonical => $aliases) {
            if (in_array($clean, $aliases, true)) {
                return $canonical;
            }
        }

        return $clean;
    }
}
