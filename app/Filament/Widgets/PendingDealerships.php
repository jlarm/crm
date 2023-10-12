<?php

namespace App\Filament\Widgets;

use App\Models\Dealership;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
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
                    ->where('user_id', auth()->id())
            )
            ->columns([
                TextColumn::make('name')
                    ->description(fn (Dealership $dealership): string => $dealership->city . ', ' . $dealership->state),
                TextColumn::make('phone'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        'pending' => 'warning',
                    }),
                TextColumn::make('rating')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'hot' => 'success',
                        'warm' => 'warning',
                        'cold' => 'primary',
                    }),
                TextColumn::make('stores_count')
                    ->counts('stores')
                    ->label('Stores'),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (Dealership $dealership) => route('filament.admin.resources.dealerships.edit', $dealership)),
            ]);
    }
}
