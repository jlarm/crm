<?php

declare(strict_types=1);

namespace App\Enum;

use Filament\Support\Contracts\HasLabel;

enum ReminderFrequency: int implements HasLabel
{
    case Immediate = -1;
    case Once = 0;
    case Daily = 1;
    case Weekly = 7;
    case Monthly = 30;

    case BiMonthly = 60;
    case Quarterly = 90;
    case Yearly = 365;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Immediate => 'Send Immediately',
            self::Once => 'Once',
            self::Daily => 'Daily',
            self::Weekly => 'Weekly',
            self::Monthly => 'Monthly',
            self::BiMonthly => 'Every Other Monthly',
            self::Quarterly => 'Quarterly',
            self::Yearly => 'Yearly',
        };
    }
}
