<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SentEmailResource\Pages;
use App\Filament\Resources\SentEmailResource\RelationManagers;
use App\Models\SentEmail;
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
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->sortable()
                    ->label('Consultant'),
                Tables\Columns\TextColumn::make('dealership.name'),
                Tables\Columns\TextColumn::make('recipient'),
                Tables\Columns\TextColumn::make('created_at')
                    ->sortable()
                    ->date()
                    ->label('Sent'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Consultant')
                    ->relationship('user', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
//                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
//                ]),
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
