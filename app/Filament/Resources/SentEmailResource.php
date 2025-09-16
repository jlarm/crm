<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SentEmailResource\Pages;
use App\Filament\Resources\SentEmailResource\RelationManagers;
use App\Models\SentEmail;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
class SentEmailResource extends Resource
{
    protected static ?string $model = SentEmail::class;

    protected static ?string $navigationGroup = 'Email';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Email Details')
                    ->schema([
                        Forms\Components\TextInput::make('subject')
                            ->label('Subject')
                            ->disabled(),
                        Forms\Components\TextInput::make('recipient')
                            ->label('Recipient')
                            ->disabled(),
                        Forms\Components\TextInput::make('message_id')
                            ->label('Message ID')
                            ->disabled(),
                        Forms\Components\Select::make('user_id')
                            ->label('Sent By')
                            ->relationship('user', 'name')
                            ->disabled(),
                        Forms\Components\Select::make('dealership_id')
                            ->label('Dealership')
                            ->relationship('dealership', 'name')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('created_at')
                            ->label('Sent Date')
                            ->disabled(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Tracking Summary')
                    ->schema([
                        Forms\Components\ViewField::make('tracking_summary')
                            ->label('')
                            ->view('filament.components.tracking-summary'),
                    ]),

                Forms\Components\Section::make('Recent Tracking Events')
                    ->schema([
                        Forms\Components\ViewField::make('recent_events')
                            ->label('')
                            ->view('filament.components.recent-events'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('recipient')
                    ->searchable(),
                Tables\Columns\TextColumn::make('dealership.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->sortable()
                    ->label('Sent By'),
                Tables\Columns\IconColumn::make('opened')
                    ->label('Opened')
                    ->getStateUsing(fn (SentEmail $record): bool => $record->wasOpened())
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('gray'),
                Tables\Columns\IconColumn::make('clicked')
                    ->label('Clicked')
                    ->getStateUsing(fn (SentEmail $record): bool => $record->wasClicked())
                    ->boolean()
                    ->trueColor('warning')
                    ->falseColor('gray'),
                Tables\Columns\IconColumn::make('bounced')
                    ->label('Bounced')
                    ->getStateUsing(fn (SentEmail $record): bool => $record->wasBounced())
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('gray'),
                Tables\Columns\TextColumn::make('created_at')
                    ->sortable()
                    ->dateTime()
                    ->label('Sent'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Sent By')
                    ->relationship('user', 'name'),
                Tables\Filters\Filter::make('opened')
                    ->label('Opened Emails')
                    ->query(fn ($query) => $query->whereHas('trackingEvents', fn($q) => $q->where('event_type', 'opened'))),
                Tables\Filters\Filter::make('clicked')
                    ->label('Clicked Emails')
                    ->query(fn ($query) => $query->whereHas('trackingEvents', fn($q) => $q->where('event_type', 'clicked'))),
                Tables\Filters\Filter::make('bounced')
                    ->label('Bounced Emails')
                    ->query(fn ($query) => $query->whereHas('trackingEvents', fn($q) => $q->where('event_type', 'bounced'))),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListSentEmails::route('/'),
            'create' => Pages\CreateSentEmail::route('/create'),
            'edit' => Pages\EditSentEmail::route('/{record}/edit'),
        ];
    }
}
