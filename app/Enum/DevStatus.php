<?php

namespace App\Enum;

use Filament\Support\Contracts\HasLabel;

enum DevStatus: string implements HasLabel
{
    case NO_CONTACT = 'no_contact';
    case REACHED_OUT = 'reached_out';
    case IN_CONTACT = 'in_contact';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::NO_CONTACT => 'No Contact',
            self::REACHED_OUT => 'Reached Out, No Response',
            self::IN_CONTACT => 'In Contact',
        };
    }
}
