<?php

namespace App\Filament\Resources\DealershipResource\Pages;

use App\Filament\Resources\DealershipResource;
use Filament\Resources\Pages\CreateRecord;
use Spatie\MailcoachSdk\Facades\Mailcoach;

class CreateDealership extends CreateRecord
{
    protected static string $resource = DealershipResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->user()->id;

        return $data;
    }

    protected function getListType(): string
    {
        $types = [
            'Automotive' => 'f694f7fd-dbb9-489d-bced-03e2fbee78af',
            'RV' => '2d97d6ea-90a0-4b49-90df-980a258884b2',
            'Motorsports' => 'd2a68b06-08e4-4e76-a714-151e07a5a907',
            'Maritime' => '59c46030-5429-4ffd-a192-42926b9b17eb',
        ];

        return $types[$this->record->type];
    }

    protected function afterCreate(): void
    {
        $this->record->users()->attach(auth()->user()->id);

        if ($this->record->email) {
            $tags = [];
            $tags[] = 'Dealership';
            $tags[] = auth()->user()->name;

            $sub = Mailcoach::createSubscriber(
                emailListUuid: $this->getListType(),
                attributes: [
                    'first_name' => $this->record->name,
                    'email' => $this->record->email,
                    'tags' => $tags,
                ]
            );
        }
    }
}
