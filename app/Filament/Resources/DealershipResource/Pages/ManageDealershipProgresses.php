<?php

namespace App\Filament\Resources\DealershipResource\Pages;

use App\Filament\Resources\DealershipResource;
use App\Models\Contact;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ManageDealershipProgresses extends ManageRelatedRecords
{
    protected static string $resource = DealershipResource::class;

    protected static string $relationship = 'progresses';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected ?string $subheading = 'Manage Progress';

    public function getHeading(): string
    {
        return $this->getOwnerRecord()->name;
    }

    public static function getNavigationLabel(): string
    {
        return 'Progress';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('user_id')
                    ->default(auth()->id()),
                Forms\Components\Select::make('contact_id')
                    ->label('Select a Contact if available')
                    ->options(
                        Contact::all()->pluck('name', 'id')
                    )
                    ->searchable(),
                Forms\Components\DatePicker::make('date'),
                Forms\Components\Textarea::make('details')
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
                Tables\Columns\TextColumn::make('details')->words(30)->wrap(),
                Tables\Columns\TextColumn::make('created_at')->label('Date')->date(),
                Tables\Columns\TextColumn::make('contact.name')->label('Contact'),
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
