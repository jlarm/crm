<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ContactResource\Pages;
use App\Filament\Resources\ContactResource\Pages\ListContacts;
use App\Models\Contact;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class ContactResource extends Resource
{
    protected static ?string $model = Contact::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model|\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->description(fn (Contact $contact): string => $contact->phone ?? '')
                    ->searchable(),
                IconColumn::make('linkedin_link')
                    ->label('LinkedIn')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn ($record) => $record->linkedin_link)
                    ->openUrlInNewTab(),
                TextColumn::make('position'),
                TextColumn::make('dealership.name')
                    ->description(fn (Contact $contact): string => $contact->dealership->city.', '.$contact->dealership->state)
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                ExportBulkAction::make()->exports([
                    ExcelExport::make()->withColumns([
                        Column::make('name'),
                        Column::make('phone'),
                        Column::make('email'),
                    ]),
                ]),
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
            'index' => ListContacts::route('/'),
            //            'create' => Pages\CreateContact::route('/create'),
            //            'edit' => Pages\EditContact::route('/{record}/edit'),
        ];
    }
}
