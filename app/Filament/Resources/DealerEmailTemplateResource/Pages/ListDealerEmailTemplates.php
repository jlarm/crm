<?php

namespace App\Filament\Resources\DealerEmailTemplateResource\Pages;

use App\Filament\Resources\DealerEmailTemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDealerEmailTemplates extends ListRecords
{
    protected static string $resource = DealerEmailTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
