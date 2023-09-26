<?php

namespace App\Filament\Widgets;

use App\Models\Dealership;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DealershipStatusOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Active', Dealership::where('status', 'active')->count()),
            Stat::make('Inactive', Dealership::where('status', 'inactive')->count()),
            Stat::make('Pending', Dealership::where('status', 'pending')->count()),
        ];
    }
}
