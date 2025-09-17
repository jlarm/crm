<?php

declare(strict_types=1);

namespace App\Filament\Resources\DealershipResource\Pages;

use App\Enum\ReminderFrequency;
use App\Filament\Resources\DealershipResource;
use App\Jobs\SendDealerEmail;
use App\Models\DealerEmailTemplate;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables;
use Filament\Tables\Table;

class ManageDealershipDealerEmails extends ManageRelatedRecords
{
    protected static string $resource = DealershipResource::class;

    protected static string $relationship = 'dealerEmails';

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected ?string $subheading = 'Manage Emails';

    public static function getNavigationLabel(): string
    {
        return 'Emails';
    }

    public function getHeading(): string
    {
        return $this->getOwnerRecord()->name;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('recipients')
                    ->label('Recipients')
                    ->multiple()
                    ->required()
                    ->options(function () {
                        return $this->getOwnerRecord()->contacts()
                            ->pluck('email', 'email')
                            ->filter() // Ensure no null values
                            ->toArray();
                    })
                    ->columnSpanFull(),
                Select::make('dealer_email_template_id')
                    ->options(function () {
                        return DealerEmailTemplate::pluck('name', 'id')
                            ->filter() // Ensure no null values
                            ->toArray();
                    })
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state === '') {
                            $set('subject', '');
                            $set('message', '');
                            $set('attachment', null);
                            $set('pdf_attachments', []);
                        } else {
                            $template = DealerEmailTemplate::find($state);
                            if ($template) {
                                $set('subject', $template->subject);
                                $set('attachment', $template->attachment);
                                $set('message', $template->body);
                                $set('pdf_attachments', $template->pdfAttachments->pluck('id')->toArray());
                            } else {
                                $set('subject', '');
                                $set('message', '');
                                $set('attachment', null);
                                $set('pdf_attachments', []);
                            }
                        }
                    })
                    ->columnSpanFull()
                    ->helperText(function (Get $get) {
                        $template = DealerEmailTemplate::find($get('dealer_email_template_id'));
                        if (! $template || $template->pdfAttachments->isEmpty()) {
                            return null;
                        }

                        return 'PDF Attachments: '.$template->pdfAttachments->pluck('file_name')->implode(', ');
                    })
                    ->label('Template'),
                // Checkbox::make('attach_pdf_template')
                //     ->reactive()
                //     ->columnSpanFull()
                //     ->label('Add custom PDF attachments')
                //     ->default(false),
                // Select::make('pdf_attachments')
                //     ->label('PDF Attachments')
                //     ->visible(fn (Get $get) => $get('attach_pdf_template') == true)
                //     ->reactive()
                //     ->multiple()
                //     ->relationship('pdfAttachments', 'file_name')
                //     ->preload()
                //     ->columnSpanFull()
                //     ->createOptionForm([
                //         FileUpload::make('file_path')
                //             ->required()
                //             ->acceptedFileTypes(['application/pdf'])
                //             ->storeFileNamesIn('file_name')
                //             ->directory('pdfs'),
                //     ]),
                Checkbox::make('customize_email')
                    ->columnSpanFull()
                    ->label('Customize email')
                    ->reactive()
                    ->hidden(fn (Get $get) => $get('dealer_email_template_id') === null)
                    ->afterStateUpdated(function ($state, callable $set, Get $get) {
                        $templateId = $get('dealer_email_template_id');
                        if ($templateId === null) {
                            $set('subject', '');
                            $set('message', '');
                            $set('attachment', null);
                        } else {
                            $template = DealerEmailTemplate::find($templateId);
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
                TextInput::make('subject')
                    ->columnSpanFull()
                    ->required()
                    ->reactive()
                    ->hidden(fn (Get $get) => $get('dealer_email_template_id') !== null && $get('customize_email') === false)
                    ->maxLength(255),
                RichEditor::make('message')
                    ->disableToolbarButtons(['attachFiles', 'codeBlock'])
                    ->columnSpanFull()
                    ->reactive()
                    ->hidden(fn (Get $get) => $get('dealer_email_template_id') !== null && $get('customize_email') === false)
                    ->required(),
                Select::make('frequency')
                    ->columnSpanFull()
                    ->options(ReminderFrequency::class)
                    ->reactive()
                    ->required(),
                DatePicker::make('start_date')
                    ->closeOnDateSelection()
                    ->format('Y-m-d')
                    ->hidden(fn (Get $get) => $get('frequency') === ReminderFrequency::Immediate->value)
                    ->required(fn (Get $get) => $get('frequency') !== ReminderFrequency::Immediate->value)
                    ->dehydrated(fn (Get $get) => $get('frequency') !== ReminderFrequency::Immediate->value)
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $set('next_send_date', $state);
                    }),
                DatePicker::make('next_send_date')
                    ->closeOnDateSelection()
                    ->format('Y-m-d')
                    ->hidden(fn (Get $get) => $get('frequency') === ReminderFrequency::Immediate->value)
                    ->required(fn (Get $get) => $get('frequency') !== ReminderFrequency::Immediate->value)
                    ->dehydrated(fn (Get $get) => $get('frequency') !== ReminderFrequency::Immediate->value),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('last_sent', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('recipients')->sortable(),
                Tables\Columns\TextColumn::make('frequency')->sortable(),
                Tables\Columns\TextColumn::make('last_sent')->date()->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->after(function ($record) {
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
