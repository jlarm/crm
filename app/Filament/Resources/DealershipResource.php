<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DealershipResource\Pages;
use App\Filament\Resources\DealershipResource\RelationManagers;
use App\Models\Dealership;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DealershipResource extends Resource
{
    protected static ?string $model = Dealership::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)
                    ->schema([
                        TextInput::make('name')
                            ->columnSpan(2)
                            ->autofocus()
                            ->required(),
                    ]),
                Section::make('Address')
                    ->schema([
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
                    ])->columns(2),
                Grid::make(3)
                    ->schema([
                       TextInput::make('corporate_name')
                            ->label('Corporate Office Name'),
                       TextInput::make('corporate_city')
                            ->label('Corporate Office City'),
                       Select::make('corporate_state')
                            ->label('Corporate Office State')
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
                    ]),
                TextInput::make('number_of_stores')
                    ->numeric()
                    ->columnSpanFull(),
                Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'pending' => 'Pending',
                    ])
                    ->required()
                    ->columnSpanFull(),
                Grid::make(2)
                    ->schema([
                        TextInput::make('current_solution_name')
                            ->label('Current Solution Name'),
                        TextInput::make('current_solution_use')
                            ->label('Current Solution Use'),
                    ]),
                Textarea::make('notes')
                    ->rows(10)
                    ->autosize()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone'),
                TextColumn::make('state')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        'pending' => 'warning',
                    })
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ]),
                Tables\Filters\SelectFilter::make('state')
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
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListDealerships::route('/'),
            'create' => Pages\CreateDealership::route('/create'),
            'edit' => Pages\EditDealership::route('/{record}/edit'),
        ];
    }
}
