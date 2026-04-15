<?php

declare(strict_types=1);

namespace App\Filament\Resources\DealerEmailTemplateResource\Pages;

use App\Filament\Resources\DealerEmailTemplateResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;

class EditDealerEmailTemplate extends EditRecord
{
    protected static string $resource = DealerEmailTemplateResource::class;

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Template Details')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('subject')
                            ->required()
                            ->columnSpanFull(),
                        Select::make('pdf_attachments')
                            ->multiple()
                            ->relationship('pdfAttachments', 'file_name')
                            ->preload()
                            ->columnSpanFull()
                            ->createOptionForm([
                                FileUpload::make('file_path')
                                    ->required()
                                    ->acceptedFileTypes(['application/pdf'])
                                    ->storeFileNamesIn('file_name')
                                    ->directory('pdfs'),
                            ]),
                        RichEditor::make('body')
                            ->hint('Use the {{contact_name}} placeholder to insert the contact\'s name.')
                            ->hintColor('primary')
                            ->required()
                            ->disableToolbarButtons(['attachFiles', 'codeBlock'])
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
