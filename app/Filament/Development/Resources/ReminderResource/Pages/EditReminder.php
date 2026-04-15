<?php

declare(strict_types=1);

namespace App\Filament\Development\Resources\ReminderResource\Pages;

use App\Filament\Development\Resources\ReminderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReminder extends EditRecord
{
    protected static string $resource = ReminderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
