<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\DealerEmailTemplateResource\Pages;
use App\Filament\Resources\DealerEmailTemplateResource\RelationManagers;
use App\Models\DealerEmailTemplate;
use Exception;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DealerEmailTemplateResource extends Resource
{
    protected static ?string $model = DealerEmailTemplate::class;

    protected static ?string $navigationLabel = 'Templates';

    protected static ?string $slug = 'dealer-email-templates';

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = 'Email';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->columnSpanFull()
                    ->required(),
                TextInput::make('subject')
                    ->required()
                    ->columnSpanFull()
                    ->suffixAction(
                        \Filament\Forms\Components\Actions\Action::make('generateSubject')
                            ->label('AI Generate')
                            ->icon('heroicon-o-sparkles')
                            ->visible(fn () => app(\App\Services\ClaudeEmailGeneratorService::class)->isConfigured())
                            ->form([
                                \Filament\Forms\Components\Textarea::make('context')
                                    ->label('What should this email be about?')
                                    ->placeholder('e.g., Product demo invitation, follow-up after meeting, pricing information, etc.')
                                    ->rows(3)
                                    ->required(),
                            ])
                            ->action(function (callable $set, array $data) {
                                $claudeService = app(\App\Services\ClaudeEmailGeneratorService::class);

                                try {
                                    $subject = $claudeService->generateEmailSubjectWithContext($data['context']);
                                    $set('subject', $subject);

                                    \Filament\Notifications\Notification::make()
                                        ->title('Subject Generated')
                                        ->body('AI-generated subject based on your context.')
                                        ->success()
                                        ->send();
                                } catch (Exception $e) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Generation Failed')
                                        ->body('Unable to generate subject. Please try again.')
                                        ->danger()
                                        ->send();
                                }
                            })
                    ),
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
                    ->columnSpanFull()
                    ->hintActions([
                        \Filament\Forms\Components\Actions\Action::make('generateAI')
                            ->label('AI Generate')
                            ->icon('heroicon-o-sparkles')
                            ->visible(fn () => app(\App\Services\ClaudeEmailGeneratorService::class)->isConfigured())
                            ->form([
                                \Filament\Forms\Components\Textarea::make('context')
                                    ->label('What should this email be about?')
                                    ->placeholder('e.g., Product demo invitation, follow-up after meeting, pricing information, partnership proposal, etc.')
                                    ->rows(3)
                                    ->required(),
                                Select::make('tone')
                                    ->label('Email Tone')
                                    ->options([
                                        'professional' => 'Professional',
                                        'friendly' => 'Friendly',
                                        'formal' => 'Formal',
                                        'casual' => 'Casual',
                                        'consultative' => 'Consultative',
                                    ])
                                    ->default('professional')
                                    ->required(),
                            ])
                            ->action(function (callable $set, array $data) {
                                $claudeService = app(\App\Services\ClaudeEmailGeneratorService::class);

                                try {
                                    $content = $claudeService->generateEmailContentWithContext($data['context'], $data['tone']);
                                    $set('body', $content);

                                    \Filament\Notifications\Notification::make()
                                        ->title('Template Content Generated')
                                        ->body('AI-generated template content based on your context has been added.')
                                        ->success()
                                        ->send();
                                } catch (Exception $e) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Generation Failed')
                                        ->body('Unable to generate content. Please try again.')
                                        ->danger()
                                        ->send();
                                }
                            }),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('dealer_emails_count')
                    ->counts('dealerEmails')
                    ->label('Emails Using Template'),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\DealerEmailsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDealerEmailTemplates::route('/'),
            'create' => Pages\CreateDealerEmailTemplate::route('/create'),
            'view' => Pages\ViewDealerEmailTemplate::route('/{record}'),
            'edit' => Pages\EditDealerEmailTemplate::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [];
    }
}
