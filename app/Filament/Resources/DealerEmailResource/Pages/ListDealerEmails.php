<?php

namespace App\Filament\Resources\DealerEmailResource\Pages;

use App\Filament\Resources\DealerEmailResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDealerEmails extends ListRecords
{
    protected static string $resource = DealerEmailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
