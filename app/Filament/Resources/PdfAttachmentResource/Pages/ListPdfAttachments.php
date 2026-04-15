<?php

declare(strict_types=1);

namespace App\Filament\Resources\PdfAttachmentResource\Pages;

use App\Filament\Resources\PdfAttachmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPdfAttachments extends ListRecords
{
    protected static string $resource = PdfAttachmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
