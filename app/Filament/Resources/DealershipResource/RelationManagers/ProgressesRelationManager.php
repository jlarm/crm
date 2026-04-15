<?php

declare(strict_types=1);

namespace App\Filament\Resources\DealershipResource\RelationManagers;

use App\Models\Contact;
use App\Models\ProgressCategory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProgressesRelationManager extends RelationManager
{
    protected static string $relationship = 'progresses';

    protected static ?string $recordTitleAttribute = 'Progress';

    protected static ?string $modelLabel = 'Progress';

    protected static ?string $pluralModelLabel = 'Progress';

    protected static ?string $pluralLabel = 'Progress';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('user_id')
                    ->default(auth()->id()),
                Select::make('contact_id')
                    ->label('Select a Contact if available')
                    ->options(
                        Contact::all()->pluck('name', 'id')
                    )
                    ->preload()
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
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('details')->words(30)->wrap(),
                TextColumn::make('created_at')->label('Date')->date(),
                TextColumn::make('contact.name')->label('Contact'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                //                Tables\Actions\EditAction::make(),
                //                Tables\Actions\DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])->defaultSort('date', 'desc');
    }
}
