<?php

namespace App\Filament\Resources\DealershipResource\Widgets;

use App\Models\Dealership;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DealerOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Active', Dealership::where('status', 'active')->count()),
            Stat::make('Inactive', Dealership::where('status', 'inactive')->count()),
        ];
    }
}
