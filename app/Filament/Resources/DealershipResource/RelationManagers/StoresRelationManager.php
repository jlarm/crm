<?php

namespace App\Filament\Resources\DealershipResource\RelationManagers;

use App\Models\Dealership;
use App\Models\Store;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class StoresRelationManager extends RelationManager
{
    protected static string $relationship = 'stores';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('user_id')
                    ->default(auth()->user()->id),
                Forms\Components\TextInput::make('name')
                    ->columnSpanFull()
                    ->required()
                    ->maxLength(255),
                Grid::make(3)
                    ->schema([
                        TextInput::make('address')
                            ->columnSpanFull(),
                        TextInput::make('city')
                            ->columns(1),
                        Select::make('state')
                            ->options([
                                'AL' => 'Alabama',
                                'AK' => 'Alaska',
                                'AZ' => 'Arizona',
                                'AR' => 'Arkansas',
                                'CA' => 'California',
                                'CO' => 'Colorado',
                                'CT' => 'Connecticut',
                                'DE' => 'Delaware',
                                'DC' => 'District of Columbia',
                                'FL' => 'Florida',
                                'GA' => 'Georgia',
                                'HI' => 'Hawaii',
                                'ID' => 'Idaho',
                                'IL' => 'Illinois',
                                'IN' => 'Indiana',
                                'IA' => 'Iowa',
                                'KS' => 'Kansas',
                                'KY' => 'Kentucky',
                                'LA' => 'Louisiana',
                                'ME' => 'Maine',
                                'MD' => 'Maryland',
                                'MA' => 'Massachusetts',
                                'MI' => 'Michigan',
                                'MN' => 'Minnesota',
                                'MS' => 'Mississippi',
                                'MO' => 'Missouri',
                                'MT' => 'Montana',
                                'NE' => 'Nebraska',
                                'NV' => 'Nevada',
                                'NH' => 'New Hampshire',
                                'NJ' => 'New Jersey',
                                'NM' => 'New Mexico',
                                'NY' => 'New York',
                                'NC' => 'North Carolina',
                                'ND' => 'North Dakota',
                                'OH' => 'Ohio',
                                'OK' => 'Oklahoma',
                                'OR' => 'Oregon',
                                'PA' => 'Pennsylvania',
                                'RI' => 'Rhode Island',
                                'SC' => 'South Carolina',
                                'SD' => 'South Dakota',
                                'TN' => 'Tennessee',
                                'TX' => 'Texas',
                                'UT' => 'Utah',
                                'VT' => 'Vermont',
                                'VA' => 'Virginia',
                                'WA' => 'Washington',
                                'WV' => 'West Virginia',
                                'WI' => 'Wisconsin',
                                'WY' => 'Wyoming',
                            ]),
                        TextInput::make('zip_code')
                            ->columns(1),
                        TextInput::make('phone')
                            ->label('Phone Number')
                            ->mask('(999) 999-9999')
                            ->placeholder('(123) 456-7890')
                            ->columnSpanFull(),
                    ]),
                Textarea::make('notes')
                    ->rows(10)
                    ->autosize()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->description(fn (Store $record): string => $record->city. ', ' . $record->state),
                Tables\Columns\TextColumn::make('phone'),
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
