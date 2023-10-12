<?php

namespace App\Filament\Resources\DealershipResource\RelationManagers;

use App\Models\Contact;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProgressesRelationManager extends RelationManager
{
    protected static string $relationship = 'progresses';
    protected static ?string $recordTitleAttribute = 'Progress';
    protected static ?string $modelLabel = 'Progress';
    protected static ?string $pluralModelLabel = 'Progress';
    protected static ?string $pluralLabel = 'Progress';

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
                Forms\Components\DatePicker::make('date')
                    ->format('m-d-Y'),
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
                Tables\Columns\TextColumn::make('date')->since(),
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
            ])->defaultSort('date', 'desc');
    }
}
