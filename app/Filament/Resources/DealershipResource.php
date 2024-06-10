<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DealershipResource\Pages;
use App\Filament\Resources\DealershipResource\RelationManagers;
use App\Mail\ClientMail;
use App\Mail\MessageMail;
use App\Models\Dealership;
use App\Models\Progress;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Actions\Action;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class DealershipResource extends Resource
{
    protected static ?string $model = Dealership::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $recordTitleAttribute = 'name';

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
                        Forms\Components\Section::make()
                            ->schema([
                               Forms\Components\Actions::make([
                                   Forms\Components\Actions\Action::make('Send Email to Sales Development Rep')
                                       ->hidden(fn (string $operation): bool => $operation === 'create')
                                       ->form([
                                           Select::make('user')
                                               ->required()
                                               ->label('Sales Development Rep')
                                               ->helperText('Select the Sales Development Rep to send the email to.')
                                                ->options(User::role('Sales Development Rep')->pluck('name', 'email')),
                                           TextInput::make('subject')->required(),
                                           RichEditor::make('body')->required(),
                                       ])
                                       ->action(function (array $data, Form $form) {
                                           Mail::to($data['user'])
                                               ->send(new MessageMail(
                                                   $form->model,
                                                   auth()->user(),
                                                   $data['subject'],
                                                   $data['body']
                                               ));
                                           Progress::create([
                                               'dealership_id' => $form->model->id,
                                               'user_id' => auth()->id(),
                                               'date' => now(),
                                               'details' => 'Sent email to '.$data['user']. ' - ' . $data['subject'],
                                           ]);
                                       }),
                                   Forms\Components\Actions\Action::make('Send Email to client')
                                       ->hidden(fn (string $operation): bool => $operation === 'create')
                                       ->form([
                                           Select::make('contact_id')
                                               ->relationship('contacts', 'name', fn (Builder $query) => $query->where('dealership_id', $form->model->id))
                                               ->required()
                                               ->label('Dealership Contact')
                                               ->helperText('Select the dealership contact to send the email to.'),
                                           TextInput::make('subject')->required(),
                                           RichEditor::make('body')->disableToolbarButtons(['attachFiles'])->required(),
                                       ])
                                       ->action(function (array $data, Form $form) {
                                           Mail::to($data['user'])
                                               ->send(new ClientMail(
                                                   auth()->user(),
                                                   $data['subject'],
                                                   $data['body']
                                               ));
                                           Progress::create([
                                               'dealership_id' => $form->model->id,
                                               'user_id' => auth()->id(),
                                               'date' => now(),
                                               'details' => 'Sent email to '.$data['user']. ' - ' . $data['subject'],
                                           ]);
                                       })
                               ])
                            ])
                    ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->description(fn (Dealership $dealership): string => $dealership->city.', '.$dealership->state)
                    ->wrap()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        'imported' => 'primary',
                    }),
                TextColumn::make('rating')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'hot' => 'success',
                        'warm' => 'warning',
                        'cold' => 'primary',
                    }),
                TextColumn::make('total_store_count')
                    ->label('Stores'),
                TextColumn::make('users.name')
                    ->label('Consultants')
                    ->limitList(3)
                    ->listWithLineBreaks(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('rating')
                    ->options([
                        'hot' => 'Hot',
                        'warm' => 'Warm',
                        'cold' => 'Cold',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'Automotive' => 'Automotive',
                        'RV' => 'RV',
                        'Motorsports' => 'Motorsports',
                        'Maritime' => 'Maritime',
                    ]),
                Tables\Filters\SelectFilter::make('user')
                    ->label('Consultant')
                    ->relationship('users', 'name'),
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
                ExportBulkAction::make()->exports([
                    ExcelExport::make()->withColumns([
                        \pxlrbt\FilamentExcel\Columns\Column::make('name'),
                        \pxlrbt\FilamentExcel\Columns\Column::make('phone'),
                        \pxlrbt\FilamentExcel\Columns\Column::make('email'),
                        \pxlrbt\FilamentExcel\Columns\Column::make('status'),
                        \pxlrbt\FilamentExcel\Columns\Column::make('rating'),
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
            RelationManagers\StoresRelationManager::class,
            RelationManagers\ContactsRelationManager::class,
            RelationManagers\ProgressesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            //            'view' => Pages\ViewDealership::route('/{record}'),
            'index' => Pages\ListDealerships::route('/'),
            'create' => Pages\CreateDealership::route('/create'),
            'edit' => Pages\EditDealership::route('/{record}/edit'),
        ];
    }
}
