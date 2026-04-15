<?php

declare(strict_types=1);

namespace App\Filament\Resources\DealershipResource\Pages;

use App\Filament\Resources\DealershipResource;
use App\Models\Store;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ManageDealershipStores extends ManageRelatedRecords
{
    protected static string $resource = DealershipResource::class;

    protected static string $relationship = 'stores';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office';

    protected ?string $subheading = 'Manage Stores';

    public static function getNavigationLabel(): string
    {
        return 'Stores';
    }

    public function getHeading(): string
    {
        return $this->getOwnerRecord()->name;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('user_id')
                    ->default(auth()->user()->id),
                TextInput::make('name')
                    ->columnSpanFull()
                    ->required()
                    ->maxLength(255),
                \Filament\Schemas\Components\Grid::make(3)
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
                TextColumn::make('name')
                    ->description(fn (Store $record): string => $record->city.', '.$record->state),
                TextColumn::make('phone'),
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
