<?php

namespace App\Filament\Widgets;

use App\Models\Dealership;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PendingDealerships extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Dealership::query()
                    ->where('status', 'pending')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->description(fn (Dealership $dealership): string => $dealership->progresses()->latest()->first()->details ?? ''),
                Tables\Columns\TextColumn::make('phone'),
                Tables\Columns\TextColumn::make('state'),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (Dealership $dealership) => route('filament.admin.resources.dealerships.edit', $dealership)),
            ]);
    }
}
