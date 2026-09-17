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

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $case): array => ['value' => $case->value, 'label' => $case->label()],
            self::cases(),
        );
    }

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
