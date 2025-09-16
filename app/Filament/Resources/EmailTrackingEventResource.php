<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmailTrackingEventResource\Pages;
use App\Models\EmailTrackingEvent;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EmailTrackingEventResource extends Resource
{
    protected static ?string $model = EmailTrackingEvent::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'Email Tracking';

    protected static ?string $navigationGroup = 'Email';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('event_type')
                    ->options([
                        'delivered' => 'Delivered',
                        'opened' => 'Opened',
                        'clicked' => 'Clicked',
                        'bounced' => 'Bounced',
                        'complained' => 'Complained',
                        'unsubscribed' => 'Unsubscribed',
                    ])
                    ->required()
                    ->disabled(),
                Forms\Components\TextInput::make('recipient_email')
                    ->email()
                    ->required()
                    ->disabled(),
                Forms\Components\TextInput::make('message_id')
                    ->required()
                    ->disabled(),
                Forms\Components\TextInput::make('url')
                    ->disabled(),
                Forms\Components\DateTimePicker::make('event_timestamp')
                    ->required()
                    ->disabled()
                    ->formatStateUsing(fn ($state) => $state?->inUserTimezone()),
                Forms\Components\Textarea::make('user_agent')
                    ->disabled(),
                Forms\Components\TextInput::make('ip_address')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('event_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'delivered' => 'success',
                        'opened' => 'primary',
                        'clicked' => 'warning',
                        'bounced' => 'danger',
                        'complained' => 'danger',
                        'unsubscribed' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('sentEmail.subject')
                    ->label('Email Subject')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('recipient_email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sentEmail.dealership.name')
                    ->label('Dealership')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sentEmail.user.name')
                    ->label('Sent By')
                    ->sortable(),
                Tables\Columns\TextColumn::make('url')
                    ->limit(50)
                    ->tooltip(fn (?string $state): ?string => $state),
                Tables\Columns\TextColumn::make('event_timestamp')
                    ->dateTime()
                    ->formatStateUsing(fn ($state) => $state?->inUserTimezone()->format('M j, Y g:i A T'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event_type')
                    ->options([
                        'delivered' => 'Delivered',
                        'opened' => 'Opened',
                        'clicked' => 'Clicked',
                        'bounced' => 'Bounced',
                        'complained' => 'Complained',
                        'unsubscribed' => 'Unsubscribed',
                    ]),
                Tables\Filters\Filter::make('user_emails')
                    ->label('My Emails Only')
                    ->query(fn (Builder $query): Builder => $query->whereHas('sentEmail.dealership.users', fn ($q) => $q->where('user_id', auth()->id())
                    )
                    )
                    ->default(),
                Tables\Filters\Filter::make('last_24_hours')
                    ->query(fn (Builder $query): Builder => $query->where('event_timestamp', '>=', now()->subDay())
                    ),
                Tables\Filters\Filter::make('last_week')
                    ->query(fn (Builder $query): Builder => $query->where('event_timestamp', '>=', now()->subWeek())
                    ),
                Tables\Filters\SelectFilter::make('dealership')
                    ->relationship('sentEmail.dealership', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('event_timestamp', 'desc')
            ->poll('30s'); // Auto-refresh every 30 seconds
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmailTrackingEvents::route('/'),
            'view' => Pages\ViewEmailTrackingEvent::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Prevent manual creation
    }

    public static function canEdit($record): bool
    {
        return false; // Prevent editing
    }

    public static function canDelete($record): bool
    {
        return false; // Prevent deletion
    }
}
