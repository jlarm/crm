<?php

namespace App\Filament\Resources\DealerEmailTemplateResource\RelationManagers;

use App\Enum\ReminderFrequency;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables;
use Filament\Tables\Table;

class DealerEmailsRelationManager extends RelationManager
{
    protected static string $relationship = 'dealerEmails';

    protected static ?string $title = 'Emails Using This Template';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['dealership', 'user']))
            ->columns([
                Tables\Columns\TextColumn::make('dealership.name')
                    ->label('Dealership')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Created By')
                    ->sortable(),
                Tables\Columns\TextColumn::make('frequency')
                    ->badge()
                    ->color(fn (ReminderFrequency $state): string => match ($state) {
                        ReminderFrequency::Immediate => 'success',
                        ReminderFrequency::Once => 'danger',
                        ReminderFrequency::Daily => 'info',
                        ReminderFrequency::Weekly => 'warning',
                        ReminderFrequency::Monthly => 'primary',
                        ReminderFrequency::BiMonthly => 'secondary',
                        ReminderFrequency::Quarterly => 'gray',
                        ReminderFrequency::Yearly => 'slate',
                    }),
                Tables\Columns\TextColumn::make('last_sent')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('next_send_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('paused')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('frequency')
                    ->options(ReminderFrequency::class),
                Tables\Filters\TernaryFilter::make('paused'),
            ])
            ->actions([
                Tables\Actions\Action::make('view_dealership')
                    ->label('View Dealership')
                    ->icon('heroicon-o-building-office-2')
                    ->url(fn ($record) => \App\Filament\Resources\DealershipResource::getUrl('edit', ['record' => $record->dealership])),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
