<?php

declare(strict_types=1);

namespace App\Filament\Resources\PdfAttachmentResource\Pages;

use App\Filament\Resources\PdfAttachmentResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePdfAttachment extends CreateRecord
{
    protected static string $resource = PdfAttachmentResource::class;
}
