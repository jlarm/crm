<?php

namespace App\Filament\Widgets;

use App\Models\DealerEmail;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class UpcomingEmails extends BaseWidget
{
    use HasWidgetShield;
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                DealerEmail::query()
                    ->with(['user', 'dealership'])
                    ->where('frequency', '>', 0)
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.name'),
                Tables\Columns\TextColumn::make('dealership.name'),
                Tables\Columns\TextColumn::make('recipients'),
                Tables\Columns\TextColumn::make('next_send_date')->date(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->label('Consultant')
                    ->preload(),
                Tables\Filters\SelectFilter::make('next_send_date')
                    ->options([
                        'seven_days' => 'Next 7 days',
                        'thirty_days' => 'Next 30 days',
                        'sixty_days' => 'Next 60 days',
                        'ninety_days' => 'Next 90 days',
                    ])
                    ->default('seven_days')
                    ->query(function (Builder $query, array $data) {
                        switch ($data['value']) {
                            case 'seven_days':
                                $query->whereBetween('next_send_date', [now(), now()->addDays(7)]);
                                break;
                            case 'thirty_days':
                                $query->whereBetween('next_send_date', [now(), now()->addDays(30)]);
                                break;
                            case 'sixty_days':
                                $query->whereBetween('next_send_date', [now(), now()->addDays(60)]);
                                break;
                            case 'ninety_days':
                                $query->whereBetween('next_send_date', [now(), now()->addDays(90)]);
                                break;
                        }
                    })
                ->preload()
            ]);
    }
}
