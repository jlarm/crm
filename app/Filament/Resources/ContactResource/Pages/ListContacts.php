<?php

declare(strict_types=1);

namespace App\Filament\Resources\ContactResource\Pages;

use App\Filament\Resources\ContactResource;
use App\Filament\Resources\DealershipResource;
use App\Models\Contact;
use App\Models\Dealership;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Konnco\FilamentImport\Actions\ImportAction;
use Konnco\FilamentImport\Actions\ImportField;

class ListContacts extends ListRecords
{
    protected static string $resource = ContactResource::class;

    protected function getHeaderActions(): array
    {
        if (in_array(auth()->user()->id, [1, 2, 4])) {
            return [
                Actions\CreateAction::make(),
                ImportAction::make()
                    ->uniqueField('name')
                    ->fields([
                        ImportField::make('name')
                            ->label('Name'),
                        ImportField::make('phone')
                            ->label('Phone'),
                        ImportField::make('email')
                            ->label('Email'),
                        ImportField::make('position')
                            ->label('Position'),
                        ImportField::make('dealership.name')
                            ->label('Dealership Name'),
                        ImportField::make('dealership.address')
                            ->label('Dealership Address'),
                        ImportField::make('dealership.city')
                            ->label('Dealership City'),
                        ImportField::make('dealership.state')
                            ->label('Dealership State'),
                        ImportField::make('dealership.zip')
                            ->label('Dealership Zip Code'),
                        ImportField::make('dealership.phone')
                            ->label('Dealership Phone'),
                    ], columns: 2)
                    ->handleRecordCreation(function (array $data) {
                        if ($dealer = DealershipResource::getEloquentQuery()->where('name', $data['dealership']['name'])->first()) {
                            return Contact::create([
                                'name' => $data['name'],
                                'phone' => preg_replace('/^\+1-/', '', $data['phone']),
                                'email' => $data['email'],
                                'position' => $data['position'],
                                'dealership_id' => $dealer->id,
                            ]);
                        }
                        $newDealer = Dealership::create([
                            'user_id' => auth()->user()->id,
                            'name' => $data['dealership']['name'],
                            'address' => $data['dealership']['address'],
                            'city' => $data['dealership']['city'],
                            'state' => $data['dealership']['state'],
                            'zip_code' => $data['dealership']['zip'],
                            'phone' => preg_replace('/^\+1-/', '', $data['dealership']['phone']),
                            'status' => 'imported',
                            'rating' => 'cold',
                        ]);

                        $newDealer->users()->attach(User::where('id', 2)->first());
                        $newDealer->users()->attach(User::where('id', 4)->first());

                        return Contact::create([
                            'name' => $data['name'],
                            'phone' => preg_replace('/^\+1-/', '', $data['phone']),
                            'email' => $data['email'],
                            'position' => $data['position'],
                            'dealership_id' => $newDealer->id,
                        ]);

                    }),
            ];
        }

        return [];
    }
}
