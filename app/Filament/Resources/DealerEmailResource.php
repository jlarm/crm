<?php

namespace App\Filament\Resources;

use App\Enum\ReminderFrequency;
use App\Filament\Resources\DealerEmailResource\Pages;
use App\Filament\Resources\DealerEmailResource\RelationManagers;
use App\Models\DealerEmail;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DealerEmailResource extends Resource
{
    protected static ?string $model = DealerEmail::class;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('subject')
                    ->columnSpanFull()
                    ->required()
                    ->maxLength(255),
                Forms\Components\RichEditor::make('message')->columnSpanFull(),
                Forms\Components\Select::make('frequency')
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
            )
            ->columns([
                Tables\Columns\TextColumn::make('dealership.name')->sortable(),
                Tables\Columns\TextColumn::make('subject'),
                Tables\Columns\TextColumn::make('frequency'),
                Tables\Columns\TextColumn::make('last_sent')->date()
            ])
            ->filters([
                //
            ])
            ->actions([
//                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListDealerEmails::route('/'),
            'create' => Pages\CreateDealerEmail::route('/create'),
            'edit' => Pages\EditDealerEmail::route('/{record}/edit'),
        ];
    }
}
