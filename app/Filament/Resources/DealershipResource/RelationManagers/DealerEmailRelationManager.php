<?php

namespace App\Filament\Resources\DealershipResource\RelationManagers;

use App\Enum\ReminderFrequency;
use App\Jobs\SendDealerEmail;
use App\Models\DealerEmailTemplate;
use Carbon\Carbon;
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
                    ->options(function (RelationManager $livewire): array {
                        return $livewire->getOwnerRecord()->contacts()
                            ->pluck('email', 'email')
                            ->toArray();
                    })
                    ->columnSpanFull(),
                Select::make('dealer_email_template_id')
                    ->options(function () {
                        return DealerEmailTemplate::pluck('name', 'id')->toArray();
                    })
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
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
                    ->hidden(fn (Get $get) => $get('dealer_email_template_id') == null)
                    ->afterStateUpdated(function ($state, callable $set) {
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
                    ->hidden(fn (Get $get) => $get('dealer_email_template_id') == null)
                    ->default(false),
                FileUpload::make('attachment')
                    ->acceptedFileTypes(['application/pdf'])
                    ->storeFileNamesIn('attachment_name')
                    ->columnSpanFull()
                    ->reactive()
                    ->hidden(fn (Get $get) => $get('dealer_email_template_id') != null && $get('customize_attachment') == false)
                    ->directory('form-attachments'),
                TextInput::make('subject')
                    ->columnSpanFull()
                    ->required()
                    ->reactive()
                    ->hidden(fn (Get $get) => $get('dealer_email_template_id') != null && $get('customize_email') == false)
                    ->maxLength(255),
                RichEditor::make('message')
                    ->disableToolbarButtons(['attachFiles', 'codeBlock'])
                    ->columnSpanFull()
                    ->reactive()
                    ->hidden(fn (Get $get) => $get('dealer_email_template_id') != null && $get('customize_email') == false)
                    ->required(),
                Select::make('frequency')
                    ->options(ReminderFrequency::class)
                    ->reactive()
                    ->required(),
                DatePicker::make('start_date')
                    ->closeOnDateSelection()
                    ->format('Y-m-d')
                    ->hidden(fn (Get $get) => $get('frequency') == ReminderFrequency::Immediate->value)
                    ->required(fn (Get $get) => $get('frequency') != ReminderFrequency::Immediate->value)
                    ->dehydrated(fn (Get $get) => $get('frequency') != ReminderFrequency::Immediate->value),
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
