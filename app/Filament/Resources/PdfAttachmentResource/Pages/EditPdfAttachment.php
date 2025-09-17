<?php

declare(strict_types=1);

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
            Actions\Action::make('view_pdf')
                ->label('View PDF')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->url(fn () => route('pdf.view', $this->record))
                ->openUrlInNewTab()
                ->visible(fn (): bool => ! empty($this->record->file_path) && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->record->file_path)),
            Actions\DeleteAction::make(),
        ];
    }
}
