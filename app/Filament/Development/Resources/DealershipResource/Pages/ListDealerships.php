<?php

declare(strict_types=1);

namespace App\Filament\Development\Resources\DealershipResource\Pages;

use App\Filament\Development\Resources\DealershipResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDealerships extends ListRecords
{
    protected static string $resource = DealershipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
