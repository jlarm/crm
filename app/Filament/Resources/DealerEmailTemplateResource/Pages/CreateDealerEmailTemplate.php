<?php

namespace App\Filament\Resources\DealerEmailTemplateResource\Pages;

use App\Filament\Resources\DealerEmailTemplateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDealerEmailTemplate extends CreateRecord
{
    protected static string $resource = DealerEmailTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
