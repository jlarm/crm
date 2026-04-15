<?php

declare(strict_types=1);

namespace App\Filament\Resources\DealershipResource\Pages;

use App\Filament\Resources\DealershipResource;
use App\Models\Contact;
use App\Models\ProgressCategory;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ManageDealershipProgresses extends ManageRelatedRecords
{
    protected static string $resource = DealershipResource::class;

    protected static string $relationship = 'progresses';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected ?string $subheading = 'Manage Progress';

    public static function getNavigationLabel(): string
    {
        return 'Progress';
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
                    ->default(auth()->id()),
                Select::make('contact_id')
                    ->label('Select a Contact if available')
                    ->preload()
                    ->options(
                        Contact::all()->pluck('name', 'id')
                    )
                    ->searchable(),
                DatePicker::make('date'),
                Select::make('progress_category_id')
                    ->label('Select a Progress Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('name')->required(),
                    ])
                    ->createOptionUsing(fn (array $data): int => ProgressCategory::create($data)->getKey())
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('details')
                    ->columnSpanFull()
                    ->required()
                    ->rows(4),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('category'))
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('category.name')
                    ->default('-')
                    ->label('Category'),
                TextColumn::make('details')->words(30)->wrap(),
                TextColumn::make('created_at')->label('Date')->date(),
                TextColumn::make('contact.name')->label('Contact'),
            ])
            ->filters([
                SelectFilter::make('progress_category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->preload(),
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
