<?php

namespace App\Filament\Resources\DealershipResource\Pages;

use App\Filament\Resources\DealershipResource;
use App\Models\Dealership;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ListDealerships extends ListRecords
{
    protected static string $resource = DealershipResource::class;

    public function getStats(): array
    {
        return [
            Stat::make('Active', Dealership::where('user_id', auth()->id())->where('status', 'active')->count()),
            Stat::make('Inactive', Dealership::where('user_id', auth()->id())->where('status', 'inactive')->count()),
            Stat::make('Pending', Dealership::where('user_id', auth()->id())->where('status', 'pending')->count()),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
