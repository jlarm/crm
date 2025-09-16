<?php

namespace App\Filament\Resources\DealerEmailTemplateResource\Pages;

use App\Filament\Resources\DealerEmailTemplateResource;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\MaxWidth;

class EditDealerEmailTemplate extends EditRecord
{
    protected static string $resource = DealerEmailTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Template Details')
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
}
