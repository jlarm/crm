<?php

namespace App\Filament\Resources\ContactResource\Pages;

use App\Filament\Resources\ContactResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Spatie\MailcoachSdk\Facades\Mailcoach;

class CreateContact extends CreateRecord
{
    protected static string $resource = ContactResource::class;
}
