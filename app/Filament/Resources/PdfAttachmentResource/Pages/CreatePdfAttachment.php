<?php

namespace App\Filament\Resources\PdfAttachmentResource\Pages;

use App\Filament\Resources\PdfAttachmentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePdfAttachment extends CreateRecord
{
    protected static string $resource = PdfAttachmentResource::class;
}
