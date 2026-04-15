<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\SentEmailResource\Pages\CreateSentEmail;
use App\Filament\Resources\SentEmailResource\Pages\EditSentEmail;
use App\Filament\Resources\SentEmailResource\Pages\ListSentEmails;
use App\Models\SentEmail;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class SentEmailResource extends Resource
{
    protected static ?string $model = SentEmail::class;

    protected static string|UnitEnum|null $navigationGroup = 'Email';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Email Details')
                    ->schema([
                        TextInput::make('subject')
                            ->label('Subject')
                            ->disabled(),
                        TextInput::make('recipient')
                            ->label('Recipient')
                            ->disabled(),
                        TextInput::make('message_id')
                            ->label('Message ID')
                            ->disabled(),
                        Select::make('user_id')
                            ->label('Sent By')
                            ->relationship('user', 'name')
                            ->disabled(),
                        Select::make('dealership_id')
                            ->label('Dealership')
                            ->relationship('dealership', 'name')
                            ->disabled(),
                        DateTimePicker::make('created_at')
                            ->label('Sent Date')
                            ->disabled()
                            ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->inUserTimezone() : null),
                    ])
                    ->columns(2),

                Section::make('Tracking Summary')
                    ->schema([
                        ViewField::make('tracking_summary')
                            ->label('')
                            ->view('filament.components.tracking-summary'),
                    ]),

                Section::make('Recent Tracking Events')
                    ->schema([
                        ViewField::make('recent_events')
                            ->label('')
                            ->view('filament.components.recent-events'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('recipient')
                    ->searchable(),
                TextColumn::make('dealership.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->sortable()
                    ->label('Sent By'),
                IconColumn::make('opened')
                    ->label('Opened')
                    ->getStateUsing(fn (SentEmail $record): bool => $record->wasOpened())
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('gray'),
                IconColumn::make('clicked')
                    ->label('Clicked')
                    ->getStateUsing(fn (SentEmail $record): bool => $record->wasClicked())
                    ->boolean()
                    ->trueColor('warning')
                    ->falseColor('gray'),
                IconColumn::make('bounced')
                    ->label('Bounced')
                    ->getStateUsing(fn (SentEmail $record): bool => $record->wasBounced())
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('gray'),
                TextColumn::make('created_at')
                    ->sortable()
                    ->dateTime()
                    ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->inUserTimezone()->format('M j, Y g:i A T') : null)
                    ->label('Sent'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('user_id')
                    ->label('Sent By')
                    ->relationship('user', 'name'),
                Filter::make('opened')
                    ->label('Opened Emails')
                    ->query(fn ($query) => $query->whereHas('trackingEvents', fn ($q) => $q->where('event_type', 'opened'))),
                Filter::make('clicked')
                    ->label('Clicked Emails')
                    ->query(fn ($query) => $query->whereHas('trackingEvents', fn ($q) => $q->where('event_type', 'clicked'))),
                Filter::make('bounced')
                    ->label('Bounced Emails')
                    ->query(fn ($query) => $query->whereHas('trackingEvents', fn ($q) => $q->where('event_type', 'bounced'))),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSentEmails::route('/'),
            'create' => CreateSentEmail::route('/create'),
            'edit' => EditSentEmail::route('/{record}/edit'),
        ];
    }
}
