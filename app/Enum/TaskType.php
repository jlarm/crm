<?php

declare(strict_types=1);

namespace App\Enum;

enum TaskType: string
{
    case Call = 'call';
    case Email = 'email';
    case Demo = 'demo';
    case FollowUp = 'follow_up';
    case Proposal = 'proposal';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Call => 'Call',
            self::Email => 'Email',
            self::Demo => 'Demo',
            self::FollowUp => 'Follow Up',
            self::Proposal => 'Proposal',
            self::Other => 'Other',
        };
    }
}
