<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enum\ReminderFrequency;
use App\Filament\Resources\DealerEmailResource\Pages\CreateDealerEmail;
use App\Filament\Resources\DealerEmailResource\Pages\EditDealerEmail;
use App\Filament\Resources\DealerEmailResource\Pages\ListDealerEmails;
use App\Models\DealerEmail;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class DealerEmailResource extends Resource
{
    protected static ?string $model = DealerEmail::class;

    protected static ?string $navigationLabel = 'Current Emails';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-envelope';

    protected static string|UnitEnum|null $navigationGroup = 'Email';

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
                TextInput::make('subject')
                    ->columnSpanFull()
                    ->required()
                    ->maxLength(255),
                RichEditor::make('message')->columnSpanFull(),
                Select::make('frequency')
                    ->options(ReminderFrequency::class)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                DealerEmail::query()
                    ->where('user_id', auth()->id())
                    ->orderBy('last_sent', 'desc')
            )
            ->columns([
                TextColumn::make('dealership.name')->sortable(),
                TextColumn::make('recipients')->sortable(),
                TextColumn::make('frequency')->sortable(),
                TextColumn::make('last_sent')
                    ->date()
                    ->sortable(),
                ToggleColumn::make('paused'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('view_dealership_emails')
                    ->label('View')
                    ->url(fn (DealerEmail $record): string => DealershipResource::getUrl('emails', ['record' => $record->dealership])
                    ),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('toggle_paused')
                        ->label('Toggle Paused')
                        ->icon('heroicon-o-pause')
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                $record->update(['paused' => ! $record->paused]);
                            }
                        })
                        ->deselectRecordsAfterCompletion()
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Paused status toggled')
                                ->body('The paused status has been toggled for the selected emails.')
                        ),
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
            'index' => ListDealerEmails::route('/'),
            'create' => CreateDealerEmail::route('/create'),
            'edit' => EditDealerEmail::route('/{record}/edit'),
        ];
    }
}
