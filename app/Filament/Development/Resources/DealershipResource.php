<?php

namespace App\Filament\Development\Resources;

use App\Filament\Development\Resources\DealershipResource\Pages;
use App\Filament\Resources\DealershipResource\RelationManagers\ContactsRelationManager;
use App\Filament\Resources\DealershipResource\RelationManagers\ProgressesRelationManager;
use App\Filament\Resources\DealershipResource\RelationManagers\StoresRelationManager;
use App\Models\Dealership;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DealershipResource extends Resource
{
    protected static ?string $model = Dealership::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('in_development', true);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
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
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('phone')
                                                    ->label('Phone Number')
                                                    ->mask('(999) 999-9999')
                                                    ->placeholder('(123) 456-7890'),
                                                TextInput::make('email')
                                                    ->label('Email Address')
                                                    ->email(),
                                            ]),
                                        Select::make('type')
                                            ->options([
                                                'Automotive' => 'Automotive',
                                                'RV' => 'RV',
                                                'Motorsports' => 'Motorsports',
                                                'Maritime' => 'Maritime',
                                            ])
                                            ->required(),
                                    ]),
                            ])->columnSpanFull(),
                        Forms\Components\Section::make('Notes')
                            ->schema([
                                Textarea::make('notes')
                                    ->rows(10)
                                    ->autosize()
                                    ->hiddenLabel()
                                    ->columnSpanFull(),
                            ])->collapsible(),
                        Forms\Components\Section::make('Current Solution')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('current_solution_name')
                                            ->label('Name'),
                                        TextInput::make('current_solution_use')
                                            ->label('Use'),
                                    ]),
                            ])->collapsed()
                    ])->columnSpan(2),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Consultant')
                            ->schema([
                                Select::make('users')
                                    ->columnSpanFull()
                                    ->multiple()
                                    ->relationship('users', 'name')
                                    ->hiddenLabel(),
                            ]),
                        Forms\Components\Section::make('Status')
                            ->schema([
                                Forms\Components\Toggle::make('in_development')
                                    ->onColor('success')
                                    ->offColor('primary')
                                    ->helperText('*Turn on "In Development" when actively working on this dealership with the Sales Dev Rep.')
                                    ->label('In Development'),
                                Select::make('status')
                                    ->options([
                                        'active' => 'Active',
                                        'inactive' => 'Inactive',
                                        'imported' => 'Imported',
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
                    ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->searchable(),
                Tables\Columns\TextColumn::make('city')
                    ->searchable(),
                Tables\Columns\TextColumn::make('state')
                    ->searchable(),
                Tables\Columns\TextColumn::make('zip_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
            ])
            ->filters([
                //
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
            StoresRelationManager::class,
            ContactsRelationManager::class,
            ProgressesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDealerships::route('/'),
            //            'create' => Pages\CreateDealership::route('/create'),
            'edit' => Pages\EditDealership::route('/{record}/edit'),
        ];
    }
}
