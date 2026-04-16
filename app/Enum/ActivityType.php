<?php

declare(strict_types=1);

namespace App\Enum;

enum ActivityType: string
{
    case Call = 'call';
    case Note = 'note';
    case Email = 'email';

    public function label(): string
    {
        return match ($this) {
            self::Call => 'Call',
            self::Note => 'Note',
            self::Email => 'Email',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Call => 'phone',
            self::Note => 'file-text',
            self::Email => 'mail',
        };
    }
}
