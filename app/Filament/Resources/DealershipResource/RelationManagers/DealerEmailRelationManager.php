<?php

declare(strict_types=1);

namespace App\Filament\Resources\DealershipResource\RelationManagers;

use App\Enum\ReminderFrequency;
use App\Jobs\SendDealerEmail;
use App\Models\DealerEmailTemplate;
use Exception;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class DealerEmailRelationManager extends RelationManager
{
    protected static string $relationship = 'dealerEmails';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('recipients')
                    ->label('Recipients')
                    ->multiple()
                    ->required()
                    ->options(fn (RelationManager $livewire): array => $livewire->getOwnerRecord()->contacts()
                        ->pluck('email', 'email')
                        ->toArray())
                    ->columnSpanFull(),
                Select::make('dealer_email_template_id')
                    ->options(fn () => DealerEmailTemplate::pluck('name', 'id')->toArray())
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set): void {
                        if ($state === '') {
                            $set('subject', '');
                            $set('message', '');
                            $set('attachment', null);
                        } else {
                            $template = DealerEmailTemplate::find($state);
                            if ($template) {
                                $set('subject', $template->subject);
                                $set('attachment', $template->attachment);
                                $set('message', $template->body);
                            } else {
                                $set('subject', '');
                                $set('message', '');
                                $set('attachment', null);
                            }
                        }
                    })
                    ->columnSpanFull()
                    ->helperText('Optional: Select a template to use for the email')
                    ->label('Template'),
                Checkbox::make('customize_email')
                    ->columnSpanFull()
                    ->label('Customize email')
                    ->reactive()
                    ->hidden(fn (Get $get): bool => $get('dealer_email_template_id') === null)
                    ->afterStateUpdated(function ($state, callable $set): void {
                        if ($state === '') {
                            $set('subject', '');
                            $set('message', '');
                            $set('attachment', null);
                        } else {
                            $template = DealerEmailTemplate::find($state);
                            if ($template) {
                                $set('subject', $template->subject);
                                $set('attachment', $template->attachment);
                                $set('message', $template->body);
                            } else {
                                $set('subject', '');
                                $set('message', '');
                                $set('attachment', null);
                            }
                        }
                    })
                    ->default(false),
                Checkbox::make('customize_attachment')
                    ->columnSpanFull()
                    ->label('Customize attachment')
                    ->reactive()
                    ->hidden(fn (Get $get): bool => $get('dealer_email_template_id') === null)
                    ->default(false),
                FileUpload::make('attachment')
                    ->acceptedFileTypes(['application/pdf'])
                    ->storeFileNamesIn('attachment_name')
                    ->columnSpanFull()
                    ->reactive()
                    ->hidden(fn (Get $get): bool => $get('dealer_email_template_id') !== null && $get('customize_attachment') === false)
                    ->directory('form-attachments'),
                TextInput::make('subject')
                    ->columnSpanFull()
                    ->required()
                    ->reactive()
                    ->hidden(fn (Get $get): bool => $get('dealer_email_template_id') !== null && $get('customize_email') === false)
                    ->maxLength(255)
                    ->suffixAction(
                        \Filament\Forms\Components\Actions\Action::make('generateSubject')
                            ->label('AI Generate')
                            ->icon('heroicon-o-sparkles')
                            ->visible(fn () => app(\App\Services\ClaudeEmailGeneratorService::class)->isConfigured())
                            ->form([
                                \Filament\Forms\Components\Textarea::make('context')
                                    ->label('What should this email be about?')
                                    ->placeholder('e.g., Follow-up from our conversation, product demo invitation, pricing discussion, etc.')
                                    ->rows(3)
                                    ->required(),
                            ])
                            ->action(function (callable $set, callable $get, array $data, RelationManager $livewire): void {
                                $claudeService = app(\App\Services\ClaudeEmailGeneratorService::class);

                                if (! $claudeService->isConfigured()) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Claude API Not Configured')
                                        ->body('Please set your CLAUDE_API_KEY environment variable.')
                                        ->danger()
                                        ->send();

                                    return;
                                }

                                $dealership = $livewire->getOwnerRecord();
                                $templateId = $get('dealer_email_template_id');
                                $template = $templateId ? DealerEmailTemplate::find($templateId) : null;

                                try {
                                    $subject = $claudeService->generateEmailSubjectWithDealershipContext(
                                        $dealership,
                                        $data['context'],
                                        $template
                                    );
                                    $set('subject', $subject);

                                    \Filament\Notifications\Notification::make()
                                        ->title('Subject Generated')
                                        ->body('AI-generated subject based on your context and dealership details.')
                                        ->success()
                                        ->send();
                                } catch (Exception $e) {
                                    \Illuminate\Support\Facades\Log::error('Subject generation failed', [
                                        'error' => $e->getMessage(),
                                        'dealership_id' => $dealership->id,
                                    ]);

                                    \Filament\Notifications\Notification::make()
                                        ->title('Generation Failed')
                                        ->body('Unable to generate subject. Please try again.')
                                        ->danger()
                                        ->send();
                                }
                            })
                    ),
                RichEditor::make('message')
                    ->disableToolbarButtons(['attachFiles', 'codeBlock'])
                    ->columnSpanFull()
                    ->reactive()
                    ->hidden(fn (Get $get): bool => $get('dealer_email_template_id') !== null && $get('customize_email') === false)
                    ->required()
                    ->hintActions([
                        \Filament\Forms\Components\Actions\Action::make('generateMessage')
                            ->label('AI Generate')
                            ->icon('heroicon-o-sparkles')
                            ->visible(fn () => app(\App\Services\ClaudeEmailGeneratorService::class)->isConfigured())
                            ->form([
                                \Filament\Forms\Components\Textarea::make('context')
                                    ->label('What should this email be about?')
                                    ->placeholder('e.g., Follow-up from our conversation, product demo invitation, pricing discussion, address their current CRM limitations, etc.')
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
                                Checkbox::make('include_call_to_action')
                                    ->label('Include call to action')
                                    ->default(true),
                            ])
                            ->action(function (callable $set, callable $get, array $data, RelationManager $livewire): void {
                                $claudeService = app(\App\Services\ClaudeEmailGeneratorService::class);

                                if (! $claudeService->isConfigured()) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Claude API Not Configured')
                                        ->body('Please set your CLAUDE_API_KEY environment variable.')
                                        ->danger()
                                        ->send();

                                    return;
                                }

                                $dealership = $livewire->getOwnerRecord();
                                $templateId = $get('dealer_email_template_id');
                                $template = $templateId ? DealerEmailTemplate::find($templateId) : null;

                                try {
                                    $content = $claudeService->generateEmailContentWithDealershipContext(
                                        $dealership,
                                        $data['context'],
                                        $data['tone'],
                                        $data['include_call_to_action'],
                                        $template
                                    );
                                    $set('message', $content);

                                    \Filament\Notifications\Notification::make()
                                        ->title('Message Generated')
                                        ->body('AI-generated message based on your context and dealership profile.')
                                        ->success()
                                        ->send();
                                } catch (Exception $e) {
                                    \Illuminate\Support\Facades\Log::error('Message generation failed', [
                                        'error' => $e->getMessage(),
                                        'dealership_id' => $dealership->id,
                                    ]);

                                    \Filament\Notifications\Notification::make()
                                        ->title('Generation Failed')
                                        ->body('Unable to generate message. Please try again.')
                                        ->danger()
                                        ->send();
                                }
                            }),
                    ]),
                Select::make('frequency')
                    ->options(ReminderFrequency::class)
                    ->reactive()
                    ->required(),
                DatePicker::make('start_date')
                    ->closeOnDateSelection()
                    ->format('Y-m-d')
                    ->hidden(fn (Get $get): bool => $get('frequency') === ReminderFrequency::Immediate->value)
                    ->required(fn (Get $get): bool => $get('frequency') !== ReminderFrequency::Immediate->value)
                    ->dehydrated(fn (Get $get): bool => $get('frequency') !== ReminderFrequency::Immediate->value),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('recipients'),
                Tables\Columns\TextColumn::make('frequency'),
                Tables\Columns\TextColumn::make('last_sent')->date(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->after(function ($record): void {
                        if ($record->frequency === ReminderFrequency::Immediate) {
                            SendDealerEmail::dispatchSync($record);
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
