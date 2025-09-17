<?php

declare(strict_types=1);

namespace App\Filament\Resources\PdfAttachmentResource\Pages;

use App\Filament\Resources\PdfAttachmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPdfAttachments extends ListRecords
{
    protected static string $resource = PdfAttachmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
