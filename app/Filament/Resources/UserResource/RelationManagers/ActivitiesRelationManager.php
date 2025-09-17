<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Models\Contact;
use App\Models\DealerEmail;
use App\Models\Dealership;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class ActivitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'activities';

    protected static ?string $title = 'Activity Log';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('description')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                Tables\Columns\TextColumn::make('event')
                    ->label('Action')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        'login' => 'info',
                        'logout' => 'gray',
                        default => 'primary',
                    })
                    ->formatStateUsing(fn (?string $state): string => ucfirst($state ?? 'unknown'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->wrap()
                    ->searchable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('subject_type')
                    ->label('Model')
                    ->formatStateUsing(function (?string $state): string {
                        if (! $state) {
                            return '';
                        }

                        return class_basename($state);
                    })
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('subject_id')
                    ->label('Related Item')
                    ->formatStateUsing(function ($state, $record): string {
                        if (! $state || ! $record->subject_type) {
                            return '';
                        }

                        return match ($record->subject_type) {
                            'App\\Models\\Dealership' => $this->getDealershipName($state),
                            'App\\Models\\Contact' => $this->getContactName($state),
                            'App\\Models\\DealerEmail' => $this->getDealerEmailSubject($state),
                            'App\\Models\\User' => 'User #'.$state,
                            default => class_basename($record->subject_type).' #'.$state,
                        };
                    })
                    ->limit(30)
                    ->tooltip(function ($state, $record): ?string {
                        if (! $state || ! $record->subject_type) {
                            return null;
                        }

                        return match ($record->subject_type) {
                            'App\\Models\\Dealership' => $this->getDealershipName($state),
                            'App\\Models\\Contact' => $this->getContactName($state),
                            'App\\Models\\DealerEmail' => $this->getDealerEmailSubject($state),
                            default => null,
                        };
                    }),

                Tables\Columns\TextColumn::make('properties')
                    ->label('Changes')
                    ->formatStateUsing(function ($state): HtmlString {
                        if (! $state) {
                            return new HtmlString('');
                        }

                        $properties = is_string($state) ? json_decode($state, true) : $state;
                        if (! is_array($properties)) {
                            return new HtmlString('');
                        }

                        $changes = [];

                        // Show changed attributes
                        if (isset($properties['old']) && isset($properties['attributes'])) {
                            $old = $properties['old'];
                            $new = $properties['attributes'];

                            foreach ($new as $key => $newValue) {
                                if (isset($old[$key]) && $old[$key] !== $newValue) {
                                    $changes[] = "<strong>{$key}:</strong> {$old[$key]} → {$newValue}";
                                }
                            }
                        }

                        // Show IP address if available
                        if (isset($properties['ip'])) {
                            $changes[] = "<strong>IP:</strong> {$properties['ip']}";
                        }

                        // Show user agent if available
                        if (isset($properties['user_agent'])) {
                            $userAgent = $this->shortenUserAgent($properties['user_agent']);
                            $changes[] = "<strong>Browser:</strong> {$userAgent}";
                        }

                        return new HtmlString(implode('<br>', $changes));
                    })
                    ->html()
                    ->wrap()
                    ->limit(100),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('When')
                    ->dateTime('M j, Y H:i')
                    ->sortable()
                    ->since()
                    ->tooltip(fn ($state): string => $state instanceof \Carbon\Carbon
                            ? $state->format('F j, Y \a\t g:i:s A')
                            : (string) $state
                    ),
            ])->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('event')
                    ->label('Action')
                    ->options([
                        'created' => 'Created',
                        'updated' => 'Updated',
                        'deleted' => 'Deleted',
                        'login' => 'Login',
                        'logout' => 'Logout',
                    ]),

                SelectFilter::make('subject_type')
                    ->label('Model Type')
                    ->options([
                        'App\\Models\\Dealership' => 'Dealership',
                        'App\\Models\\Contact' => 'Contact',
                        'App\\Models\\DealerEmail' => 'Dealer Email',
                        'App\\Models\\User' => 'User',
                        'App\\Models\\Progress' => 'Progress',
                        'App\\Models\\Reminder' => 'Reminder',
                    ]),
            ])
            ->headerActions([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->form([
                        Forms\Components\TextInput::make('description')
                            ->label('Description')
                            ->disabled(),
                        Forms\Components\TextInput::make('event')
                            ->label('Event')
                            ->disabled(),
                        Forms\Components\TextInput::make('subject_type')
                            ->label('Model Type')
                            ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '')
                            ->disabled(),
                        Forms\Components\Textarea::make('properties')
                            ->label('Properties (JSON)')
                            ->formatStateUsing(fn ($state): string => is_string($state) ? $state : json_encode($state, JSON_PRETTY_PRINT))
                            ->rows(10)
                            ->disabled(),
                        Forms\Components\TextInput::make('created_at')
                            ->label('Created At')
                            ->formatStateUsing(fn ($state): string => $state instanceof \Carbon\Carbon
                                    ? $state->format('F j, Y \a\t g:i:s A')
                                    : (string) $state
                            )
                            ->disabled(),
                    ]),
            ])
            ->bulkActions([
                //
            ]);
    }

    protected function canCreate(): bool
    {
        return false;
    }

    protected function getDealershipName(int $id): string
    {
        $dealership = Dealership::find($id);

        return $dealership ? $dealership->name : 'Unknown Dealership';
    }

    protected function getContactName(int $id): string
    {
        $contact = Contact::find($id);

        return $contact ? $contact->name : 'Unknown Contact';
    }

    protected function getDealerEmailSubject(int $id): string
    {
        $email = DealerEmail::find($id);

        return $email ? ($email->subject ?? 'Email #'.$id) : 'Unknown Email';
    }

    protected function shortenUserAgent(string $userAgent): string
    {
        // Extract browser name from user agent
        if (preg_match('/Chrome\/[\d.]+/', $userAgent)) {
            return 'Chrome';
        }
        if (preg_match('/Firefox\/[\d.]+/', $userAgent)) {
            return 'Firefox';
        }
        if (preg_match('/Safari\/[\d.]+/', $userAgent) && ! preg_match('/Chrome/', $userAgent)) {
            return 'Safari';
        }
        if (preg_match('/Edge\/[\d.]+/', $userAgent)) {
            return 'Edge';
        }

        return 'Unknown Browser';
    }
}
