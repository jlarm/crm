<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\DealerEmailTemplateResource\Pages\CreateDealerEmailTemplate;
use App\Filament\Resources\DealerEmailTemplateResource\Pages\EditDealerEmailTemplate;
use App\Filament\Resources\DealerEmailTemplateResource\Pages\ListDealerEmailTemplates;
use App\Filament\Resources\DealerEmailTemplateResource\Pages\ViewDealerEmailTemplate;
use App\Filament\Resources\DealerEmailTemplateResource\RelationManagers\DealerEmailsRelationManager;
use App\Models\DealerEmailTemplate;
use App\Services\ClaudeEmailGeneratorService;
use BackedEnum;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class DealerEmailTemplateResource extends Resource
{
    protected static ?string $model = DealerEmailTemplate::class;

    protected static ?string $navigationLabel = 'Templates';

    protected static ?string $slug = 'dealer-email-templates';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';

    protected static string|UnitEnum|null $navigationGroup = 'Email';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->columnSpanFull()
                    ->required(),
                TextInput::make('subject')
                    ->required()
                    ->columnSpanFull()
                    ->suffixAction(
                        Action::make('generateSubject')
                            ->label('AI Generate')
                            ->icon('heroicon-o-sparkles')
                            ->visible(fn () => app(ClaudeEmailGeneratorService::class)->isConfigured())
                            ->schema([
                                Textarea::make('context')
                                    ->label('What should this email be about?')
                                    ->placeholder('e.g., Product demo invitation, follow-up after meeting, pricing information, etc.')
                                    ->rows(3)
                                    ->required(),
                            ])
                            ->action(function (callable $set, array $data): void {
                                $claudeService = app(ClaudeEmailGeneratorService::class);

                                try {
                                    $subject = $claudeService->generateEmailSubjectWithContext($data['context']);
                                    $set('subject', $subject);

                                    Notification::make()
                                        ->title('Subject Generated')
                                        ->body('AI-generated subject based on your context.')
                                        ->success()
                                        ->send();
                                } catch (Exception) {
                                    Notification::make()
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
                        Action::make('generateAI')
                            ->label('AI Generate')
                            ->icon('heroicon-o-sparkles')
                            ->visible(fn () => app(ClaudeEmailGeneratorService::class)->isConfigured())
                            ->schema([
                                Textarea::make('context')
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
                            ->action(function (callable $set, array $data): void {
                                $claudeService = app(ClaudeEmailGeneratorService::class);

                                try {
                                    $content = $claudeService->generateEmailContentWithContext($data['context'], $data['tone']);
                                    $set('body', $content);

                                    Notification::make()
                                        ->title('Template Content Generated')
                                        ->body('AI-generated template content based on your context has been added.')
                                        ->success()
                                        ->send();
                                } catch (Exception) {
                                    Notification::make()
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
            ->recordActions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->toolbarActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            DealerEmailsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDealerEmailTemplates::route('/'),
            'create' => CreateDealerEmailTemplate::route('/create'),
            'view' => ViewDealerEmailTemplate::route('/{record}'),
            'edit' => EditDealerEmailTemplate::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [];
    }
}
