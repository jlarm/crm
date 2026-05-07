<?php

declare(strict_types=1);

namespace App\Enum;

enum DevStatus: string
{
    case NO_CONTACT = 'no_contact';
    case REACHED_OUT = 'reached_out';
    case IN_CONTACT = 'in_contact';

    public function getLabel(): string
    {
        return match ($this) {
            self::NO_CONTACT => 'No Contact',
            self::REACHED_OUT => 'Reached Out, No Response',
            self::IN_CONTACT => 'In Contact',
        };
    }
}
