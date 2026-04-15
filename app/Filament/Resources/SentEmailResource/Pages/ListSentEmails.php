<?php

declare(strict_types=1);

namespace App\Filament\Resources\SentEmailResource\Pages;

use App\Filament\Resources\SentEmailResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListSentEmails extends ListRecords
{
    protected static string $resource = SentEmailResource::class;

    public function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user', 'dealership', 'trackingEvents']);
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
