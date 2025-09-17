<?php

declare(strict_types=1);

namespace App\Filament\Resources\DealerEmailResource\Pages;

use App\Filament\Resources\DealerEmailResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDealerEmail extends EditRecord
{
    protected static string $resource = DealerEmailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
