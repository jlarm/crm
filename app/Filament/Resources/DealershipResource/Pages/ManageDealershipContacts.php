<?php

namespace App\Filament\Resources\DealershipResource\Pages;

use App\Filament\Resources\DealershipResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ManageDealershipContacts extends ManageRelatedRecords
{
    protected static string $resource = DealershipResource::class;

    protected static string $relationship = 'contacts';

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected ?string $subheading = 'Manage Contacts';

    public function getHeading(): string
    {
        return $this->getOwnerRecord()->name;
    }

    public static function getNavigationLabel(): string
    {
        return 'Contacts';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->columnSpanFull()
                    ->required()
                    ->maxLength(255),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->nullable()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->mask('(999) 999-9999')
                            ->placeholder('(123) 456-7890')
                            ->nullable()
                            ->maxLength(255),
                    ]),
                Forms\Components\TextInput::make('position')
                    ->columnSpanFull()
                    ->nullable()
                    ->maxLength(255),
                Forms\Components\Toggle::make('primary_contact'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('email'),
                Tables\Columns\TextColumn::make('phone'),
                Tables\Columns\TextColumn::make('position'),
                Tables\Columns\ToggleColumn::make('primary_contact')
                    ->afterStateUpdated(function ($record, $state) {
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
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
