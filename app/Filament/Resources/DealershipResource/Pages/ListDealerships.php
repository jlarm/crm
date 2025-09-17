<?php

declare(strict_types=1);

namespace App\Filament\Resources\DealershipResource\Pages;

use App\Filament\Resources\DealershipResource;
use App\Models\Dealership;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class ListDealerships extends ListRecords
{
    protected static string $resource = DealershipResource::class;

    public function getTitle(): string
    {
        return 'All Dealerships'; // Custom page title
    }

    public function getStats(): array
    {
        return [
            Stat::make('Active', Dealership::where('user_id', auth()->id())->where('status', 'active')->count()),
            Stat::make('Inactive', Dealership::where('user_id', auth()->id())->where('status', 'inactive')->count()),
            Stat::make('Pending', Dealership::where('user_id', auth()->id())->where('status', 'pending')->count()),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'active' => Tab::make('Active')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'active')),
            'inactive' => Tab::make('Inactive')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'inactive')),
            'imported' => Tab::make('Imported')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'imported')),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            DealershipResource\Widgets\DealerOverview::class,
        ];
    }
}
