<?php

declare(strict_types=1);

namespace App\Exports;

use Illuminate\Database\Eloquent\Collection;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class AdvancedDealershipExport extends ExcelExport
{
    public array $exportData = [];

    public function __construct()
    {
        parent::__construct('dealerships');
    }

    public function setExportData(array $data): void
    {
        $this->exportData = $data;
    }

    public function collection()
    {
        $records = parent::collection();

        if (! ($records instanceof Collection)) {
            return collect();
        }

        $includeContacts = $this->exportData['include_contacts'] ?? false;
        $transformedRecords = collect();

        foreach ($records as $record) {
            if (! $includeContacts || $record->contacts->isEmpty()) {
                // Single row for dealership only
                $transformedRecords->push($this->mapDealershipRow($record));
            } else {
                // Multiple rows - one for each contact
                foreach ($record->contacts as $contact) {
                    $row = $this->mapDealershipRow($record);

                    // Add contact data
                    $contactFields = $this->exportData['contact_fields'] ?? [];
                    foreach ($contactFields as $field) {
                        $columnKey = match ($field) {
                            'contact_name' => 'contact_name',
                            'contact_email' => 'contact_email',
                            'contact_phone' => 'contact_phone',
                            'contact_title' => 'contact_title',
                            default => $field,
                        };

                        $row[$columnKey] = match ($field) {
                            'contact_name' => $contact->name,
                            'contact_email' => $contact->email,
                            'contact_phone' => $contact->phone,
                            'contact_title' => $contact->title,
                            default => $contact->{$field} ?? '',
                        };
                    }

                    $transformedRecords->push((object) $row);
                }
            }
        }

        return $transformedRecords;
    }

    private function mapDealershipRow($record): array
    {
        $row = [];
        $selectedFields = $this->exportData['fields'] ?? [];

        foreach ($selectedFields as $field) {
            $row[$field] = match ($field) {
                'name' => $record->name,
                'address' => $record->address,
                'city' => $record->city,
                'state' => $record->state,
                'zip_code' => $record->zip_code,
                'phone' => $record->phone,
                'email' => $record->email,
                'type' => $record->type,
                'status' => $record->status,
                'rating' => $record->rating,
                'current_solution_name' => $record->current_solution_name,
                'in_development' => $record->in_development ? 'Yes' : 'No',
                'consultants' => $record->users->pluck('name')->join(', '),
                'store_count' => $record->stores->count(),
                default => $record->{$field} ?? '',
            };
        }

        return $row;
    }
}
