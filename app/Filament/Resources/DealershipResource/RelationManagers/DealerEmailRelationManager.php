<?php

namespace App\Filament\Resources\DealershipResource\RelationManagers;

use App\Enum\ReminderFrequency;
use App\Models\Contact;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class DealerEmailRelationManager extends RelationManager
{
    protected static string $relationship = 'dealerEmails';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('recipients')
                    ->label('Recipients')
                    ->multiple()
                    ->options(function (RelationManager $livewire): array {
                        return $livewire->getOwnerRecord()->contacts()
                            ->pluck('email', 'email')
                            ->toArray();
                    })
                    ->columnSpanFull(),
                FileUpload::make('attachment')
                    ->acceptedFileTypes(['application/pdf'])
                    ->preserveFilenames()
                    ->columnSpanFull()
                    ->directory('form-attachments'),
                TextInput::make('subject')
                    ->columnSpanFull()
                    ->required()
                    ->maxLength(255),
                RichEditor::make('message')
                    ->columnSpanFull()
                    ->required(),
                DatePicker::make('start_date')
                    ->closeOnDateSelection()
                    ->minDate(now()->format('Y-m-d'))
                    ->format('Y-m-d')
                    ->required(),
                Select::make('frequency')
                    ->options(ReminderFrequency::class)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('subject'),
                Tables\Columns\TextColumn::make('frequency'),
                Tables\Columns\TextColumn::make('last_sent')->date(),
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
