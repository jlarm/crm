<?php

declare(strict_types=1);

namespace App\Filament\Resources\EmailTrackingEventResource\Pages;

use App\Filament\Resources\EmailTrackingEventResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEmailTrackingEvent extends CreateRecord
{
    protected static string $resource = EmailTrackingEventResource::class;
}
