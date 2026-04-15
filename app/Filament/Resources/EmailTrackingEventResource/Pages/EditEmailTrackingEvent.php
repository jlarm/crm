<?php

declare(strict_types=1);

namespace App\Filament\Resources\EmailTrackingEventResource\Pages;

use App\Filament\Resources\EmailTrackingEventResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditEmailTrackingEvent extends EditRecord
{
    protected static string $resource = EmailTrackingEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
