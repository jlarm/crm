<?php

declare(strict_types=1);

namespace App\Filament\Resources\PdfAttachmentResource\Pages;

use App\Filament\Resources\PdfAttachmentResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditPdfAttachment extends EditRecord
{
    protected static string $resource = PdfAttachmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view_pdf')
                ->label('View PDF')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->url(fn () => route('pdf.view', $this->record))
                ->openUrlInNewTab()
                ->visible(fn (): bool => ! empty($this->record->file_path) && Storage::disk('public')->exists($this->record->file_path)),
            DeleteAction::make(),
        ];
    }
}
