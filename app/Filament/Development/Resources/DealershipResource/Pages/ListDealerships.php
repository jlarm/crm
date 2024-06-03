<?php

namespace App\Filament\Development\Resources\DealershipResource\Pages;

use App\Filament\Development\Resources\DealershipResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDealerships extends ListRecords
{
    protected static string $resource = DealershipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
