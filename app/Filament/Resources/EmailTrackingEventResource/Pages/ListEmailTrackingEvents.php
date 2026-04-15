<?php

declare(strict_types=1);

namespace App\Filament\Resources\EmailTrackingEventResource\Pages;

use App\Filament\Resources\EmailTrackingEventResource;
use App\Filament\Resources\EmailTrackingEventResource\Widgets\EmailAnalyticsWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmailTrackingEvents extends ListRecords
{
    protected static string $resource = EmailTrackingEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            EmailAnalyticsWidget::class,
        ];
    }
}
