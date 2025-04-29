<?php

namespace App\Filament\Widgets;

use App\Models\Dealership;
use App\Tables\Columns\LatestProgress;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class MyDealerships extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->description('A list of all of your dealerships.')
            ->query(
                Dealership::query()
                    ->whereHas('users', fn ($query) => $query->where('id', auth()->user()->id))
            )
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->description(fn (Dealership $dealership): string => $dealership->city.', '.$dealership->state),
                TextColumn::make('phone'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        'imported' => 'primary',
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
            ->filters([
                Tables\Filters\Filter::make('dealer_group')
                    ->label('Dealer Groups')
                    ->query(fn (Builder $query): Builder => $query->has('stores')),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ]),
                Tables\Filters\SelectFilter::make('rating')
                    ->options([
                        'hot' => 'Hot',
                        'warm' => 'Warm',
                        'cold' => 'Cold',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (Dealership $dealership) => route('filament.admin.resources.dealerships.edit', $dealership)),
            ]);
    }
}
