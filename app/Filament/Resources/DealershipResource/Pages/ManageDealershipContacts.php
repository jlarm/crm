<?php

declare(strict_types=1);

namespace App\Filament\Resources\DealershipResource\Pages;

use App\Filament\Resources\DealershipResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;

class ManageDealershipContacts extends ManageRelatedRecords
{
    protected static string $resource = DealershipResource::class;

    protected static string $relationship = 'contacts';

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected ?string $subheading = 'Manage Contacts';

    public static function getNavigationLabel(): string
    {
        return 'Contacts';
    }

    public function getHeading(): string
    {
        return $this->getOwnerRecord()->name;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->columnSpanFull()
                    ->required()
                    ->maxLength(255),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->nullable()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->mask('(999) 999-9999')
                            ->placeholder('(123) 456-7890')
                            ->nullable()
                            ->maxLength(255),
                    ]),
                Forms\Components\TextInput::make('position')
                    ->columnSpanFull()
                    ->nullable()
                    ->maxLength(255),
                Forms\Components\TextInput::make('linkedin_link')
                    ->columnSpanFull()
                    ->url()
                    ->placeholder('https://www.linkedin.com/in/tomdortch/')
                    ->nullable(),
                Forms\Components\Toggle::make('primary_contact'),
                Forms\Components\Select::make('tags')
                    ->label('MailCoach Tags')
                    ->columnSpanFull()
                    ->relationship('tags', 'name')
                    ->multiple()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->unique('tags', 'name'),
                    ])
                    ->createOptionUsing(fn(array $data) => \App\Models\Tag::create($data)->id)
                    ->searchable()
                    ->placeholder('Select or create tags')
                    ->helperText('Type to search or create new tags'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('email'),
                Tables\Columns\IconColumn::make('linkedin_link')
                    ->label('LinkedIn')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn ($record) => $record->linkedin_link)
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('position'),
                Tables\Columns\ToggleColumn::make('primary_contact')
                    ->afterStateUpdated(function ($record, $state): void {
                        // turn off anyone else as primary contact
                        if ($state) {
                            $record->dealership->contacts()
                                ->where('id', '!=', $record->id)
                                ->update(['primary_contact' => false]);
                        }
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(function (\App\Models\Contact $record, array $data): void {
                        // Log before explicit save to see if $touches worked at all
                        Log::debug('Filament EditAction ->after() hook - BEFORE explicit save.', [
                            'contact_id' => $record->id,
                            'email' => $record->email,
                            'original_updated_at' => $record->getOriginal('updated_at') ? $record->getOriginal('updated_at')->toIso8601String() : null,
                            'current_updated_at' => $record->updated_at->toIso8601String(),
                            'is_dirty_before_our_save' => $record->isDirty(),
                            'dirty_attributes_before_our_save' => $record->getDirty(),
                            'data_from_form' => $data, // See what data Filament's action had
                        ]);

                        // Explicitly save the record again to ensure 'updated' event fires if $touches didn't suffice
                        // or if Filament's own save didn't make it dirty enough for an event.
                        // We need to check if the timestamp was actually updated by $touches
                        // $record->isDirty() might be false if only timestamps were touched by Eloquent's default $touches behavior
                        $originalUpdatedAt = $record->getOriginal('updated_at');
                        $currentUpdatedAt = $record->updated_at;

                        // If the timestamp hasn't changed, or if it's not dirty for other reasons, force an update.
                        if (! $record->isDirty() && ($originalUpdatedAt && $currentUpdatedAt && $originalUpdatedAt->equalTo($currentUpdatedAt))) {
                            Log::debug('Filament EditAction ->after() hook: Record not dirty and timestamp unchanged by Filament/touches, forcing update.', ['contact_id' => $record->id]);
                            $record->updated_at = now(); // Force it to be dirty
                        } elseif ($record->isDirty()) {
                            Log::debug('Filament EditAction ->after() hook: Record is dirty, proceeding with save.', ['contact_id' => $record->id, 'dirty_fields' => $record->getDirty()]);
                        } else {
                            Log::debug('Filament EditAction ->after() hook: Record not dirty BUT timestamp was updated by Filament/touches. Saving anyway to ensure event.', ['contact_id' => $record->id]);
                            // It's possible $touches updated the timestamp, but an event wasn't fired. Saving again might help.
                        }

                        $record->save();

                        // Refresh to get the latest state after our save
                        $record->refresh();
                        Log::debug('Filament EditAction ->after() hook - AFTER explicit save.', [
                            'contact_id' => $record->id,
                            'email' => $record->email,
                            'final_updated_at' => $record->updated_at->toIso8601String(),
                        ]);
                    }),
                Tables\Actions\DeleteAction::make(), // Good to have an explicit delete action too
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
