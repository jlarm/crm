<?php

namespace App\Filament\Resources\DealerEmailTemplateResource\Pages;

use App\Filament\Resources\DealerEmailTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDealerEmailTemplate extends ViewRecord
{
    protected static string $resource = DealerEmailTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}