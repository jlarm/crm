<?php

declare(strict_types=1);

namespace App\Filament\Development\Resources;

use App\Enum\ReminderFrequency;
use App\Filament\Development\Resources\ReminderResource\Pages\CreateReminder;
use App\Filament\Development\Resources\ReminderResource\Pages\EditReminder;
use App\Filament\Development\Resources\ReminderResource\Pages\ListReminders;
use App\Models\Reminder;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class ReminderResource extends Resource
{
    protected static ?string $model = Reminder::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('dev_rel')->default(true),
                Select::make('user_id')
                    ->label('Consultant')
                    ->columnSpanFull()
                    ->options(User::all()->pluck('name', 'id')),
                TextInput::make('title')
                    ->helperText('A short description for the reminder')
                    ->columnSpanFull()
                    ->required(),
                RichEditor::make('message')
                    ->columnSpanFull()
                    ->required(),
                DatePicker::make('start_date')
                    ->required(),
                Select::make('sending_frequency')
                    ->options(ReminderFrequency::class)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                Reminder::where('dev_rel', true)
            )
            ->columns([
                TextColumn::make('title'),
                TextColumn::make('user.name')->label('Consultant'),
                ToggleColumn::make('pause')
                    ->label('Status')
                    ->offIcon('heroicon-o-play')
                    ->onIcon('heroicon-s-pause')
                    ->offColor('success')
                    ->onColor('warning'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => ListReminders::route('/'),
            'create' => CreateReminder::route('/create'),
            'edit' => EditReminder::route('/{record}/edit'),
        ];
    }
}
