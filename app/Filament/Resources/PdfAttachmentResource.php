<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\PdfAttachmentResource\Pages\CreatePdfAttachment;
use App\Filament\Resources\PdfAttachmentResource\Pages\EditPdfAttachment;
use App\Filament\Resources\PdfAttachmentResource\Pages\ListPdfAttachments;
use App\Models\PdfAttachment;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use UnitEnum;

class PdfAttachmentResource extends Resource
{
    protected static ?string $model = PdfAttachment::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document';

    protected static string|UnitEnum|null $navigationGroup = 'Email';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('file_path')
                    ->label('PDF File')
                    ->required()
                    ->acceptedFileTypes(['application/pdf'])
                    ->storeFileNamesIn('file_name')
                    ->columnSpanFull()
                    ->directory('pdf-attachments'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('file_name'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('view_pdf')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn (PdfAttachment $record) => route('pdf.view', $record))
                    ->openUrlInNewTab()
                    ->visible(fn (PdfAttachment $record): bool => ! empty($record->file_path) && Storage::disk('public')->exists($record->file_path)),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPdfAttachments::route('/'),
            'create' => CreatePdfAttachment::route('/create'),
            'edit' => EditPdfAttachment::route('/{record}/edit'),
        ];
    }
}
