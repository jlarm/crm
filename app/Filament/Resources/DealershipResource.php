<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\DealershipResource\Pages;
use App\Filament\Resources\DealershipResource\Pages\CreateDealership;
use App\Filament\Resources\DealershipResource\Pages\EditDealership;
use App\Filament\Resources\DealershipResource\Pages\ListDealerships;
use App\Filament\Resources\DealershipResource\Pages\ManageDealershipContacts;
use App\Filament\Resources\DealershipResource\Pages\ManageDealershipDealerEmails;
use App\Filament\Resources\DealershipResource\Pages\ManageDealershipProgresses;
use App\Filament\Resources\DealershipResource\Pages\ManageDealershipStores;
use App\Mail\MessageMail;
use App\Models\Contact;
use App\Models\Dealership;
use App\Models\Progress;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class DealershipResource extends Resource
{
    protected static ?string $model = Dealership::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?\Filament\Pages\Enums\SubNavigationPosition $subNavigationPosition = \Filament\Pages\Enums\SubNavigationPosition::Top;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        \Filament\Schemas\Components\Tabs::make('Label')
                            ->tabs([
                                Tab::make('General')
                                    ->schema([
                                        TextInput::make('name')
                                            ->columnSpanFull()
                                            ->autofocus()
                                            ->required(),
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
                                            ]),
                                        \Filament\Schemas\Components\Grid::make(2)
                                            ->schema([
                                                TextInput::make('phone')
                                                    ->columnSpanFull()
                                                    ->label('Phone Number')
                                                    ->mask('(999) 999-9999')
                                                    ->placeholder('(123) 456-7890'),
                                            ]),
                                        Select::make('type')
                                            ->options([
                                                'Automotive' => 'Automotive',
                                                'RV' => 'RV',
                                                'Motorsports' => 'Motorsports',
                                                'Maritime' => 'Maritime',
                                                'Association' => 'Association',
                                            ])
                                            ->required(),
                                    ]),
                            ])->columnSpanFull(),
                        Section::make('Notes')
                            ->schema([
                                Textarea::make('notes')
                                    ->rows(10)
                                    ->autosize()
                                    ->hiddenLabel()
                                    ->columnSpanFull(),
                            ])->collapsed(),
                        Section::make('Current Solution')
                            ->schema([
                                \Filament\Schemas\Components\Grid::make(2)
                                    ->schema([
                                        TextInput::make('current_solution_name')
                                            ->label('Name'),
                                        TextInput::make('current_solution_use')
                                            ->label('Use'),
                                    ]),
                            ])->collapsed(),
                    ])->columnSpan(2),
                Group::make()
                    ->schema([
                        Section::make('Consultant')
                            ->schema([
                                Select::make('users')
                                    ->columnSpanFull()
                                    ->multiple()
                                    ->relationship('users', 'name')
                                    ->hiddenLabel(),
                            ]),
                        Section::make('Status')
                            ->schema([
                                Toggle::make('in_development')
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
                        Section::make()
                            ->schema([
                                Actions::make([
                                    Action::make('Email Sales Development Rep')
                                        ->link()
                                        ->icon('heroicon-o-envelope')
//                                       ->color('gray')
                                        ->hidden(fn (string $operation): bool => $operation === 'create')
                                        ->schema([
                                            Select::make('user')
                                                ->required()
                                                ->label('Sales Development Rep')
                                                ->helperText('Select the Sales Development Rep to send the email to.')
                                                ->options(User::role('Sales Development Rep')->pluck('name', 'email')),
                                            TextInput::make('subject')->required(),
                                            RichEditor::make('body')->required(),
                                        ])
                                        ->action(function (array $data, Schema $schema): void {
                                            Mail::to($data['user'])
                                                ->send(new MessageMail(
                                                    $schema->model,
                                                    auth()->user(),
                                                    $data['subject'],
                                                    $data['body']
                                                ));
                                            Progress::create([
                                                'dealership_id' => $schema->model->id,
                                                'user_id' => auth()->id(),
                                                'date' => now(),
                                                'details' => 'Sent email to '.$data['user'].' - '.$data['subject'],
                                            ]);
                                        }),
                                ]),
                            ]),
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
                TextColumn::make('stores_count')
                    ->counts('stores')
                    ->label('Stores'),
                TextColumn::make('users.name')
                    ->label('Consultants')
                    ->limitList(3)
                    ->listWithLineBreaks(),
            ])
            ->filters([
                Filter::make('dealer_group')
                    ->label('Dealer Groups')
                    ->query(fn (Builder $query): Builder => $query->has('stores')),
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'imported' => 'Imported',
                    ]),
                SelectFilter::make('rating')
                    ->options([
                        'hot' => 'Hot',
                        'warm' => 'Warm',
                        'cold' => 'Cold',
                    ]),
                SelectFilter::make('type')
                    ->options([
                        'Automotive' => 'Automotive',
                        'RV' => 'RV',
                        'Motorsports' => 'Motorsports',
                        'Maritime' => 'Maritime',
                        'Association' => 'Association',
                    ]),
                SelectFilter::make('user')
                    ->label('Consultant')
                    ->relationship('users', 'name'),
                SelectFilter::make('state')
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
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                ExportBulkAction::make()
                    ->label('Export Dealerships Details')
                    ->exports([
                        ExcelExport::make()
                            ->withColumns([
                                Column::make('name'),
                                Column::make('status'),
                                Column::make('rating'),
                            ]),
                    ]),
                ExportBulkAction::make('contacts')
                    ->label('Export Contact Emails')
                    ->exports([
                        ExcelExport::make()
                            ->modifyQueryUsing(fn ($query) => $query->whereIn('dealership_id', $query->pluck('id'))
                                ->join('contacts', 'contacts.dealership_id', '=', 'dealerships.id')
                                ->select(['contacts.name', 'contacts.email']))
                            ->withColumns([
                                Column::make('name'),
                                Column::make('email'),
                            ]),
                    ]),
                BulkAction::make('export_contacts')
                    ->label('Export Contacts with Dealership Info')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->form([
                        Section::make('Export Options')
                            ->schema([
                                CheckboxList::make('dealership_fields')
                                    ->label('Select Dealership Fields to Include')
                                    ->options([
                                        'name' => 'Dealership Name',
                                        'address' => 'Address',
                                        'city' => 'City',
                                        'state' => 'State',
                                        'zip_code' => 'Zip Code',
                                        'phone' => 'Dealership Phone',
                                        'email' => 'Dealership Email',
                                        'type' => 'Type',
                                        'status' => 'Status',
                                        'rating' => 'Rating',
                                        'current_solution_name' => 'Current Solution',
                                        'in_development' => 'In Development',
                                        'consultants' => 'Assigned Consultants',
                                        'store_count' => 'Number of Stores',
                                    ])
                                    ->columns(3)
                                    ->default(['name', 'city', 'state', 'status', 'rating'])
                                    ->required(),

                                CheckboxList::make('contact_fields')
                                    ->label('Select Contact Fields to Include')
                                    ->options([
                                        'name' => 'Contact Name',
                                        'email' => 'Contact Email',
                                        'phone' => 'Contact Phone',
                                        'title' => 'Contact Title',
                                        'linkedin_link' => 'LinkedIn Profile',
                                    ])
                                    ->columns(2)
                                    ->default(['name', 'email'])
                                    ->required(),
                            ]),
                    ])
                    ->action(function (array $data, $records) {
                        // Get contacts from selected dealerships
                        $dealershipIds = $records->pluck('id');
                        $contacts = Contact::whereIn('dealership_id', $dealershipIds)
                            ->with(['dealership.users', 'dealership.stores'])
                            ->join('dealerships', 'contacts.dealership_id', '=', 'dealerships.id')
                            ->orderBy('dealerships.name')
                            ->orderBy('contacts.name')
                            ->select('contacts.*')
                            ->get();

                        // Build CSV content
                        $csvData = [];

                        // Build headers
                        $headers = [];
                        foreach ($data['dealership_fields'] as $field) {
                            $headers[] = match ($field) {
                                'name' => 'Dealership Name',
                                'address' => 'Dealership Address',
                                'city' => 'Dealership City',
                                'state' => 'Dealership State',
                                'zip_code' => 'Dealership Zip Code',
                                'phone' => 'Dealership Phone',
                                'email' => 'Dealership Email',
                                'type' => 'Dealership Type',
                                'status' => 'Dealership Status',
                                'rating' => 'Dealership Rating',
                                'current_solution_name' => 'Current Solution',
                                'in_development' => 'In Development',
                                'consultants' => 'Consultants',
                                'store_count' => 'Store Count',
                                default => ucwords(str_replace('_', ' ', $field)),
                            };
                        }

                        foreach ($data['contact_fields'] as $field) {
                            $headers[] = match ($field) {
                                'name' => 'Contact Name',
                                'email' => 'Contact Email',
                                'phone' => 'Contact Phone',
                                'title' => 'Contact Title',
                                'linkedin_link' => 'LinkedIn Profile',
                                default => ucwords(str_replace('_', ' ', $field)),
                            };
                        }
                        $csvData[] = $headers;

                        // Build data rows
                        foreach ($contacts as $contact) {
                            $row = [];

                            // Add dealership data
                            foreach ($data['dealership_fields'] as $field) {
                                $row[] = match ($field) {
                                    'name' => $contact->dealership->name,
                                    'address' => $contact->dealership->address,
                                    'city' => $contact->dealership->city,
                                    'state' => $contact->dealership->state,
                                    'zip_code' => $contact->dealership->zip_code,
                                    'phone' => $contact->dealership->phone,
                                    'email' => $contact->dealership->email,
                                    'type' => $contact->dealership->type,
                                    'status' => $contact->dealership->status,
                                    'rating' => $contact->dealership->rating,
                                    'current_solution_name' => $contact->dealership->current_solution_name,
                                    'in_development' => $contact->dealership->in_development ? 'Yes' : 'No',
                                    'consultants' => $contact->dealership->users->pluck('name')->join(', '),
                                    'store_count' => $contact->dealership->stores->count(),
                                    default => $contact->dealership->{$field} ?? '',
                                };
                            }

                            // Add contact data
                            foreach ($data['contact_fields'] as $field) {
                                $row[] = $contact->{$field} ?? '';
                            }

                            $csvData[] = $row;
                        }

                        // Generate CSV
                        $filename = 'contacts-with-dealerships-'.now()->format('Y-m-d-H-i-s').'.csv';

                        return response()->streamDownload(function () use ($csvData): void {
                            $file = fopen('php://output', 'w');
                            foreach ($csvData as $row) {
                                fputcsv($file, $row);
                            }
                            fclose($file);
                        }, $filename, ['Content-Type' => 'text/csv']);
                    }),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            EditDealership::class,
            ManageDealershipStores::class,
            ManageDealershipContacts::class,
            ManageDealershipProgresses::class,
            ManageDealershipDealerEmails::class,
        ]);
    }

    public static function getPages(): array
    {
        return [
            //            'view' => pages\ViewDealership::route('/{record}'),
            'index' => ListDealerships::route('/'),
            'create' => CreateDealership::route('/create'),
            'edit' => EditDealership::route('/{record}/edit'),
            'stores' => ManageDealershipStores::route('/{record}/stores'),
            'contacts' => ManageDealershipContacts::route('/{record}/contacts'),
            'progresses' => ManageDealershipProgresses::route('/{record}/progresses'),
            'emails' => ManageDealershipDealerEmails::route('/{record}/emails'),
        ];
    }
}
