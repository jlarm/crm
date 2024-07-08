<?php

namespace App\Enum;

use Filament\Support\Contracts\HasLabel;

enum ReminderFrequency: int implements HasLabel
{
    case Daily = 1;
    case Weekly = 7;
    case Monthly = 30;
    case Quarterly = 90;
    case Yearly = 365;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Daily => 'Daily',
            self::Weekly => 'Weekly',
            self::Monthly => 'Monthly',
            self::Quarterly => 'Quarterly',
            self::Yearly => 'Yearly',
        };
    }
}
