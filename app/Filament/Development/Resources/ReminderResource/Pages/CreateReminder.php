<?php

declare(strict_types=1);

namespace App\Filament\Development\Resources\ReminderResource\Pages;

use App\Filament\Development\Resources\ReminderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateReminder extends CreateRecord
{
    protected static string $resource = ReminderResource::class;
}
