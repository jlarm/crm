<?php

declare(strict_types=1);

namespace App\Filament\Resources\DealershipResource\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class ContactsRelationManager extends RelationManager
{
    protected static string $relationship = 'contacts';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->columnSpanFull()
                    ->required()
                    ->maxLength(255),
                Grid::make(2)
                    ->schema([
                        TextInput::make('email')
                            ->email()
                            ->nullable()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->mask('(999) 999-9999')
                            ->placeholder('(123) 456-7890')
                            ->nullable()
                            ->maxLength(255),
                    ]),
                TextInput::make('position')
                    ->columnSpanFull()
                    ->nullable()
                    ->maxLength(255),
                Toggle::make('primary_contact'),
                Select::make('tags')
                    ->relationship('tags', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->placeholder('Select tags'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('email'),
                TextColumn::make('phone'),
                TextColumn::make('position'),
                ToggleColumn::make('primary_contact')
                    ->afterStateUpdated(function ($record, $state): void {
                        // turn off anyone else as primary contact
                        if ($state) {
                            $record->dealership->contacts()
                                ->where('id', '!=', $record->id)
                                ->update(['primary_contact' => false]);
                        }
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
