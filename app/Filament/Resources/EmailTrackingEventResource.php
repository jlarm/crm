<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\EmailTrackingEventResource\Pages\ListEmailTrackingEvents;
use App\Filament\Resources\EmailTrackingEventResource\Pages\ViewEmailTrackingEvent;
use App\Models\EmailTrackingEvent;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class EmailTrackingEventResource extends Resource
{
    protected static ?string $model = EmailTrackingEvent::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'Email Tracking';

    protected static string|UnitEnum|null $navigationGroup = 'Email';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('event_type')
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
                TextInput::make('recipient_email')
                    ->email()
                    ->required()
                    ->disabled(),
                TextInput::make('message_id')
                    ->required()
                    ->disabled(),
                TextInput::make('url')
                    ->disabled(),
                DateTimePicker::make('event_timestamp')
                    ->required()
                    ->disabled()
                    ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->inUserTimezone() : null),
                Textarea::make('user_agent')
                    ->disabled(),
                TextInput::make('ip_address')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('event_type')
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
                TextColumn::make('sentEmail.subject')
                    ->label('Email Subject')
                    ->searchable()
                    ->limit(30),
                TextColumn::make('recipient_email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sentEmail.dealership.name')
                    ->label('Dealership')
                    ->limit(30)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sentEmail.user.name')
                    ->label('Sent By')
                    ->sortable(),
                TextColumn::make('event_timestamp')
                    ->dateTime()
                    ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->inUserTimezone()->format('M j, Y g:i A T') : null)
                    ->sortable(),
                TextColumn::make('ip_address')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('event_type')
                    ->options([
                        'delivered' => 'Delivered',
                        'opened' => 'Opened',
                        'clicked' => 'Clicked',
                        'bounced' => 'Bounced',
                        'complained' => 'Complained',
                        'unsubscribed' => 'Unsubscribed',
                    ]),
                Filter::make('user_emails')
                    ->label('My Emails Only')
                    ->query(fn (Builder $query): Builder => $query->whereHas('sentEmail.dealership.users', fn ($q) => $q->where('user_id', auth()->id())
                    )
                    )
                    ->default(),
                Filter::make('last_24_hours')
                    ->query(fn (Builder $query): Builder => $query->where('event_timestamp', '>=', now()->subDay())
                    ),
                Filter::make('last_week')
                    ->query(fn (Builder $query): Builder => $query->where('event_timestamp', '>=', now()->subWeek())
                    ),
                SelectFilter::make('dealership')
                    ->relationship('sentEmail.dealership', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('event_timestamp', 'desc')
            ->poll('30s'); // Auto-refresh every 30 seconds
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmailTrackingEvents::route('/'),
            'view' => ViewEmailTrackingEvent::route('/{record}'),
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
