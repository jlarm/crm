<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DealershipResource\Pages;
use App\Filament\Resources\DealershipResource\RelationManagers;
use App\Models\Dealership;
use App\Models\Progress;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Konnco\FilamentImport\Actions\ImportField;
use Konnco\FilamentImport\Import;

class DealershipResource extends Resource
{
    protected static ?string $model = Dealership::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Label')
                    ->tabs([
                        Tabs\Tab::make('General')
                            ->schema([
                                TextInput::make('name')
                                ->columnSpanFull()
                                ->autofocus()
                                ->required(),
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
                                ]),
                            Grid::make()
                                ->schema([
                                    TextInput::make('phone')
                                        ->label('Phone Number')
                                        ->mask('(999) 999-9999')
                                        ->placeholder('(123) 456-7890')
                                        ->columnSpanFull(),
                                    TextInput::make('email')
                                        ->label('Email Address')
                                        ->email()
                                        ->columnSpanFull(),
                                    Select::make('status')
                                        ->options([
                                            'active' => 'Active',
                                            'inactive' => 'Inactive',
                                            'pending' => 'Pending',
                                        ])
                                        ->required(),
                                    Select::make('rating')
                                        ->options([
                                            'hot' => 'Hot',
                                            'warm' => 'Warm',
                                            'cold' => 'Cold',
                                        ])
                                        ->required(),
                                ]),
                            ]),
                        Tabs\Tab::make('Current Solution')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('current_solution_name')
                                            ->label('Current Solution Name'),
                                        TextInput::make('current_solution_use')
                                            ->label('Current Solution Use'),
                                    ])
                            ]),
                        Tabs\Tab::make('Notes')
                            ->schema([
                                Textarea::make('notes')
                                ->rows(10)
                                ->autosize()
                                ->columnSpanFull(),
                            ]),
                    ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->description(fn (Dealership $dealership): string => $dealership->city . ', ' . $dealership->state)
                    ->wrap()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('progresses.date')
                    ->description(fn (Dealership $dealership): string => $dealership->progresses()->latest()->first()->details ?? '-')
                    ->wrap()
                    ->words(5)
                    ->label('Progress'),
                TextColumn::make('phone'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        'pending' => 'warning',
                    }),
                TextColumn::make('rating')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'hot' => 'success',
                        'warm' => 'warning',
                        'cold' => 'primary',
                    }),
                TextColumn::make('stores_count')
                    ->counts('stores')
                    ->label('Stores'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ]),
                Tables\Filters\SelectFilter::make('rating')
                    ->options([
                        'hot' => 'Hot',
                        'warm' => 'Warm',
                        'cold' => 'Cold',
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
            RelationManagers\StoresRelationManager::class,
            RelationManagers\ContactsRelationManager::class,
            RelationManagers\ProgressesRelationManager::class,
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
