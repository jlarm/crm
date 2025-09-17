<?php

declare(strict_types=1);

namespace App\Filament\Resources\SentEmailResource\Pages;

use App\Filament\Resources\SentEmailResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSentEmail extends CreateRecord
{
    protected static string $resource = SentEmailResource::class;
}
