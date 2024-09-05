<?php

namespace App\Filament\Resources;

use App\Enum\ReminderFrequency;
use App\Filament\Resources\ReminderResource\Pages;
use App\Filament\Resources\ReminderResource\RelationManagers;
use App\Models\Reminder;
use Faker\Provider\Text;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReminderResource extends Resource
{
    protected static ?string $model = Reminder::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->helperText('A short description for the reminder')
                    ->columnSpanFull()
                    ->required(),
                Forms\Components\RichEditor::make('message')
                    ->disableToolbarButtons(['attachFiles', 'codeBlock'])
                    ->columnSpanFull()
                    ->required(),
                Forms\Components\DatePicker::make('start_date')
                    ->required(),
                Forms\Components\Select::make('sending_frequency')
                    ->options(ReminderFrequency::class)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                Reminder::whereBelongsTo(auth()->user())->where('dev_rel', null)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title'),
                Tables\Columns\TextColumn::make('sending_frequency'),
                Tables\Columns\TextColumn::make('start_date')->date(),
                Tables\Columns\TextColumn::make('last_sent')->date(),
                Tables\Columns\ToggleColumn::make('pause')
                    ->label('Status')
                    ->offIcon('heroicon-o-play')
                    ->onIcon('heroicon-s-pause')
                    ->offColor('success')
                    ->onColor('warning'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListReminders::route('/'),
            'create' => Pages\CreateReminder::route('/create'),
            'edit' => Pages\EditReminder::route('/{record}/edit'),
        ];
    }
}
