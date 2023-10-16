<?php

namespace App\Filament\Resources\DealershipResource\Pages;

use App\Filament\Resources\DealershipResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDealership extends CreateRecord
{
    protected static string $resource = DealershipResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->user()->id;

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->users()->attach(auth()->user()->id);
    }
}
