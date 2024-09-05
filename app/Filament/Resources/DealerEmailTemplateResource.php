<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DealerEmailTemplateResource\Pages;
use App\Models\DealerEmailTemplate;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DealerEmailTemplateResource extends Resource
{
    protected static ?string $model = DealerEmailTemplate::class;

    protected static ?string $slug = 'dealer-email-templates';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->columnSpanFull()
                    ->required(),
                TextInput::make('subject')
                    ->required()
                    ->columnSpanFull()
                    ->required(),
                FileUpload::make('attachment_path')
                    ->label('Attachment')
                    ->acceptedFileTypes(['application/pdf'])
                    ->storeFileNamesIn('attachment_name')
                    ->columnSpanFull()
                    ->directory('form-attachments'),
                RichEditor::make('body')
                    ->hint('Use the {{contact_name}} placeholder to insert the contact\'s name.')
                    ->hintColor('primary')
                    ->required()
                    ->disableToolbarButtons(['attachFiles', 'codeBlock'])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDealerEmailTemplates::route('/'),
            'create' => Pages\CreateDealerEmailTemplate::route('/create'),
            'edit' => Pages\EditDealerEmailTemplate::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [];
    }
}
