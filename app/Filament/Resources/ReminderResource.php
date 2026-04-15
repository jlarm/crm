<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enum\ReminderFrequency;
use App\Filament\Resources\ReminderResource\Pages\CreateReminder;
use App\Filament\Resources\ReminderResource\Pages\EditReminder;
use App\Filament\Resources\ReminderResource\Pages\ListReminders;
use App\Models\Reminder;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
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
                TextInput::make('title')
                    ->helperText('A short description for the reminder')
                    ->columnSpanFull()
                    ->required(),
                RichEditor::make('message')
                    ->disableToolbarButtons(['attachFiles', 'codeBlock'])
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
                Reminder::whereBelongsTo(auth()->user())->where('dev_rel', null)
            )
            ->columns([
                TextColumn::make('title'),
                TextColumn::make('sending_frequency'),
                TextColumn::make('start_date')->date(),
                TextColumn::make('last_sent')->date(),
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
