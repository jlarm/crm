<?php

namespace App\Filament\Resources\PdfAttachmentResource\Pages;

use App\Filament\Resources\PdfAttachmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPdfAttachment extends EditRecord
{
    protected static string $resource = PdfAttachmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
