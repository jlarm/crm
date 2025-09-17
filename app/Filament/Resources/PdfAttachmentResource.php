<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\PdfAttachmentResource\Pages;
use App\Models\PdfAttachment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class PdfAttachmentResource extends Resource
{
    protected static ?string $model = PdfAttachment::class;

    protected static ?string $navigationIcon = 'heroicon-o-document';

    protected static ?string $navigationGroup = 'Email';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('file_path')
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
                Tables\Columns\TextColumn::make('file_name'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('view_pdf')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn (PdfAttachment $record) => route('pdf.view', $record))
                    ->openUrlInNewTab()
                    ->visible(fn (PdfAttachment $record): bool => ! empty($record->file_path) && Storage::disk('public')->exists($record->file_path)),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListPdfAttachments::route('/'),
            'create' => Pages\CreatePdfAttachment::route('/create'),
            'edit' => Pages\EditPdfAttachment::route('/{record}/edit'),
        ];
    }
}
