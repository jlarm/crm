<?php

declare(strict_types=1);

namespace App\Filament\Development\Resources\ReminderResource\Pages;

use App\Filament\Development\Resources\ReminderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReminders extends ListRecords
{
    protected static string $resource = ReminderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
