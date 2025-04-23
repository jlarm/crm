<?php

namespace App\Filament\Resources;

use App\Enum\ReminderFrequency;
use App\Filament\Resources\DealerEmailResource\Pages;
use App\Models\DealerEmail;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DealerEmailResource extends Resource
{
    protected static ?string $model = DealerEmail::class;

    protected static ?string $navigationLabel = 'Current Emails';

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'Email';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('subject')
                    ->columnSpanFull()
                    ->required()
                    ->maxLength(255),
                Forms\Components\RichEditor::make('message')->columnSpanFull(),
                Forms\Components\Select::make('frequency')
                    ->options(ReminderFrequency::class)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                DealerEmail::query()
                    ->where('user_id', auth()->id())
                ->orderBy('created_at', 'desc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('dealership.name')->sortable(),
                Tables\Columns\TextColumn::make('recipients')->sortable(),
                Tables\Columns\TextColumn::make('frequency')->sortable(),
                Tables\Columns\TextColumn::make('last_sent')->date()->sortable()
            ])
            ->defaultSort('last_sent', 'asc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('view_dealership_emails')
                    ->label('View')
                    ->url(fn (DealerEmail $record): string =>
                        DealershipResource::getUrl('emails', ['record' => $record->dealership])
                    ),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDealerEmails::route('/'),
            'create' => Pages\CreateDealerEmail::route('/create'),
            'edit' => Pages\EditDealerEmail::route('/{record}/edit'),
        ];
    }
}
