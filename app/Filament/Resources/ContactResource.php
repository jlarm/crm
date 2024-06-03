<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactResource\Pages;
use App\Models\Contact;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class ContactResource extends Resource
{
    protected static ?string $model = Contact::class;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model|\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    protected static ?string $navigationIcon = 'heroicon-o-users';

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
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->description(fn (Contact $contact): string => $contact->phone ?? '')
                    ->searchable(),
                Tables\Columns\TextColumn::make('position'),
                Tables\Columns\TextColumn::make('dealership.name')
                    ->description(fn (Contact $contact): string => $contact->dealership->city.', '.$contact->dealership->state)
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                ExportBulkAction::make()->exports([
                    ExcelExport::make()->withColumns([
                        \pxlrbt\FilamentExcel\Columns\Column::make('name'),
                        \pxlrbt\FilamentExcel\Columns\Column::make('phone'),
                        \pxlrbt\FilamentExcel\Columns\Column::make('email'),
                    ]),
                ]),
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
            'index' => Pages\ListContacts::route('/'),
            //            'create' => Pages\CreateContact::route('/create'),
            //            'edit' => Pages\EditContact::route('/{record}/edit'),
        ];
    }
}
