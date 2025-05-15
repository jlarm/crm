<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Models\Dealership;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ActivitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'activities';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                Tables\Columns\TextColumn::make('id'),
                Tables\Columns\TextColumn::make('description'),
                Tables\Columns\TextColumn::make('subject_id')
                    ->label('Dealership')
                    ->sortable()
                    ->formatStateUsing(function ($state, $record) {
                        if ($record && isset($record->subject_type) && $record->subject_type === 'App\\Models\\Dealership' && $state) {
                            $dealership = Dealership::find($state);
                            return $dealership ? $dealership->name : 'Unknown Dealership';
                        }
                        
                        if ($record && isset($record->properties)) {
                            $properties = json_decode($record->properties, true);
                            if (is_array($properties) && isset($properties['attributes']['dealership_id'])) {
                                $dealershipId = $properties['attributes']['dealership_id'];
                                $dealership = Dealership::find($dealershipId);
                                return $dealership ? $dealership->name : 'Unknown Dealership';
                            }
                        }
                        
                        return '';
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->date()
                    ->sortable(),
            ])->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ]);
    }
}
