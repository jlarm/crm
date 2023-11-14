<?php

namespace App\Filament\Widgets;

use App\Models\Dealership;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DealershipStatusOverview extends BaseWidget
{
    protected $user;
    protected function getStats(): array
    {
        $this->user = auth()->user();
        return [
            Stat::make('Active', $this->user->dealerships()->where('status', 'active')->count()),
            Stat::make('Inactive', $this->user->dealerships()->where('status', 'inactive')->count()),
            Stat::make('Imported', $this->user->dealerships()->where('status', 'imported')->count()),
        ];
    }
}
