<?php

declare(strict_types=1);

namespace App\Filament\Resources\DealerEmailTemplateResource\RelationManagers;

use App\Enum\ReminderFrequency;
use App\Filament\Resources\DealershipResource;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DealerEmailsRelationManager extends RelationManager
{
    protected static string $relationship = 'dealerEmails';

    protected static ?string $title = 'Emails Using This Template';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['dealership', 'user']))
            ->columns([
                TextColumn::make('dealership.name')
                    ->label('Dealership')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Created By')
                    ->sortable(),
                TextColumn::make('frequency')
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
                TextColumn::make('last_sent')
                    ->date()
                    ->sortable(),
                TextColumn::make('next_send_date')
                    ->date()
                    ->sortable(),
                IconColumn::make('paused')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('frequency')
                    ->options(ReminderFrequency::class),
                TernaryFilter::make('paused'),
            ])
            ->recordActions([
                Action::make('view_dealership')
                    ->label('View Dealership')
                    ->icon('heroicon-o-building-office-2')
                    ->url(fn ($record): string => DealershipResource::getUrl('edit', ['record' => $record->dealership])),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
