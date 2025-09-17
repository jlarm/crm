<?php

declare(strict_types=1);

namespace App\Filament\Resources\DealershipResource\Pages;

use App\Filament\Resources\DealershipResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDealership extends EditRecord
{
    protected static string $resource = DealershipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
