<?php

declare(strict_types=1);

namespace App\Enum;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum OpportunityStage: string implements HasColor, HasLabel
{
    case Prospect = 'prospect';
    case Contacted = 'contacted';
    case Qualified = 'qualified';
    case Demo = 'demo';
    case Proposal = 'proposal';
    case Negotiation = 'negotiation';
    case Won = 'won';
    case Lost = 'lost';

    /** @return list<string> */
    public static function openValues(): array
    {
        return array_column(
            array_filter(self::cases(), fn (self $s) => $s->isOpen()),
            'value'
        );
    }

    /** @return list<string> */
    public static function closedValues(): array
    {
        return [self::Won->value, self::Lost->value];
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Prospect => 'Prospect',
            self::Contacted => 'Contacted',
            self::Qualified => 'Qualified',
            self::Demo => 'Demo',
            self::Proposal => 'Proposal',
            self::Negotiation => 'Negotiation',
            self::Won => 'Won',
            self::Lost => 'Lost',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Prospect => 'gray',
            self::Contacted => 'info',
            self::Qualified => 'primary',
            self::Demo => 'indigo',
            self::Proposal => 'warning',
            self::Negotiation => 'orange',
            self::Won => 'success',
            self::Lost => 'danger',
        };
    }

    public function isOpen(): bool
    {
        return ! in_array($this, [self::Won, self::Lost], true);
    }
}
